<?php
require_once 'Zend/Mail.php';

class NotificationEmail {
	
	/**
	 * @var Zend_Mail
	 */
	protected $mail;
	
	protected $params = array();
	
	public static $default_emails_from;
	
	public static $override_emails_to;
	
	public function __construct($subject, $recipient, $from = null, $mail = null) {
		if(is_null($from)) {
			if(!is_null(self::$default_emails_from)) {
				$from = self::$default_emails_from;
			}
			else {
				throw new Exception("No from-address set, and no default set");
			}
		}
		if(is_null($mail)) {
			$mail = new Zend_Mail();	
		}
		if(!is_null(self::$override_emails_to)) {
			$recipient = self::$override_emails_to;
		}
		$this->mail = $mail;
		$this->mail->setSubject($subject);
		$this->mail->addTo($recipient);
		$this->mail->setFrom($from, 'Website Notification');
	}
	
	public function set($key, $val) {
		$this->params[$key] = $val;
	}
	
	public function send() {
		$body = '';
		foreach($this->params as $key=>$val) {
			$body .= $key . ' : ' . $val ."\n";
		}
		$body.="\n";
		$this->mail->setBodyText($body)->send();
	}
	
}
?>