<?php
class Service_Entanet_Response {
	
	const DIALLING = 'Dialling';
	const OK = 'Ok';
	
	const NO_EXTENSION = 'No Extension Defined';
	const NO_DOMAIN = 'No Domain Defined';
	const NO_NUMBER = 'No Number To Dial Defined';
	const INVALID_SECURITY_DETAILS = 'Security Details Incorrect';
	const INVALID_EXTENSION = 'Extension Does Not Exist';
	const NO_PERMISSION = 'No Permission For That Number';
	const NO_CALLS = 'No Calls';
	
	/**
	 * All the possible response-codes, to check things at least failed in a known/expected way
	 *
	 * @var Array
	 */
	protected static $_responseCodes = array(
		self::DIALLING,
		self::OK,
		self::NO_EXTENSION,
		self::NO_DOMAIN,
		self::NO_NUMBER,
		self::INVALID_SECURITY_DETAILS,
		self::INVALID_SECURITY_DETAILS,
		self::NO_PERMISSION,
		self::NO_CALLS
	);
	
	/**
	 * All response codes that indicate success
	 *
	 * @var Array
	 */
	protected static $_successCodes = array(
		self::DIALLING,
		self::OK
	);
	
	/**
	 * @var Zend_Http_Response
	 */
	protected $_httpResponse;
	
	/**
	 * The body of the response, i.e. the return-code
	 *
	 * @var String
	 */
	protected $_returnValue;
	
	protected $_calls = array();
	
	/**
	 * Create a wrapper around the Http_Response that comes back from Entanet
	 *
	 * @param Zend_Http_Response $httpResponse
	 */
	public function __construct(Zend_Http_Response $httpResponse) {
		$this->_httpResponse = $httpResponse;
		$body = $httpResponse->getBody();
		$lines = array_filter(split("\n", $body));
		foreach($lines as $line) {
			if(false !== strpos($line, ',')) {
				$split = explode(',', $line);
				if($split[0] == 'Error') {	//errors have a 2nd part with more info
					$this->_returnValue = $split[1];
				}
				else if(count($split) == 3) {	//call-info is in 3 columns
					$this->_calls[] = array(
						'from' => $split[0],
						'type' => $split[1],
						'timestamp' => $split[2]
					);
					$this->_returnValue = self::OK;
				}
			}
			else {	//'Dialling' is returned on its own
				$this->_returnValue = $line;
			}
		}
		if(!in_array($this->_returnValue, self::$_responseCodes)) {
			require_once 'Service/Entanet/Response/Exception.php';
			throw new Service_Entanet_Response_Exception("Invalid Response code: " . $this->_returnValue);
		}
	}
	
	/**
	 * Return true iff the response-value is a successful one
	 *
	 * @return boolean
	 */
	public function wasSuccessful() {
		return in_array($this->_returnValue, self::$_successCodes);
	}
	
	public function getCallerDetails() {
		return $this->_calls;
	}
	
	/**
	 * Get the return value
	 *
	 * @return String
	 */
	public function getReturnValue() {
		return $this->_returnValue;
	}
	
	/**
	 * Get back the HttpResponse
	 *
	 * @return Zend_Http_Response
	 */
	public function getHttpResponse() {
		return $this->_httpResponse;
	}
	
}
