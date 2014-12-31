<?php

set_include_path(get_include_path().PATH_SEPARATOR.LIB_ROOT);
require 'Zend/Crypt/Hmac.php';

/**
 * Responsible for generation of the 'Authorization' header for connections to Amazon's S3 Service
 * 
 * @author gj
 * @package S3
 */
class S3_Connection_Authorization {
	
	/**
	 * A template for the format of the Authorization value when sending the Authorization header
	 *
	 */
	const HEADER_TEMPLATE = 'AWS %s:%s';
	
	/**
	 * A template for the format of the Authorization part of a query-string request
	 *
	 */
	const QUERY_STRING_TEMPLATE = 'AWSAccessKeyId=%s&Signature=%s&Expires=%s';

	/**
	 * The Connection instance to generate authentication for
	 *
	 * @var S3_Connection
	 */
	protected $connection;
	
	
	/**
	 * The Access Key used to authenticate
	 *
	 * @var String
	 */
	protected $access_key;
	
		
	/**
	 *Constructor
	 * 
	 * @param S3_Connection $connection
	 * @param String $access_key 
	 */
	function __construct(S3_Connection $connection) {
		$this->connection = $connection;
	}
	
	/**
	 * Returns the 'Authorization' string for use in requests
	 *
	 * @return String
	 */
	public function generateHeaderAuthorization() {
		$auth_string = sprintf(
			self::HEADER_TEMPLATE, 
			$this->connection->getAccessKey(), 
			$this->generateSignature($this->generateHeaderStringToSign()));
		return $auth_string;
	}
	
	/**
	 * Returns the query-string parts that make up the authorization
	 *
	 * @param Integer optional $expires A timestamp after which requests will stop being valid, defaults to 10 seconds in the future 
	 * @return String
	 */
	public function generateQueryStringAuthorization($expires = null) {
		if(is_null($expires)) {
			$expires = strtotime('+10 seconds');
		}
		$query_string = sprintf(
			self::QUERY_STRING_TEMPLATE,
			$this->connection->getAccessKey(),
			urlencode($this->generateSignature($this->generateQueryStringToSign($expires))),
			$expires
		);
		return $query_string;
	}
	
	
	/**
	 * Generates the 'signature' part of the authentication string
	 * This is an encoded SHA1 hash of the 'string to sign' using the S3 'secret'
	 * 
	 * @return String
	 */
	public function generateSignature($string_to_sign) {
		$signature = $this->hex2b64(
			Zend_Crypt_Hmac::compute(
				$this->connection->getSecret(), 
				'SHA1', 
				$string_to_sign));
		return $signature;	
	}
	
	/**
	 * Generate the 'string to sign' for use in the Authorization header using different bits of the connection
	 *
	 * @return String
	 */
	public function generateHeaderStringToSign() {
		$conn = $this->connection;
		$client = $this->connection->getClient();
		
		$string_to_sign = $conn->getMethod()."\n";				//Method
		$string_to_sign .= "\n";								//Content-MD5
		$string_to_sign .= $client->getHeader('Content-Type')."\n";			//Content-Type
		$string_to_sign .= $client->getHeader('Date')."\n";			//Date
		$string_to_sign .= $this->generateAmzHeaderString($conn->getAmzHeaders());	//AmzHeaderString
		$string_to_sign .= $conn->getAmzResource();
		return $string_to_sign;		
	}
	
	/**
	 * Generate the 'string to sign' for use with query-string requests
	 *
	 * @param Integer $expires The expiry timestamp
	 * @return String
	 */
	public function generateQueryStringToSign($expires) {
		$conn = $this->connection;
		$string_to_sign = $conn->getMethod()."\n";
		$string_to_sign .= "\n\n"; 	//browser won't send content-type or content-md5
		$string_to_sign .= $expires."\n";
		$string_to_sign .= $conn->getAmzResource();
		return $string_to_sign;
	}
	
	/**
	 * Generates the header-string that forms part of the signature
	 * - sort the headers alphabetically (by key)
	 * - then concatenate as "{key}:{value}\n"
	 * 
	 * @param Array $headers
	 * @return unknown
	 */
	public function generateAmzHeaderString($headers) {
		ksort($headers);
		$header_string = '';
		foreach($headers as $key=>$value) {
			$header_string .= $key.':'.$value."\n";
		}
		return $header_string;
	}
	
	/**
	 * Utility method for converting the string into base64 for encoding
	 *
	 * @param String $str
	 * @return String
	 */
	private function hex2b64($str) {	
		$raw = '';
		for ($i=0; $i < strlen($str); $i+=2) {
			$raw .= chr(hexdec(substr($str, $i, 2)));
		}
		return base64_encode($raw);		
	}
}

?>
