<?php
require_once 'Zend/Mail.php';
require_once 'Zend/Log.php';
/**
 * Wrapper for using Zend_Mail in conjunction with MailView for sending template'd emails
 * - __call is implemented to pass through, but probably cleaner to access the methods by first using getMail() and getView()
 * - sending the mail causes an entry to be added to the mail_log table (which must exist!)
 * 
 * @author gj
 */
class Omelette_Mail {
	
	/**
	 * The name of the table that is logged to
	 * 
	 * @static String
	 */
	protected static $LOG_TABLE = 'mail_log';
	
	/**
	 * The mapping of the log-table fields to the Zend_Log 'events'
	 *
	 * @static Array
	 */
	protected static $LOG_TABLE_MAP = array(
		'name'=>'mail_name',
		'time_sent'=>'timestamp',
		'recipient'=>'recipient',
		'token'=>'token',
		'username'=>'username',
		'html'=>'html'
	);
	
	/**
	 * The name of the template to use
	 *
	 * @var String
	 */
	protected $template_name;
	
	/**
	 * The Zend_Mail instance used for actually sending the mail
	 *
	 * @var Zend_Mail
	 */
	protected $mail;
	
	/**
	 * The MailView instance used for the templating
	 *
	 * @var MailView
	 */
	protected $view;
	
	/**
	 * The username that this mail is being sent to, overrides the logged in user if set.
	 *
	 * @var String
	 */
	protected $username;
	
	/**
	 * Construct a new wrapper for the Zend_Mail/MailView combinations that are often used
	 * 
	 * @param String $name A 'key' for the email, this is the name of the template and what gets logged
	 * @param Zend_Mail optional $mail
	 * @param MailView optional $view
	 */
	public function __construct($template_name,$mail=null,$view=null) {
		$this->template_name = $template_name;
		
		$this->mail = ($mail!==null)?$mail:new Zend_Mail('UTF-8');
		$this->view = ($view!==null)?$view:new MailView();
		
		$this->view->setMailTemplate($this->template_name);
	}
	
	/**
	 * Pass through most functions to either Mail or View, in that order
	 *
	 * @param String $method
	 * @param Array $args
	 */
	public function __call($method,$args) {
		if(is_callable(array($this->mail,$method))) {
			call_user_func_array(array($this->mail,$method),$args);
		}
		elseif(is_callable(array($this->view,$method))) {
			call_user_func_array(array($this->view,$method),$args);
		}
		return $this;
	}
	
	/**
	 * Returns the Zend_Mail instance
	 *
	 * @return Zend_Mail
	 */
	public function getMail() {
		return $this->mail;
	}
	
	/**
	 * Return the MailView instance
	 *
	 * @return MailView
	 */
	public function getView() {
		return $this->view;
	}
	
	/**
	 * Set the username that the email is being sent to
	 *
	 * @param $username The username to set to override the logged in username (if present)
	 */
	public function setUsername($username) {
		$this->username = $username;
	}
	
	
	/**
	 * Return the username that the email is being sent to. If not overriden with setUsername then the
	 * currently logged in user will be returned. If there is no logged in user, null is returned.
	 *
	 * @return String
	 */
	public function getUsername() {
		// The set username overrides any other source of usernames
		if (isset($this->username)) return $this->username;
		
		// Try the EGS username
		try {
			return EGS::getUsername();
		} catch (Exception $e) {}
		
		// All else fails, just return null
		return null;
	}

	/**
	 * Generate a token based on supplied parameters and the current time.
	 *
	 * @return string
	 */
	public function generateToken($type, $recipient) {
		return sha1($type . $recipient . mktime());
	}
	
	/**
	 * Wraps Zend_Mail's send() to first set the body-text to be that of the template
	 * Includes logging of the email to the mail_log table
	 * 
	 * @param $actually_send Boolean Whether or not to actually perform the send() command
	 * @return void
	 */
	public function send($actually_send = true) {
		$writer = new Omelette_DBLogWriter(DB::Instance(),self::$LOG_TABLE,self::$LOG_TABLE_MAP);
		$logger = new Zend_Log($writer);
		$logger->setEventItem('mail_name',$this->template_name);
		$logger->setEventItem('username', $this->getUsername());
		
		$recipients = $this->mail->getRecipients();
		$token = self::generateToken($this->template_name, $recipients[0]);
		$logger->setEventItem('token', $token);
		$this->view->set('tracking_image', "http://" . TRACKING_HOST . "/{$token}");

		$logger->setEventItem('recipient',$recipients[0]);

		$this->mail->setBodyText($this->view->fetch());
		if ($this->view->hasHTML()) {
			$this->mail->setBodyHtml($this->view->fetchHTML());
			$logger->setEventItem('html', true);
		}
		
		$logger->info('x');	//blank as we only care about the column_map parts (and timestamp);
		
		if($actually_send!==false) {
			// Check message size before sending
			$total_size = strlen($this->mail->getBodyHtml(true)) + strlen($this->mail->getBodyText(true));
			foreach ($this->mail->getParts() as $part) {
				$total_size += strlen($part->getContent());
			}
			if ($total_size > 1048576 * 5) {
				$e_msg = 'MAIL EXCEEDED MAXIMUM SIZE: ' . $this->mail->getSubject() . ' - ' . $total_size . ' bytes';
				$tmp_file = tempnam(DATA_ROOT . 'tmp/', 'email_');
				file_put_contents($tmp_file, $this->mail->getBodyText(true));
				$emergency_mail = new Zend_Mail('UTF-8');
				$emergency_mail->setSubject($e_msg)
					->setBodyText("Mail size was $total_size, recipient was {$recipients[0]}, at " . date('Y-m-d H:i:s') . ', file: ' . $tmp_file)
					->addTo(DEBUG_EMAIL_ADDRESS)
					->send();
				throw new Exception($e_msg);
			}
			$this->mail->send();
		}
	}
	
	/**
	 * Rather than sending an email, this echos a version of the email to the screen (for testing)
	 *
	 * @return void
	 */
	public function preview() {
		echo "To: ".implode(',',$this->mail->getRecipients())."\n";
		echo "From: ".$this->mail->getFrom()."\n";
		echo "Subject: ".$this->mail->getSubject()."\n";
		echo "Body:\n";
		echo $this->view->fetch();
		echo "\n----------------------------------------------------\n";
		if ($this->view->hasHTML()) {
			echo $this->view->fetchHTML();
			echo "\n----------------------------------------------------\n\n";
		}
		echo "\n";
		
	}
	
}
?>
