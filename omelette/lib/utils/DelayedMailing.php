<?php
/**
 * Responsible for sending an email from TaskRunner
 * @author gj
 * @package Tasks
 */
class DelayedMailing extends DelayedTask {
	
	/**
	 * Set the username that the email is to be sent to
	 * 
	 * This makes more sense than just an email address so that we can grab other details if we want to
	 */
	public function setTo($username) {
		$this->data['username'] = $username;
	}
	
	/**
	 * Specify the 'type' of mail. This affects which template is used
	 */
	public function setType($type) {
		$this->data['type'] = $type;
	}
	
	/**
	 * Specify find=>replace pairs to fill the template with
	 */
	public function setParams($params) {
		$this->data['params'] = $params;
	}
	
	/**
	 * Set the filename of a file to be attached to the email
	 *
	 * @param String $filename
	 */
	public function setAttachment($filename) {
		$this->data['attachment'] = $filename;
	}
	
	/**
	 * Do the template-rendering and sending
	 * 
	 * @return void
	 */
	public function execute() {
		set_include_path(get_include_path().PATH_SEPARATOR.LIB_ROOT);
		require_once LIB_ROOT.'Zend/Mail.php';

		$mail = new Omelette_Mail($this->data['type']);

		foreach($this->data['params'] as $find=>$replace) {
			if(is_array($replace)) {
				$replace = implode(',',$replace);
			}
			$mail->getView()->set($find,$replace);
		}

		$mail->getMail()
				->addTo('greg.jones@senokian.com')
				->setSubject('Tactile CRM: Status Update')
				->setFrom('robot@tactilecrm.com','Your Friendly Tactile Robot');
		
		if(isset($this->data['attachment'])) {
			$attachment = $this->data['attachment'];
			$body = file_get_contents($attachment);
			$mail->getMail()->createAttachment($body,	//contents of file (can be a stream)
									'text/csv',	//file-type
									Zend_Mime::DISPOSITION_ATTACHMENT,	//default seems not to work?
									Zend_Mime::ENCODING_BASE64,			//likewise
									'errors.csv'); //the name of the file
		}
		$mail->send();
		$this->cleanup();
	}
	
	/**
	 * We want to remove the attachment file on top of the job file
	 * @return void
	 */
	protected function cleanup() {
		if(isset($this->data['attachment'])) {
			unlink($this->data['attachment']);
		}
		parent::cleanup();
	}
}
?>
