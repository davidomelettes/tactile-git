<?php
require_once 'Zend/Rest/Client.php';
require_once 'Service/Highrise/Exception.php';
require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Null.php';

/**
 * @author Paul M Bain
 * @package Service_Highrise
 */
class Service_Highrise extends Zend_Service_Abstract {
	
	protected $_siteaddress;
	protected $_username;
	protected $_password;
	
	/**
	 * @var path to the call
	 */
	protected $_path;
	
	protected $_logger=null;
	
	const ENDPOINT = 'http://%s.highrisehq.com';
	
	
	/**
	 * Constructs a new Highrise Service
	 * 
	 * @param String $siteaddress The zendesk site-address (x.zendesk.com)
	 * @param string $email The email address for the user
	 * @param string $password The password for the user
	 * 
	 */
	public function __construct($siteaddress, $username, $password, $logger=null){
		$this->_siteaddress = $siteaddress;
		$this->_username = $username;
		$this->_password = $password;
		$this->_logger = is_null($logger) ? new Zend_Log(new Zend_Log_Writer_Null()) : $logger;		
	}
	
	/*
	 * Get the URL to connect to
	 */
	private function _getUri($path = null) {
		$uri = sprintf(self::ENDPOINT, $this->_siteaddress);
		if (!is_null($this->_path)){
			$uri .= "/".$this->_path;
		}
		return $uri.".xml";
	}
	
	/**
	 * @var String $path to the search
	 */
	public function setPath($path){
		$this->_path = $path;
	}
	
	/**
	 * Run the request
	 */
	public function execute(){
		$url = $this->_getUri();
		$client = $this->getHttpClient();
		$items = array();
		$n = 0;
		
		do {
			$uri = $url;
			if($n > 0){
				if (preg_match('/\?/', $uri)) {
					$uri .= "&n=" . ($n * 500);
				} else {
					$uri .= "?n=" . ($n * 500);
				}
			}
			
			$client->setUri($uri);
			$client->setAuth($this->_username, $this->_password, Zend_Http_Client::AUTH_BASIC);
			
			try {
				$this->_logger->debug('Fetching: ' . $uri);
				$response = $client->request();
			} catch (Zend_Http_Client_Exception $e) {
				$this->_logger->warn($e->getMessage());
				return false;
			}
			if (!$response->isSuccessful()) {
				$this->_logger->debug($response->getMessage());
				return false;
			}
			$xml = simplexml_load_string($response->getBody());
			if (count($xml)){
				$this->_logger->debug(count($xml) . ' item(s) in response');
				foreach($xml as $x){
					$items[] = $x;
				}
			}
			$n++;
		} while (count($xml) >= 500);
		
		return $items;
	}
}
