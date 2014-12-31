<?php
require_once 'Zend/Mail/Storage/Imap.php';
require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Null.php';

class MailboxProcessor {
	
	protected $config = array();
	
	/**
	 * The Logger instance
	 *
	 * @var Zend_Log
	 */
	protected $logger;
	
	protected $storage;
	
	protected $delete_messages = true;
	
	public function __construct($config, $logger = null, $delete_messages = true) {
		$this->config = $config;
		if(is_null($logger)) {
			$logger = new Zend_Log();
			$logger->addWriter(new Zend_Log_Writer_Null());
		}
		$this->logger = $logger;
		$this->delete_messages = $delete_messages;
	}
	
	public function setStorage(Zend_Mail_Storage_Abstract $storage) {
		$this->storage = $storage;
	}
	
	/**
	 * Return a storage if set, or create a new one
	 *
	 * @return Zend_Mail_Storage_Imap
	 */
	public function getStorage() {
		if(is_null($this->storage)) {
			$this->logger->debug('Attempting to connect to '.$this->config['host']);
			try {
				$this->storage = new Zend_Mail_Storage_Imap($this->config);
				$foo = new Zend_Mail_Protocol_Imap();
			}
			catch(Zend_Mail_Exception $e) {
				$this->logger->warn(print_r($this->config,true));
				$this->logger->warn("Connection failed: ".$e->getMessage());
				return false;
			}
			$this->logger->debug("Connected");
		}
		
		return $this->storage;
	}
	
	public function invoke($action) {
		$db = DB::Instance();
		$GO_START = time();
		$box = $this->getStorage();
		if($box===false) {
			return false;
		}
		$delete_queue = array();
		foreach($box as $id => $message) {
			$success = $action->apply($id, $message);
			if($success === false) {
				try {
					$box->copyMessage($id,'Problems');
				}
				catch(Zend_Mail_Storage_Exception $e) {
					$box->createFolder('Problems');
					$box->copyMessage($id,'Problems');
				}
				$this->logger->debug("Message moved to Problems folder");
			}
			if($this->delete_messages === true) $delete_queue[] = $id; 
		}
		$delete_queue = array_reverse($delete_queue);
		foreach($delete_queue as $id) {
			$box->removeMessage($id);
		}
		$GO_STOP = time();
		$this->logger->info('Dropbox parser took ' . ($GO_STOP - $GO_START) . ' to run.');
	}
	
}
?>