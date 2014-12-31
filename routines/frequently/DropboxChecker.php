<?php
AutoLoader::Instance()->addPath(APP_CLASS_ROOT.'mail/');
class DropboxChecker extends EGSCLIApplication {

	public function go() {
		return false; // Disabling dropbox checker
		$processor = new MailboxProcessor($this->config['mail'], $this->logger, $this->config['delete_messages']);
		$action = new EmailParser($this->logger);
		$processor->invoke($action);
	}
	
	protected function checkConfig() {
		$mail_conf = $this->config['mail'];
		if(empty($mail_conf['host']) || empty($mail_conf['user']) || empty($mail_conf['password'])) {
			throw new Exception('Config needs server, username and password');
		}
		if(!isset($this->config['delete_messages'])) {
			$this->config['delete_messages'] = true;
		}
	}
}

?>
