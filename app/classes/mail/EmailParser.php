<?php
require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Null.php';

class MuppetMitigationException extends Exception {
}

class MailParserException extends Exception {
    protected $source;
	
	public function __construct($source, $message = '') {
        $this->source = $source;
        parent::__construct($message);
	}
	
	public function getSource() {
		return $this->source;
    }
}
class DropboxUserAddressMismatchException extends MailParserException {
	protected $dropbox;
	protected $validAddresses;
	
	public function __construct($dropbox, $validAddresses) {
		$this->dropbox = $dropbox;
		$this->validAddresses = $validAddresses;
	}
	
	public function getDropbox() {
		return $this->dropbox;
	}
	
	public function getValidAddresses() {
		return $this->validAddresses;
	}
}
class MalformedForwardedMailException extends MailParserException {}
class NoDropboxIdException extends MailParserException {
	public function __construct() {
		// NOOP
	}
}
class MissingContactMethodException extends MailParserException {
	protected $recipient;
	
	public function __construct($source, $recipient) {
		$this->source = $source;
		$this->recipient = $recipient;
	}
	
	public function getRecipient() {
		return $this->recipient;
	}
}

class EmailParser {
	/**
	 * Holds email models created by parsing for each recipient
	 */
	protected $emails = array();
	
	protected $attachments = array();
	
	protected $message_recipients = array();
	
	protected $plaintext = "";
	
	protected $logger;

	protected $message;
	
	protected $origin;
	
	protected $dropbox;
	
	protected $domain;
	
	protected $date;
	
	protected $email_regexp = '\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b';
	//protected $email_regexp = '[A-Za-z0-9\.\-_\+]+@[A-Za-z0-9\.\-_\+]+'; // This is the old one (pre Matt days)
	
	public function __construct(Zend_Log $logger = null) {
		if(is_null($logger)) {
			$logger = new Zend_Log();
			$logger->addWriter(new Zend_Log_Writer_Null());
		}
		$this->logger = $logger;
	}
	
	function apply($id,Zend_Mail_Message $message) {
		$this->plaintext = "";
		$this->attachments = array();
		$this->emails = array();
		$this->message_recipients = array();
		$db = DB::Instance();
		try {
			$this->parseMessage($message);
			$this->save();
			$this->saveAttachments();
		}
		catch (DropboxUserAddressMismatchException $e) {
			// Find correct address for given dropbox
			$email = $db->getOne('SELECT pcm.contact FROM users u LEFT JOIN person_contact_methods pcm ON pcm.person_id = u.person_id WHERE pcm.type = \'E\' AND pcm.main AND u.dropboxkey = ' . $db->qstr($e->getDropbox()));
			$username = $db->getOne('SELECT username FROM users WHERE dropboxkey = ' . $db->qstr($e->getDropbox()));
			$user = new User();
			$user->load($username); 
			$this->sendMail(
				$email,
				'dropbox_address_mismatch',
				'Tactile CRM: Dropbox Mismatch',
				array(
					'user' => $user
				)
			);
			return false;
		}
		catch (MalformedForwardedMailException $e) {
			$subject = $message->subject;
			$this->sendMail(
				$e->getSource(),
				'dropbox_malformed_forward',
				'Tactile CRM: Forwarded Mail Malformed',
				array(
					'subject' => $subject
				)
			);
			return false;
		}
		catch (NoDropboxIdException $e) {
			return false;
		}
		catch (MailParserException $e) {
			$this->logger->warn('Message could not be processed: ' . $e->getMessage());
			$this->logger->warn(get_class($e));
			return false;
		}
		catch (MuppetMitigationException $e) {
			return false;
		}
		return true;
	}
	
	/**
	 * Runs initial parsing of message without saving
	 */
	function parseMessage(Zend_Mail_Message $message) {
		$flash = Flash::Instance();
		$this->message = $message;
		
		$dropbox_address = $this->message->getHeader('Delivered-To', 'string');
		
		// Discover which dropbox/domain mail was sent to
		$dropbox = $this->findDropbox($dropbox_address);
		$this->dropbox = $dropbox;
		$domain = $this->findDomain($dropbox_address);
		$this->domain = $domain;
		if($dropbox === false) {
			// This message does not have a dropbox ID.
			$this->logger->debug('Skipping message, not for us.');
			throw new NoDropboxIdException();
		}
		$this->logger->debug('Found message for dropbox ' . $dropbox . ', domain ' . $domain . ', subject ' . $this->message->getHeader('Subject', 'string'));
		
		// Find user and company to initialise EGS_COMPANY_ID
		$db = DB::Instance();
		$row = $db->getRow('SELECT u.username, u.person_id, uca.organisation_id FROM users u LEFT JOIN user_company_access uca ON uca.username = u.username  WHERE u.dropboxkey = ' . $db->qstr($dropbox));
		if (empty($row)) {
			// Dropbox key mustn't match
			$this->logger->debug('Could not find dropbox key ' . $dropbox . '.');
            throw new NoDropboxIdException();
		}
		EGS::setCompanyId($row['organisation_id']);
		EGS::setUsername($row['username']);
		$_SESSION['username'] = $row['username'];
		
		// Where should the email be from?
		$user_addresses = new PersoncontactmethodCollection();
		$sh = new SearchHandler($user_addresses, false);
		$sh->addConstraint(new Constraint('person_id', '=', $row['person_id']));
		$user_addresses->load($sh);
		
		$foundFlag = false;
		foreach ($user_addresses as $user_address) {
			$match_count = preg_match('/' . preg_quote($user_address->contact) . '/i', $this->message->getHeader('From', 'string'), $matches);
			if ($match_count > 0) {
				$foundFlag = true;
				$this->origin = $user_address->contact;
				$this->source = $user_address->contact;
				break;
			}
		}
		
        if (!$foundFlag) {
			// Final check, is this tactile it's coming from?
            if ($this->message->getHeader('From', 'string') == '"Tactile" <robot@tactilecrm.com>') {
				$this->logger->debug('Detected loop mail - throwing exception to delete to prevent looping');
				throw new MuppetMitigationException();
			}
			else {
				// Email not from the appropriate address
				$this->logger->debug('Mail did not originate from the address we expected. (' . $this->message->getHeader('From', 'string') . ')');
				throw new DropboxUserAddressMismatchException($dropbox, $user_addresses);
			}
		}
		else {
			$this->logger->debug('Sender address checks out.');
			$this->logger->debug('Mail is for user '.$row['username']);
		}
		
		// Determine if this mail was sent to the box directly
		$regex = '/tactile\+' . preg_quote($dropbox) . '@' . preg_quote($domain) . '/i';
		try {
			$match_count = preg_match($regex, $this->message->getHeader('To', 'string'), $matches);
		} catch (Zend_Mail_Exception $e) {
			// Argh! @de
			$this->logger->debug("No 'To' header!");
			
			$match_count = false;
		}
		if ($match_count === 0) {
			$this->logger->debug('Email has been BCC to us.');

			// Must be a sent mail in this case
			$this->direction = 'O';

			// Date will be that of the sent mail
			$this->date = strtotime($this->message->date);

			// Retreive all recipients
			preg_match_all('/'.$this->email_regexp.'/i', $this->message->getHeader('To', 'string'), $matches);
			$this->message_recipients = $matches[0];
		}
		else {
			$this->logger->debug('Not a BCC, we need to search for a forwarded mail.');
			
			// Find the To: and From: part of the forwarded mail
			unset($forwarded_to);
			$match_count = preg_match('/To:[ \t]+([^\n\r]+)/i', $this->message->getContent(), $matches);
			if ($match_count === 1) {
				$forwarded_to = $matches[1];
				$this->logger->debug('Forwarded to: '.$forwarded_to);
			} else {
				$forwarded_to = '';
				$this->logger->debug('Could not parse Forwarded-to');
			}
			
			unset($forwarded_from);
			$match_count = preg_match('/From:[ \t]+([^\n\r]+)/i', $this->message->getContent(), $matches);
			if ($match_count === 1) {
				$forwarded_from = $matches[1];
				$this->logger->debug('Forwarded from: '.$forwarded_from);
			} else {
				$forwarded_from = '';
				$this->logger->debug('Could not parse Forwarded-from');
			}
			
			// Attempt to find the original date of the email
			if(preg_match('/Date:[ \t]*([^\n\r]+)/i', $this->message->getContent(), $matches)) {
				$this->date = strtotime($matches[1]);
			}
			else {
				$this->date = strtotime($this->message->date);
			}
			
			$sentFlag = false;
			foreach ($user_addresses as $user_address) {
				$match_count = preg_match('/' . preg_quote($user_address->contact) . '/', $forwarded_from, $matches);
				if ($match_count > 0) {
					$sentFlag = true;
					$this->origin = $user_address->contact;
					break;
				}
			}
			$recvFlag = false;
			foreach ($user_addresses as $user_address) {
				$match_count = preg_match('/' . preg_quote($user_address->contact) . '/', $forwarded_to, $matches);
				if ($match_count > 0) {
					$recvFlag = true;
					$this->origin = $user_address->contact;
					break;
				}
			}
			
			$recips = "";
			if ($sentFlag) {
				$this->logger->debug('Message originally sent by user.');
				$this->direction = 'O';
				$recips = $forwarded_to;
			}
			elseif ($recvFlag) {
				$this->logger->debug('Message originally received by user.');
				$this->direction = 'I';
				$recips = $forwarded_from;
			}
			else {
				$this->logger->debug('Forwarded mail is not sent to or by this user.');
				$this->message_recipients = array();
				throw new MalformedForwardedMailException($this->source);
			}
			
			preg_match_all('/'.$this->email_regexp.'/i', $recips, $matches);
			if(count($matches[0]) === 0) $matches[0][] = '';
			$this->message_recipients = $matches[0];
			
			$this->logger->debug('Message recipients: ' . print_r($this->message_recipients,true));
		} 
		
		if ($this->message->isMultiPart()) {
			foreach (new RecursiveIteratorIterator($this->message) as $part) {
				
				// Parse content type line
				preg_match_all('/ ?([^;]+)/', $part->contentType, $matches);
				$params = array('type' => array_shift($matches[1]));
				foreach ($matches[1] as $pair) {
					$pair = split('=', $pair);
					$params[$pair[0]] = trim($pair[1], '"\'');
				}
				
				// Grab content deposition
				$content_disposition = null;
				try {
					$content_disposition = $part->getHeader('content-disposition');
				} catch (Zend_Mail_Exception $e) {
					// Why throw an exception for this not existing?!!
				}
				
				if ($params['type'] == 'text/plain' && $content_disposition === null) { // TODO This may be a little shakey
					$this->plaintext .= quoted_printable_decode($part->getContent());
				} else {
					if ($params['type'] == 'text/html') {
						$attachment = array(
							'name' => 'html-alternative.html',
							'type' => 'text/html'
						);
					} else {
						$attachment['type'] = $params['type'];
						$attachment['name'] = isset($params['name']) ? $params['name'] : "unnamed-attachment";
					
						if (empty($attachment['name'])) { $attachment['name'] = 'unnamed-attachment'; }
					}
					
                    $headers = $part->getHeaders();
					if (isset($headers['content-transfer-encoding']) && strtolower($headers['content-transfer-encoding']) == 'base64') {
						$attachment['content'] = base64_decode($part->getContent());
					} else {
						$attachment['content'] = quoted_printable_decode($part->getContent());
					}
					
					array_push($this->attachments, $attachment);
				}
			}
		}
		else {
			$this->plaintext = quoted_printable_decode($this->message->getContent());
		}
	}

	protected function findDropbox($email_address) {
		$regex = '/dropbox\+([A-Za-z0-9]+)\+[A-Za-z0-9]+@.*/';
		$match_count = preg_match($regex, $email_address, $matches);
		if ($match_count === 0) {
			return false;
		}
		return $matches[1];
	}
	
	protected function findDomain($email_address) {
		$regex = '/dropbox\+[A-Za-z0-9]+\+([A-Za-z0-9]+)@.*/';
		$match_count = preg_match($regex, $email_address, $matches);
		if ($match_count === 0) {
			return false;
		}
		return $matches[1];
	}
	
	public function save() {
		$flash = Flash::Instance();
		foreach ($this->message_recipients as $recipient) {
			if($recipient == "") $recipient = "<not_found>";
			$db = DB::Instance();
			$this->logger->debug($this->direction . ' => ' . $recipient);
			
			// Search for people
			$pc = $db->getOne("SELECT person_id FROM person_contact_methods pcm LEFT JOIN people p ON p.id = pcm.person_id WHERE p.usercompanyid = " . $db->qstr(EGS::getCompanyId()) . " AND pcm.type='E' AND pcm.contact ILIKE " . $db->qstr($recipient));
			
			$email_data = array(
				'subject' => $this->message->subject,
				'body' => $this->plaintext,
				'received' => date('Y-m-d H:i:s', $this->date)
				//'received_hours' => strftime('%H', $this->date),
				//'received_minutes' => strftime('%M', $this->date)
			);
			
			if ($pc === false) {
				$this->logger->debug("No such person. Saving into database anyway. Will be viewable from online dropbox.");
			}
			else {
				$person = new Person();
				$person->load($pc);
				$this->logger->debug("Filing against " . $pc . ".");
				$email_data['person_id'] = $person->id;
				$email_data['organisation_id'] = $person->organisation_id;
			}
			
			if ($this->direction == 'O') {
				$email_data['email_from'] = $this->origin;
				$email_data['email_to'] = $recipient;
			}
			elseif ($this->direction == 'I') {
				$email_data['email_from'] = $recipient;
				$email_data['email_to'] = $this->origin;
			}
			
			$errors = array();
			$email = DataObject::Factory($email_data, $errors, 'Email');
			
			if (!$email->save()) {
				throw new MailParserException($this->source, 'Error saving email: ' . print_r($errors, true));
			}
			
			$this->emails[] = $email;
			
			// If we didn't find the person, then we might need to send an email to the user (depending on the user's preferences)
			if ($pc === false) {
				$send_missing_contact_email = EmailPreference::getSendStatus('missing_contact_email', EGS::getUsername());
				if($send_missing_contact_email) {
					$this->sendMail(
						$this->source,
						'dropbox_missing_contact',
						'Tactile CRM: Contact Missing',
						array(
							'subject' => $this->message->subject,
							'recipient' => $recipient,
							'email_id' => $email->id,
							'domain' => $this->domain
						)
					);
				}
			}
			
		}
	}
	
	public function saveAttachments() {
		if (!empty($this->attachments)) {
			$this->logger->debug("Filing " . count($this->attachments) . " attachments...");

			foreach ($this->attachments as $attachment) {
				$tempnam = tempnam('/tmp/', 'egs2mail');
				$this->logger->debug("Writing " . $attachment['name'] . " to disk at " . $tempnam);
				file_put_contents($tempnam, $attachment['content']);

				$file_data = array(
					'name' => $attachment['name'],
					'type' => $attachment['type'],
					'size' => filesize($tempnam),
					'tmp_name' => $tempnam
				);

				foreach ($this->emails as $email) {
					$file_data['organisation_id']  = $email->organisation_id;
					$file_data['person_id']  = $email->person_id;
					$file = new S3Attachment($email);
					$errors = array();
					try {
						$s3file = $file->attachFile($file_data, $errors);
						if (!empty($errors)) {
							throw new MailParserException($this->source, 'Error saving attachment: ' . print_r($errors,true));
						}
					} catch (Zend_Http_Client_Adapter_Exception $e) {
						$this->logger->warn('Error while saving S3 file: ' . $e->getMessage());
					}
					
                    
				}

				$this->logger->debug("Unlinking " . $tempnam . "...");
				unlink($tempnam);
			}
		}		 
	}
	
	protected function sendMail($recipient, $template, $subject, $variables) {
		$this->logger->debug('Sending email to: '.$recipient);
		
		$mail = new Omelette_Mail($template);

		foreach ($variables as $key=>$value) {
			$mail->getView()->set($key, $value);
		}

		$mail->getMail()
			->addTo($recipient)
			->addBcc('matt.galloway@senokian.com')
			->setFrom(TACTILE_EMAIL_FROM,TACTILE_EMAIL_NAME)
			->setSubject($subject);
		$mail->send();
	}

}
?>
