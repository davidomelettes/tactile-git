<?php

require_once ('Zend/Log/Writer/Abstract.php');
require_once 'Zend/Log/Formatter/Simple.php';
require_once 'Zend/Mail.php';

class Log_Writer_Mail extends Zend_Log_Writer_Abstract {

	public static $email_from;
	
	/**
	 * The Zend_Mail object
	 *
	 * @var Zend_Mail
	 */
	protected $mail;
	
	/**
	 * An Array of log-lines, sent on shutdown()
	 *
	 * @var Array
	 */
	protected $lines = array();
	
	/**
	 * 
	 */
	function __construct($recipient, $subject = 'Tactile CRM: Notification') {
		if(is_null(self::$email_from)) {
			throw new Exception('Log_Writer_Mail::$email_from needs to be set');
		}
		$this->mail = new Zend_Mail();
		$this->mail->addTo($recipient)
			->setSubject($subject)
			->setFrom(self::$email_from);
			
		$this->_formatter = new Zend_Log_Formatter_Simple();
	}

	/**
	 * 
	 * @param array  $event  log data event 
	 * @return void 
	 * @see Zend_Log_Writer_Abstract::_write()
	 */
	protected function _write($event) {
		$line = $this->_formatter->format($event);
		$this->lines[] = $line;
	}
	
	public function shutdown() {
		if(count($this->lines)>0) {
			$this->mail->setBodyText(implode("\n",$this->lines));
			$this->mail->send();
		}
	}
	
	public static function factory($config) {
		
	}
}

?>
