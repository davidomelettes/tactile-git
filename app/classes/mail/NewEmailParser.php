<?php

require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Null.php';

class MailParserException extends Exception {
	protected $_source;
	
	public function __construct($message, $source=null) {
		$this->_source = $source;
		parent::__construct($message);
	}
	
	public function getSource() {
		return $this->_source;
	}
}
class MalformedMailException extends MailParserException { } // Junk
class NoDropboxIdException extends MailParserException { } // Could not identify dropbox owner
class MailLoopException extends MailParserException { } // Just in case
class NoOpportunityFoundMailException extends MailParserException { } // Tried to attach to Opp, but failed

class NewEmailParser {
	
	const EMAIL_ADDRESS_PATTERN = "/[a-z0-9._%+\-]+[a-z0-9._%+\-']*@[a-z0-9.-]+\.[a-z]{2,4}/i";
	const DROPBOX_ADDRESS_PATTERN = '/([a-z0-9._%+-]+)@([a-z0-9]+)\.([a-z0-9]+)\.mail\.tactilecrm(labs)?\.com/i'; // <action>@<key>.<site_address>.mail.tactilecrm.com
	const OLD_DROPBOX_ADDRESS_PATTERN = '/^(tactile)\+([a-z0-9]+)@([a-z0-9]+)\.tactilecrm(labs)?\.com/i';
	const ACTION_PATTERN_DROPBOX = '/dropbox$/i';
	const ACTION_PATTERN_OLD_DROPBOX = '/^tactile$/i';
	const ACTION_PATTERN_OPPORTUNITY = '/opp(?:\+|%2B)(\d+)$/i';
	const DIRECTION_OUTGOING = 'outgoing';
	const DIRECTION_INCOMING = 'incoming';
	
	/**
	 * Logger
	 *
	 * @var Zend_Log
	 */
	public $logger;
	
	/**
	 * An email
	 *
	 * @var Zend_Mail_Message
	 */
	protected $_message;
	
	/**
	 * Action to perform, extracted from dropbox address
	 *
	 * @var string
	 */
	protected $_action;
	
	/**
	 * Potentially useful email addresses extracted from the email
	 *
	 * @var array
	 */
	protected $_addresses;
	
	/**
	 * Which address the email originally came from
	 *
	 * @var string
	 */
	protected $_origin = false;
	
	/**
	 * Which address(es) the email originally arrived at
	 *
	 * @var array
	 */
	protected $_destinations = array();
	
	/**
	 * The email address of the User involved, if found (may be the same as the origin or destination)
	 *
	 * @var string
	 */
	protected $_user_address = false;
	
	/**
	 * If we can decided one way or the other, 'incoming' or 'outgoing'
	 *
	 * @var string|boolean
	 */
	protected $_direction = false;
	
	/**
	 * Any attachments for this email
	 *
	 * @var array
	 */
	protected $_attachements = array();
	
	/**
	 * Plaintext body of the email
	 *
	 * @var unknown_type
	 */
	protected $_plaintext = '';
	
	protected $_opportunity_id = null;
	protected $_emails = array();
	protected $_is_forwarded = false;
	
	public function __construct(Zend_Log $logger = null) {
		if (is_null($logger)) {
			$logger = new Zend_Log(new Zend_Log_Writer_Null());
		}
		$this->logger = $logger;
	}
	
	protected function _getDecodedContent($part = null) {
		$this->logger->debug('Fetching decoding content...');
		$encoding = null;
		$ct_encoding = 'default';
		
		// Have we been given a specific part to work with?
		if ($part instanceof Zend_Mail_Part) {
			$this->logger->debug('Passed a Zend_Mail_Part');
			$content = $part->getContent();
			if (preg_match('/charset=(\S+)/i', $part->contentType, $matches)) {
				$encoding = $matches[1];
				$this->logger->debug('Encoding: ' . $encoding);
			}
			try {
				$ct_encoding = $part->getHeader('Content-Transfer-Encoding', 'string');
			} catch (Exception $e) {
				$ct_encoding = 'default';
			}
			
		} else {
			// Search multipart messages for plaintext
			if ($this->_message->isMultipart()) {
				$this->logger->debug('Message is multipart');
				$foundPart = null;
				foreach (new RecursiveIteratorIterator($this->_message) as $part) {
					try {
						if (strtok($part->contentType, ';') == 'text/plain') {
							$this->logger->debug('Found some plaintext');
							$foundPart = $part;
							if (preg_match('/charset=(\S+)/i', $part->contentType, $matches)) {
								$encoding = $matches[1];
								$this->logger->debug('Encoding: ' . $encoding);
							}
							try {
								$ct_encoding = $part->getHeader('Content-Transfer-Encoding', 'string');
							} catch (Exception $e) {
								$ct_encoding = 'default';
							}
							break;
						}
					} catch (Zend_Mail_Exception $e) {
						// ignore
					}
				}
				if (!$foundPart) {
					// Fallback
					$this->logger->debug('Failed to find plaintext in multipart message');
					$content = $this->_message->getContent();
					try {
						$ct_encoding = $this->_message->getHeader('Content-Transfer-Encoding', 'string');
					} catch (Exception $e) {
						$ct_encoding = 'default';
					}
				} else {
					$content = $foundPart->getContent();
					try {
						$ct_encoding = $foundPart->getHeader('Content-Transfer-Encoding', 'string');
					} catch (Exception $e) {
						$ct_encoding = 'default';
					}
				}
			} else {
				$content = $this->_message->getContent();
				try {
					$ct_encoding = $this->_message->getHeader('Content-Transfer-Encoding', 'string');
				} catch (Exception $e) {
					$ct_encoding = 'default';
				}
			}
		}
		
		$this->logger->debug('Transfer encoding: ' . $ct_encoding);
		switch ($ct_encoding) {
			case 'base64':
				$content = base64_decode($content);
				break;
		}
		//$this->logger->debug('Content: ' . substr($content, 0, 50));
		
		// Decode Quoted-Printable
		$content = quoted_printable_decode($content);
		
		// Recode as UTF8
		if ($encoding) {
			$content = @iconv($encoding, 'UTF8//TRANSLIT', $content);
			return $content;
		} else {
			try {
				if (preg_match('/charset=(\S+)/i', $this->_message->getHeader('Content-Type'), $matches)) {
					$encoding = $matches[1];
				}
			} catch (Zend_Mail_Exception $e) {
				// ignore
			}
		}
		
		if (!$encoding) {
			// No content-type header to work from, assume ISO-8859-1
			return utf8_encode($content);
		} else {
			$content = @iconv($encoding, 'UTF8//TRANSLIT', $content);
			return $content;
		}
	}
	
	/**
	 * Does the business
	 *
	 * @param Zend_Mail_Message $message
	 */
	public function apply(Zend_Mail_Message $message) {
		$this->logger->debug('-- START: EmailParser --');
		$this->_message = $message;
		$this->_origin = false;
		$this->_destinations = array();
		$this->_opportunity_id = null;
		$this->_emails = array();
		$this->_is_forwarded = false;
		
		try {
			$this->logger->debug('STAGE 1/3: parse()');
			$this->parse();
			$this->logger->debug('STAGE 2/3: save()');
			$this->save();
			$this->logger->debug('STAGE 3/3: saveAttachments()');
			$this->saveAttachments();
			$this->logger->debug('-- DONE --');
			return true;

		} catch (MalformedMailException $e) {
			// Do nothing, this is junk mail
			$this->logger->debug('Stopping: ' . $e->getMessage());
			return false;
		
		} catch (NoDropboxIdException $e) {
			// Looks valid-ish, but can't identify the dropbox owner. Probably junk
			$this->logger->debug('Stopping: ' . $e->getMessage());
			return false;
		
		} catch (MailLoopException $e) {
			// Stop right there
			$this->logger->warn('Stopping: ' . $e->getMessage());
			return false;
			
		} catch (Zend_Mail_Transport_Exception $e) {
			// Parsing was successful, but we wanted to send an email, and failed
			$this->logger->warn('Failed to send email: ' . $e->getMessage());
			return false;
			
		} catch (MailParserException $e) {
			// Something unexpected happened
			$this->logger->warn($e->getMessage());
			return false;
			
		} catch (Exception $e) {
			// Something very unexpected happened
			$this->logger->warn('General exception occurred: ' . $e->getMessage());
			return false;
		}
	}
	
	/**
	 * Parses the message for any and all addresses
	 *
	 * @return Array
	 */
	public function extractAddresses() {
		if (!$this->_message instanceof Zend_Mail_Message) {
			throw new Exception('Message not set!');
		}
		
		$addresses = array(
			'delivered-to'	=> '',
			'from'			=> '',
			'to'			=> FALSE,
			'forwarded-from'=> FALSE,
			'forwarded-to'	=> FALSE,
			'forwarded-cc'	=> FALSE
		);
		
		try {
			$addresses['delivered-to'] = strtolower($this->_message->getHeader('Delivered-To', 'string'));
		} catch (Zend_Mail_Exception $e) {
			// This is bad
			throw new MalformedMailException('Could not get Delivered-To header!');
		}
		try {
			$addresses['from'] = strtolower($this->_message->getHeader('From', 'string'));
		} catch (Zend_Mail_Exception $e) {
			// This is also bad
			throw new MalformedMailException('Could not get From header!');
		}
		try {
			$addresses['to'] = strtolower($this->_message->getHeader('To', 'string'));
		} catch (Zend_Mail_Exception $e) {
			// A missing 'To' header is okay, sometimes. 
		}
		try {
			$addresses['cc'] = strtolower($this->_message->getHeader('Cc', 'string'));
		} catch (Zend_Mail_Exception $e) {
			// A missing 'Cc' header is okay 
		}
		
		$mail_body = $this->_getDecodedContent();
		if (preg_match('/From:\s+([^\n\r]+)/i', $mail_body, $matches)) {
			$addresses['forwarded-from'] = $matches[1];
		}
		if (preg_match('/To:\s+([^\n\r]+)/i', $mail_body, $matches)) {
			$addresses['forwarded-to'] = $matches[1];
		}
		if (preg_match('/Cc:\s+([^\n\r]+)/i', $mail_body, $matches)) {
			$addresses['forwarded-cc'] = $matches[1];
		}

		return $addresses;
	}
	
	/**
	 * Pulls all of the attachments out of the message, and sets the _plaintext property
	 *
	 * @return Array
	 */
	public function extractAttachments() {
		if (!$this->_message instanceof Zend_Mail_Message) {
			throw new Exception('Message not set!');
		}
		
		$this->_plaintext = '';
		$attachments = array();
		
		// Extract any attachments
		if ($this->_message->isMultiPart()) {
			$part_types = array();
			foreach (new RecursiveIteratorIterator($this->_message) as $part) {
				// Parse content type line (e.g. Content-Type: text/html; charset=ISO-8859-1)
				preg_match_all('/ ?([^;]+)/', $part->contentType, $matches);
				$params = array('type' => array_shift($matches[1]));
				foreach ($matches[1] as $pair) {
					$pair = split('=', $pair);
					$params[$pair[0]] = trim($pair[1], '"\'');
				}
				
				$part_types[] = $params['type'];
				
				if ($params['type'] == 'text/plain') {
					// This part is plaintext
					$this->_plaintext .= $this->_getDecodedContent($part);
				} else {
					// This part is an attachment (could also be the HTML component)
					$attachment = array();
					
					if ($params['type'] == 'text/html') {
						$attachment['type'] = 'text/html';
						$attachment['name'] = 'html-alternative.html';
					} else {
						$attachment['type'] = $params['type'];
						$attachment['name'] = !empty($params['name']) ? $params['name'] : "unnamed-attachment";
					}
					
                    $headers = $part->getHeaders();
					if (isset($headers['content-transfer-encoding']) && strtolower($headers['content-transfer-encoding']) == 'base64') {
						$attachment['content'] = base64_decode($part->getContent());
					} else {
						$attachment['content'] = quoted_printable_decode($part->getContent());
					}
					
					array_push($attachments, $attachment);
				}
			}
			$this->logger->debug('Multipart types: ' . implode(', ', $part_types));
		} else {
			$this->_plaintext = $this->_getDecodedContent();
		}
		
		return $attachments;
	}
	
	/**
	 * Determines whether the requested dropbox action is valid or not
	 *
	 * @return boolean
	 */
	public function isValidAction() {
		if (!$this->_message instanceof Zend_Mail_Message) {
			throw new Exception('Message not set!');
		}
		
		switch (1) {
			case preg_match(self::ACTION_PATTERN_DROPBOX, $this->_action):
			case preg_match(self::ACTION_PATTERN_OLD_DROPBOX, $this->_action):
			case preg_match(self::ACTION_PATTERN_OPPORTUNITY, $this->_action):
				return true;
			default:
				return false;
		}
	}
	
	public function extractEmailAddress($string) {
		if (preg_match(self::EMAIL_ADDRESS_PATTERN, $string, $matches)) {
			return $matches[0];
		} else {
			return false;
		}
	}
	
	/**
	 * Process Mail to determine ownership, direction, and who/what to attach it to 
	 *
	 */
	public function parse() {
		// We have new mail. Let's parse it!
		if (!$this->_message instanceof Zend_Mail_Message) {
			throw new Exception('Message not set!');
		}
		try {
			$this->logger->debug('Subject: ' . $this->_message->subject);
		} catch (Zend_Mail_Exception $e) {
			// Missing a subject header? Must be junk
			throw new MalformedMailException($e->getMessage());
		}
		
		$db = DB::Instance();
		
		$this->_addresses = $this->extractAddresses();
		
		// Mail loop detection
		if (preg_match('/robot@tactilecrm\.com/', $this->_addresses['from'])) {
			throw new MailLoopException('Mail loop detected! From: ' . $this->_addresses['from']);
		}
		
		// Test the destination address for validity, and to determine what to do if valid
		$matches = array();
		if (!preg_match(self::DROPBOX_ADDRESS_PATTERN, $this->_addresses['delivered-to'], $matches) &&
			!preg_match(self::OLD_DROPBOX_ADDRESS_PATTERN, $this->_addresses['delivered-to'], $matches)) {
			throw new MalformedMailException('Invalid dropbox address: ' . $this->_addresses['delivered-to']);
		}
		$dropbox_address = $matches[0];
		$this->_action = $matches[1];
		$dropboxkey = $matches[2];
		$site_address = $matches[3];
		
		if (!$this->isValidAction()) {
			throw new MalformedMailException('Invalid dropbox action: ' . $this->_action);
		}
		
		// From which (enabled) account is this message purporting to be?
		$company_id = $db->getOne('SELECT organisation_id FROM tactile_accounts WHERE enabled AND site_address = ' . $db->qstr($site_address));
		if (empty($company_id)) {
			throw new NoDropboxIdException('Failed to find enabled account with site_address: ' . $site_address);
		}
		// Act as this account
		EGS::setCompanyId($company_id);
		
		// Does this dropbox key belong to an enabled user on this account?
		$user = new Tactile_User();
		$cc = new ConstraintChain();
		$cc->add(new Constraint('username', 'like', '%//'.$site_address));
		$cc->add(new Constraint('dropboxkey', '=', $dropboxkey));
		$cc->add(new Constraint('enabled', '=', 'true'));
		if (FALSE === $user->loadBy($cc)) {
			throw new NoDropboxIdException('Failed to find enabled ' . $site_address . ' User with dropbox: ' . $dropboxkey);
		}
		// Act as this User
		Omelette::setUserSpace($site_address);
		EGS::setUsername($user->getRawUsername());
		$this->logger->debug('Mail for User: ' . $user->getRawUsername());
		
		// Ok, we know who owns this email. But in which direction is it travelling?
		$user_addresses = new PersoncontactmethodCollection();
		$sh = new SearchHandler($user_addresses, false);
		$sh->addConstraint(new Constraint('type', '=', 'E'));
		$sh->addConstraint(new Constraint('person_id', '=', $user->person_id));
		$sh->setOrderby('main desc, name');
		$user_addresses->load($sh);
		
		if (count($user_addresses) < 1) {
			// User has no addresses!
			throw new MailParserException('User has no email addresses?');
		}
		
		// Look for one of the User's email addresses somewhere in the mail
		if (preg_match('/^re:/i', $this->_message->subject)) {
			// Assume a BCC, reverse detection order
			foreach ($user_addresses as $address) {
				// Probably BCC'ed to the dropbox while sent by User
				if (preg_match('/'.preg_quote($address->contact).'/', $this->_addresses['from'])) {
					$this->logger->debug('(Not Forwarded) Email is FROM User with: ' . $address->contact);
					$this->_is_forwarded = false;
					$this->_user_address = $address->contact;
					$this->_direction = self::DIRECTION_OUTGOING;
					$this->_origin = $this->_addresses['from'];
					$this->_destinations[] = $this->_addresses['to'];
					break;
				}
				// Probably a message a User has received, then forwarded to the dropbox
				if (preg_match('/'.preg_quote($address->contact).'/', $this->_addresses['forwarded-to'])) {
					$this->logger->debug('(Forwarded) Email is TO User with: ' . $address->contact);
					$this->_is_forwarded = true;
					$this->_user_address = $address->contact;
					$this->_direction = self::DIRECTION_INCOMING;
					$this->_origin = $this->_addresses['forwarded-from'];
					$this->_destinations[] = $address->contact;
					break;
				}
				// Same as above
				if (preg_match('/'.preg_quote($address->contact).'/', $this->_addresses['forwarded-cc'])) {
					$this->logger->debug('(Forwarded) Email is CC User with: ' . $address->contact);
					$this->_is_forwarded = true;
					$this->_user_address = $address->contact;
					$this->_direction = self::DIRECTION_INCOMING;
					$this->_origin = $this->_addresses['forwarded-from'];
					$this->_destinations[] = $address->contact;
					break;
				}
				// Probably a message a User has sent, then later forwarded from "sent items" to the dropbox
				if (preg_match('/'.preg_quote($address->contact).'/', $this->_addresses['forwarded-from'])) {
					$this->logger->debug('(Forwarded) Email is FROM User with: ' . $address->contact);
					$this->_is_forwarded = true;
					$this->_user_address = $address->contact;
					$this->_direction = self::DIRECTION_OUTGOING;
					$this->_origin = $this->_addresses['forwarded-from'];
					$this->_destinations[] = $this->_addresses['forwarded-to'];
					break;
				}
			}
			
		} elseif (preg_match('/^fw:/i', $this->_message->subject)) {
			// most likely a forwarded message
			foreach ($user_addresses as $address) {
				// Probably a message a User has received, then forwarded to the dropbox
				if (preg_match('/'.preg_quote($address->contact).'/', $this->_addresses['forwarded-to'])) {
					$this->logger->debug('(Forwarded) Email is TO User with: ' . $address->contact);
					$this->_is_forwarded = true;
					$this->_user_address = $address->contact;
					$this->_direction = self::DIRECTION_INCOMING;
					$this->_origin = $this->_addresses['forwarded-from'];
					$this->_destinations[] = $address->contact;
					break;
				}
				// Same as above
				if (preg_match('/'.preg_quote($address->contact).'/', $this->_addresses['forwarded-cc'])) {
					$this->logger->debug('(Forwarded) Email is CC User with: ' . $address->contact);
					$this->_is_forwarded = true;
					$this->_user_address = $address->contact;
					$this->_direction = self::DIRECTION_INCOMING;
					$this->_origin = $this->_addresses['forwarded-from'];
					$this->_destinations[] = $address->contact;
					break;
				}
				// Probably a message a User has sent, then later forwarded from "sent items" to the dropbox
				if (preg_match('/'.preg_quote($address->contact).'/', $this->_addresses['forwarded-from'])) {
					$this->logger->debug('(Forwarded) Email is FROM User with: ' . $address->contact);
					$this->_is_forwarded = true;
					$this->_user_address = $address->contact;
					$this->_direction = self::DIRECTION_OUTGOING;
					$this->_origin = $this->_addresses['forwarded-from'];
					$this->_destinations[] = $this->_addresses['forwarded-to'];
					break;
				}
			}
			
		} else {
			// Assume is a forward, check for forward first
			foreach ($user_addresses as $address) {
				// Probably a message a User has received, then forwarded to the dropbox
				if (preg_match('/'.preg_quote($address->contact).'/', $this->_addresses['forwarded-to'])) {
					$this->logger->debug('(Forwarded) Email is TO User with: ' . $address->contact);
					$this->_is_forwarded = true;
					$this->_user_address = $address->contact;
					$this->_direction = self::DIRECTION_INCOMING;
					$this->_origin = $this->_addresses['forwarded-from'];
					$this->_destinations[] = $address->contact;
					break;
				}
				// Same as above
				if (preg_match('/'.preg_quote($address->contact).'/', $this->_addresses['forwarded-cc'])) {
					$this->logger->debug('(Forwarded) Email is CC User with: ' . $address->contact);
					$this->_is_forwarded = true;
					$this->_user_address = $address->contact;
					$this->_direction = self::DIRECTION_INCOMING;
					$this->_origin = $this->_addresses['forwarded-from'];
					$this->_destinations[] = $address->contact;
					break;
				}
				// Probably a message a User has sent, then later forwarded from "sent items" to the dropbox
				if (preg_match('/'.preg_quote($address->contact).'/', $this->_addresses['forwarded-from'])) {
					$this->logger->debug('(Forwarded) Email is FROM User with: ' . $address->contact);
					$this->_is_forwarded = true;
					$this->_user_address = $address->contact;
					$this->_direction = self::DIRECTION_OUTGOING;
					$this->_origin = $this->_addresses['forwarded-from'];
					$this->_destinations[] = $this->_addresses['forwarded-to'];
					break;
				}
				// Probably BCC'ed to the dropbox while sent by User
				if (preg_match('/'.preg_quote($address->contact).'/', $this->_addresses['from'])) {
					$this->logger->debug('(Not Forwarded) Email is FROM User with: ' . $address->contact);
					$this->_is_forwarded = false;
					$this->_user_address = $address->contact;
					$this->_direction = self::DIRECTION_OUTGOING;
					$this->_origin = $this->_addresses['from'];
					$this->_destinations[] = $this->_addresses['to'];
					break;
				}
			}
		}
		
		// If a direction can not be decided, try searching for the User's name instead
		if (empty($this->_direction)) {
			$person = $user->getPerson();
			$this->logger->debug('Failed to find a user email address, searching for name: ' . $person->firstname . ' ' . $person->surname);
			
			if (!empty($this->_addresses['forwarded-from']) || !empty($this->_addresses['forwarded-to']) || !empty($this->_addresses['forwarded-cc'])) {
				$this->_is_forwarded = true;
			}
				
			if (preg_match('/'.preg_quote($person->firstname).'\s+'.preg_quote($person->surname).'/', $this->_addresses['forwarded-to'])) {
				$this->logger->debug('(Forwarded) Email is TO User (likely match on name: '.$this->_addresses['forwarded-to'].')');
				$this->_direction = self::DIRECTION_INCOMING;
				$this->_user_address = $this->_addresses['forwarded-to'];
				$this->_origin = $this->_addresses['forwarded-from'];
				$this->_destinations[] = $this->_addresses['forwarded-to'];
				
			} elseif (preg_match('/'.preg_quote($person->firstname).'\s+'.preg_quote($person->surname).'/', $this->_addresses['forwarded-cc'])) {
				$this->logger->debug('(Forwarded) Email is CC User (likely match on name: '.$this->_addresses['forwarded-cc'].')');
				$this->_direction = self::DIRECTION_INCOMING;
				$this->_user_address = $this->_addresses['forwarded-cc'];
				$this->_origin = $this->_addresses['forwarded-from'];
				$this->_destinations[] = $this->_addresses['forwarded-cc'];
				
			} elseif (preg_match('/'.preg_quote($person->firstname).'\s+'.preg_quote($person->surname).'/', $this->_addresses['forwarded-from'])) {
				$this->logger->debug('(Forwarded) Email is FROM User (likely match on name: '.$this->_addresses['forwarded-from'].')');
				$this->_direction = self::DIRECTION_OUTGOING;
				$this->_user_address = $this->_addresses['forwarded-from'];
				$this->_origin = $this->_addresses['forwarded-from'];
				$this->_destinations[] = $this->_addresses['forwarded-to'];
				
			} elseif (preg_match('/'.preg_quote($person->firstname).'\s+'.preg_quote($person->surname).'/', $this->_addresses['to'])) {
				$this->logger->debug('(Not Forwarded) Email is TO User (likely match on name: '.$this->_addresses['to'].')');
				$this->_direction = self::DIRECTION_INCOMING;
				$this->_user_address = $this->_addresses['to'];
				$this->_origin = $this->_addresses['from'];
				$this->_destinations[] = $this->_addresses['to'];
				
			} elseif (preg_match('/'.preg_quote($person->firstname).'\s+'.preg_quote($person->surname).'/', $this->_addresses['from'])) {
				$this->logger->debug('(Not Forwarded) Email is FROM User (likely match on name: '.$this->_addresses['from'].')');
				$this->_direction = self::DIRECTION_OUTGOING;
				$this->_user_address = $this->_addresses['from'];
				$this->_origin = $this->_addresses['from'];
				$this->_destinations[] = $this->_addresses['to'];
				
			} else {
				$this->logger->debug("Could not find user's name in mail'");
			}
		}
			
		// STILL undecided?
		if (empty($this->_direction)) {
			// Default to main address
			$user_addresses->rewind();
			$this->_user_address = $user_addresses->current()->contact;
			
			$this->_origin = !empty($this->_addresses['forwarded-from']) ? $this->_addresses['forwarded-from'] : $this->_addresses['from'];
			$this->_destinations[] = !empty($this->_addresses['forwarded-to']) ? $this->_addresses['forwarded-to'] : $this->_addresses['to'];
		}
		
		if (empty($this->_origin)) {
			$this->logger->debug('Message missing origin: ' . print_r($this->_addresses, 1));
		} elseif (empty($this->_destinations)) {
			$this->logger->debug('Message missing destination: ' . print_r($this->_addresses, 1));
		}
		
		$this->_attachments = $this->extractAttachments();
	}
	
	/**
	 * Attempts to find a Person or Organisation by email address
	 *
	 * @param string $email
	 * @return Person|Organisation|false
	 */
	public function getContactByEmail(&$email) {
		$this->logger->debug('Getting contact by email: ' . $email);
		$db = DB::Instance();
		$person = new Person();
		$org = new Organisation();
		
		if (!preg_match(self::EMAIL_ADDRESS_PATTERN, strtolower($email), $matches)) {
			$this->logger->debug('Not an email address: ' . $email);
			
			// Try searching by name?
			$person = new Tactile_Person();
			$person_cc = new ConstraintChain();
			$person_cc->add(new Constraint('usercompanyid', '=', EGS::getCompanyId()));
			$person_cc->add(new Constraint("firstname||' '||surname", 'ILIKE', $email));
			$organisation = new Tactile_Organisation();
			$org_cc = new ConstraintChain();
			$org_cc->add(new Constraint('usercompanyid', '=', EGS::getCompanyId()));
			$org_cc->add(new Constraint("name", 'ILIKE', $email));
			
			if ($person->loadBy($person_cc)) {
				$this->logger->debug('Found likely matching Person by name: ' . $person->firstname . ' ' . $person->surname . ' (' . $person->id . ')');
				$person_addresses = new PersoncontactmethodCollection();
				$sh = new SearchHandler($person_addresses, false);
				$sh->addConstraint(new Constraint('type', '=', 'E'));
				$sh->addConstraint(new Constraint('person_id', '=', $person->id));
				$sh->setOrderby('main desc, name');
				$person_addresses->load($sh);
				if (count($person_addresses) > 0) {
					$email = $person_addresses->current()->contact;
					$this->logger->debug('Using email address: ' . $email);
				}
				return $person;
				
			} elseif ($organisation->loadBy($org_cc)) {
				$this->logger->debug('Found likely matching Org by name: ' . $organisation->name . ' (' . $organisation->id . ')');
				$org_addresses = new OrganisationcontactmethodCollection();
				$sh = new SearchHandler($org_addresses, false);
				$sh->addConstraint(new Constraint('type', '=', 'E'));
				$sh->addConstraint(new Constraint('person_id', '=', $organisation->id));
				$sh->setOrderby('main desc, name');
				$org_addresses->load($sh);
				if (count($org_addresses) > 0) {
					$email = $org_addresses->current()->contact;
					$this->logger->debug('Using email address: ' . $email);
				}
				return $organisation;
				
			} else {
				return false;
			}
		} else {
			$email = $matches[0];
		}
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('c.usercompanyid', '=', EGS::getCompanyId()));
		$cc->add(new Constraint('cm.type', '=', 'E'));
		$cc->add(new Constraint('lower(cm.contact)', '=', strtolower($email)));

		// Look for a Person first
		$qb = new QueryBuilder($db);
		$qb->select_simple(array('person_id'))
			->from('person_contact_methods cm')
			->left_join('people c', 'c.id = cm.person_id')
			->where($cc)
			->limit(1);
		
		$person_id = $db->getOne($qb->__toString());
		if (empty($person_id) || FALSE === $person->load($person_id)) {
			$this->logger->debug('No Person found');
			// No Person, try Organisations
			$qb = new QueryBuilder($db);
			$qb->select_simple(array('organisation_id'))
				->from('organisation_contact_methods cm')
				->left_join('organisations c', 'c.id = cm.organisation_id')
				->where($cc)
				->limit(1);
			
			$org_id = $db->getOne($qb->__toString());
			if (empty($org_id) || FALSE === $org->load($org_id)) {
				$this->logger->debug('No Organisation found, either');
				return false;
			} else {
				$this->logger->debug('Found Organisation: ' . $org->name . ' (' . $org->id . ')');
				return $org;
			}
		} else {
			$this->logger->debug('Found Person: ' . $person->firstname . ' ' . $person->surname . ' (' . $person->id . ')');
			return $person;
		}
	}
	
	/**
	 * Sends an email to the User informing them that a contact cannot be found for this email address
	 *
	 * @param string $email_address
	 * @param int $email_id
	 */
	public function sendMissingContactEmail($email_address, $email_id) {
		if (empty($this->_user_address)) {
			$this->logger->debug('Unable to send missing contact email, User Address is empty!');
			return false;
		}
		
		if (!preg_match(self::EMAIL_ADDRESS_PATTERN, strtolower($email_address), $matches)) {
			$this->logger->debug('Not an email address: ' . $email_address);
		} else {
			$email_address = $matches[0];
		}
		
		$send_missing_contact_email = EmailPreference::getSendStatus('missing_contact_email', EGS::getUsername());
		if ($send_missing_contact_email) {
			$this->logger->debug('Sending missing contact email to: ' . $this->_user_address);
			$this->_sendMail(
				$this->_user_address,
				'dropbox_missing_contact',
				'Tactile CRM: Contact Missing',
				array(
					'subject'	=> utf8_encode($this->_message->subject),
					'recipient'	=> $email_address,
					'email_id'	=> $email_id,
					'domain'	=> Omelette::getUserSpace()
				)
			);
		} else {
			$this->logger->debug('Missing contact emails disabled for this user');
		}
	}
	
	/**
	 * Saves the email against a Person/Organisation
	 *
	 * @param Person|Organisation $attache
	 * @return Email
	 */
	public function saveEmail($attache, $email_address) {
		$this->logger->debug('Executing saveEmail() with ' . ($attache === FALSE ? 'FALSE' : ($attache->get_name() . ' ' . $attache->id)) . ' and ' . $email_address);
		if (!preg_match(self::EMAIL_ADDRESS_PATTERN, strtolower($email_address), $matches)) {
			$this->logger->debug('Is this really an email address: ' . $email_address);
		} else {
			$email_address = $matches[0];
		}
		
		// Which date should we use as the 'received' time?
		if ($this->_is_forwarded) {
			if (preg_match('/Date:[ \t]*([^\n\r]+)/i', $this->_getDecodedContent(), $matches)) {
				$this->logger->debug('Using forwarded date: ' . $matches[1]);
				if (FALSE === ($date = strtotime($matches[1]))) {
					$this->logger->debug('Nope, couldn\'t understand that date, using system received instead: ' . $this->_message->date);
					$date = strtotime($this->_message->date);
				}
			} else {
				$this->logger->debug('Forwarded, but can\'t find date. Using system received instead: ' . $this->_message->date);
				$date = strtotime($this->_message->date);
			}
		} else {
			$this->logger->debug('Using system received date: ' . $this->_message->date);
			$date = strtotime($this->_message->date);
		}

		$subject = is_null($this->_encoding) ? $this->_message->subject : @iconv($this->_encoding, 'UTF8//TRANSLIT', $this->_message->subject);
		$email_data = array(
			'subject'			=> $subject,
			'body'				=> $this->_plaintext,
			'received'			=> date('Y-m-d H:i:s', $date)
			//'received_hours'	=> strftime('%H', $date),
			//'received_minutes'	=> strftime('%M', $date)
		);
		if (!empty($this->_opportunity_id)) {
			$email_data['opportunity_id'] = $this->_opportunity_id;
		}
		
		if ($attache instanceof Person) {
			$email_data['person_id'] = $attache->id;
			$email_data['organisation_id'] = $attache->organisation_id;
		} elseif ($attache instanceof Organisation) {
			$email_data['organisation_id'] = $attache->id;
		} elseif (FALSE === $attache) {
			// Unassigned, or just assigned to an Opportunity
		} else {
			throw new Exception('Unknown attache type!');
		}
		
		switch ($this->_direction) {
			case self::DIRECTION_INCOMING:
				$email_data['email_from'] = empty($email_address) ? false :
					($this->extractEmailAddress($email_address) ? $this->extractEmailAddress($email_address) : $email_address);
				$email_data['email_to'] = empty($this->_user_address) ? false :
					($this->extractEmailAddress($this->_user_address) ? $this->extractEmailAddress($this->_user_address) : $this->_user_address);
				break;
			case self::DIRECTION_OUTGOING:
			default:
				$email_data['email_from'] = empty($this->_origin) ? false :
					($this->extractEmailAddress($this->_origin) ? $this->extractEmailAddress($this->_origin) : $this->_origin);
				$email_data['email_to'] = empty($email_address) ? false :
					($this->extractEmailAddress($email_address) ? $this->extractEmailAddress($email_address) : $email_address);
				break;
		}
		
		$this->logger->debug('Saving ' . $this->_direction . ' email with ' . $email_address);
		$errors = array();
		$email = DataObject::Factory($email_data, $errors, 'Email');
		if (FALSE === $email || FALSE === $email->save()) {
			throw new MailParserException('Error saving email: ' . print_r($errors, true) . print_r($email_data, true));
		}
		$this->logger->debug('Email saved: ' . $email->id);
		
		return $email;
	}
	
	/**
	 * Establish what to link this email to, and create and save the emails themselves
	 *
	 */
	public function save() {
		$db = DB::Instance();
		
		// Optionally attach to an Opportunity
		$this->_opportunity_id = null;
		if (preg_match(self::ACTION_PATTERN_OPPORTUNITY, $this->_action, $matches)) {
			$opp_id = $matches[1];
			$opp = new Tactile_Opportunity();
			if (FALSE === $opp->load($opp_id)) {
				throw new NoOpportunityFoundMailException('Failed to load Opportunity with ID: ' . $opp_id);
			}
			
			$this->logger->debug('Email belongs to opportunity ID ' . $opp_id);
			$this->_opportunity_id = $opp_id;
		}
		
		// Now that we have (hopefully) determined direction, who do we attach it to?
		switch ($this->_direction) {
			case self::DIRECTION_OUTGOING: {
				$this->logger->debug('Outgoing message has ' . count($this->_destinations) . ' destination(s)');
				
				// Attach to recipient(s)
				foreach ($this->_destinations as $destination) {
					$this->logger->debug('Destination: ' . $destination);
					if (!preg_match('/'.preg_quote($this->_addresses['delivered-to']).'/', $destination)) {
						$contact = $this->getContactByEmail($destination);
						$email = $this->saveEmail($contact, $destination);
						
						if (FALSE !== $contact) {
							$this->logger->debug('Attaching to ' . get_class($contact) . ' ' . $contact->id . ' via ' . $destination);
						} else {
							// Failed to find contact
							$this->logger->debug('Missing Contact: ' . $destination);
							if (empty($this->_opportunity_id)) {
								$this->sendMissingContactEmail($destination, $email->id);
							}
						}
						
						$this->_emails[] = $email;
					} else {
						$this->logger->debug("Don't want to save against dropbox address!");
					}
				}
				break;
			}
			
			case self::DIRECTION_INCOMING: {
				$this->logger->debug('Incoming message origin: ' . $this->_origin);
				if (!preg_match('/'.preg_quote($this->_addresses['delivered-to']).'/', $this->_origin)) {
					// Attach to sender
					$contact = $this->getContactByEmail($this->_origin);
					$email = $this->saveEmail($contact, $this->_origin);
					
					if (FALSE !== $contact) {
						$this->logger->debug('Attaching to ' . get_class($contact) . ' ' . $contact->id . ' via ' . $this->_origin);
					} else {
						// Failed to find contact
						$this->logger->debug('Missing Contact: ' . $this->_origin);
						if (empty($this->_opportunity_id)) {
							$this->sendMissingContactEmail($this->_origin, $email->id);
						}
					}
					
					$this->_emails[] = $email;
				} else {
					$this->logger->debug("Don't want to save against dropbox address!");
				}
				break;
			}
			
			default: {
				// Unknown direction. Attempt to attach to either
				$this->logger->debug('Directionless message origin: ' . $this->_origin);
				$contact = $this->getContactByEmail($this->_origin);
				
				if (FALSE !== $contact) {
					$email = $this->saveEmail($contact, $this->_origin);
					$this->logger->debug('Attaching to ' . get_class($contact) . ' ' . $contact->id . ' via ' . $this->_origin);
					$this->_emails[] = $email;
				} else {
					// Failed to find from origin, try destination(s)
					$this->logger->debug('Directionless message has ' . count($this->_destinations) . ' destination(s)');
					
					$has_attached = false;
					foreach ($this->_destinations as $destination) {
						$this->logger->debug('Destination: ' . $destination);
						$regex = '/'.preg_quote($this->_addresses['delivered-to']).'/';
						if (!preg_match($regex, $destination)) {
							$contact = $this->getContactByEmail($destination);
							$email = $this->saveEmail($contact, $destination);
							
							if (FALSE !== $contact) {
								$has_attached = true;
								$this->logger->debug('Attaching to ' . get_class($contact) . ' ' . $contact->id . ' via ' . $destination);
							} else {
								// Failed to find contact
								$this->logger->debug('Missing Contact: ' . $destination);
								if (empty($this->_opportunity_id)) {
									$this->sendMissingContactEmail($destination, $email->id);
								}
							}
							$this->_emails[] = $email;
						} else {
							$this->logger->debug("Don't want to save against dropbox address!");
						}
					}
					if (!$has_attached) {
						// Last resort!
						$this->logger->debug('For some reason, we could not attach to anything');
					}
				}
			}
		}
		
		// May have failed to attach to a contact, but still has an opportunity_id
		if ($this->_opportunity_id && empty($this->_emails)) {
			$this->logger->debug('Failed to save to a contact, but still going to attach to opp ' . $this->_opportunity_id);
			$email = $this->saveEmail(FALSE, $this->_addresses['delivered-to']);
			$has_attached = true;
			$this->_emails[] = $email;
		}
		
		// May have failed to attach to anything at all (e.g. was a forwarded messasge but failed to parse the forwarded-from/to headers)
		if (empty($this->_emails)) {
			$this->logger->debug('Failed to save to a contact, will save as unassigned');
			$email = $this->saveEmail(FALSE, $this->_user_address);
			$this->_emails[] = $email;
		}
	}
	
	/**
	 * Upload attachments from email to S3
	 *
	 */
	public function saveAttachments() {
		if (!empty($this->_attachments)) {
			$this->logger->debug("Filing " . count($this->_attachments) . " attachments...");

			foreach ($this->_attachments as $attachment) {
				$tempnam = tempnam('/tmp/', 'egs2mail');
				$this->logger->debug("Writing " . $attachment['name'] . " to disk at " . $tempnam);
				file_put_contents($tempnam, $attachment['content']);

				$file_data = array(
					'name' => $attachment['name'],
					'type' => $attachment['type'],
					'size' => filesize($tempnam),
					'tmp_name' => $tempnam
				);

				if (count($this->_emails) < 1) {
					$this->logger->debug('No emails to attach file to!');
				}
				foreach ($this->_emails as $email) {
					if ($email === FALSE) {
						$this->logger->debug('This is a FALSE, not an email!');
						continue;
					}
					$this->logger->debug('Preparing to attach to email ' . $email->id);
					$file_data['organisation_id']  = $email->organisation_id;
					$file_data['person_id']  = $email->person_id;
					$file = new S3Attachment($email);
					$errors = array();
					if (defined('PRODUCTION') && PRODUCTION) {
						try {
							$s3file = $file->attachFile($file_data, $errors);
							if (!empty($errors)) {
								throw new MailParserException($this->source, 'Error saving attachment: ' . print_r($errors,true));
							}
							$this->logger->debug('Attached via S3: ' . $s3file->id);
						} catch (Zend_Http_Client_Adapter_Exception $e) {
							$this->logger->warn('Error while saving S3 file: ' . $e->getMessage());
						}
					} else {
						$this->logger->debug('DEVELOPMENT MODE: Skipping S3 upload');
					}
				}

				$this->logger->debug("Unlinking " . $tempnam . "...");
				unlink($tempnam);
			}
		} else {
			$this->logger->debug('No attachments to link');
		}
	}
	
	protected function _sendMail($recipient, $template, $subject, $variables) {
		$this->logger->debug('Sending email to: '.$recipient);
		
		$mail = new Omelette_Mail($template);

		foreach ($variables as $key => $value) {
			$mail->getView()->set($key, $value);
		}

		$mail->getMail()
			->addTo($recipient)
			->setFrom(TACTILE_EMAIL_FROM, TACTILE_EMAIL_NAME)
			->setSubject($subject);
		if (!PRODUCTION && defined('DEBUG_EMAIL_ADDRESS')) {
			$mail->addBcc(DEBUG_EMAIL_ADDRESS);
		}
		$mail->send();
	}
	
}
