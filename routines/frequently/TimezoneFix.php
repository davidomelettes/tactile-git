<?php
/* Author: Jake */ 

class TimezoneFix extends EGSCLIApplication {
	
	private $db;
	protected $logger;
	
	public function go() {
		$this->db = DB::Instance();
		
		require_once 'Zend/Log.php';
		require_once 'Zend/Exception.php';

		// Check if incorrect timezones
		$query = "
			SELECT username
			FROM users
			WHERE
				timezone ='Europe/London''::character varying'
		";

		$badTimezones = $this->db->getArray($query);

		if(sizeof($badTimezones) > 0) {
			$logger = new Zend_Log(new Log_Writer_Mail(DEBUG_EMAIL_ADDRESS, 'Users found with invalid timezones'));
			$logger->crit(print_r($badTimezones, true));
			
			$query = "
				UPDATE users
				SET
					timezone ='Europe/London'
				WHERE
					timezone='Europe/London''::character varying'
			";

			$this->db->query($query);
		}
	}
}
