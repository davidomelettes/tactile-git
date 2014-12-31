<?php

require_once 'Zend/Http/Client.php';

/**
 * Responsible for creating connections with Amazon's S3 service
 * 
 * @author gj
 * @package S3
 */
class S3_Connection {
	
	const TYPE_GET = 'GET';
	
	const TYPE_PUT = 'PUT';
	
	const TYPE_DELETE = 'DELETE';
	
	/**
	 * The prefix used for custom headers
	 *
	 */
	const AMZ_HEADER_PREFIX = 'x-amz-';
	
	/**
	 * The Amazon-supplied access key
	 *
	 * @var String
	 */
	protected $access_key;
	
	/**
	 * The Amazon-supplied 'secret'
	 *
	 * @var String
	 */
	protected $secret;
	
	/**
	 * The Zend_Http_Client instance used for the connection
	 *
	 * @var Zend_Http_Client
	 */
	protected $client;

	/**
	 * The type of HTTP request that needs to be made, defaults to 'GET'
	 *
	 * @var String
	 */
	protected $method = 'GET';
	
	/**
	 * The Amazon-specific headers that are used for requests
	 *
	 * @var unknown_type
	 */
	protected $amz_headers = array();
	
	/**
	 * The 'CanonicalizedResource' part of the signature
	 *
	 * @var String
	 */
	protected $amz_resource = '';
	
	/**
	 * Constructor
	 * 
	 * @param String $access_key The Amazon-supplied access key
	 * @param String $secret The Amazon-supplied secret
	 */
	function __construct($access_key, $secret) {
		$this->access_key = $access_key;
		$this->secret = $secret;
		$this->client = new Zend_Http_Client();
	}
	
	/**
	 * Performs the HTTP request to the URL specified, using the appropriate headers and authorization
	 *
	 * @param String $url The URL to send the request to ([...]amazonaws.com/{bucket}[...]?[...])
	 * @param String optional $body
	 * @return Zend_Http_Response
	 */
	function send($url, $body = '') {
		$client = $this->client;
		$client->setUri($url);
		$client->setHeaders('Date', gmdate(DATE_RFC1123));
		$client->setMethod($this->method);
		$client->setRawData($body);
		
		$auth = new S3_Connection_Authorization($this);
		
		$client->setHeaders('Authorization', $auth->generateHeaderAuthorization());
		
		$client->setHeaders($this->amz_headers);
		$response = $client->request();
		
		return $response;
	}
	
	/**
	 * Return a URL to be used a request by someone else
	 * 
	 * Instead of making the request, amazon allows you to construct a query-string that will allow
	 * someone else to make a request, within a given time-period (before $expires), the signature for such
	 * requests is constructed differently 
	 *
	 * @param String $url The URL to send the request to (e.g. http://s3.amazon.com/bucket1/file1)
	 * @param Int optional $expires A timestamp after which the request won't work
	 * @return String
	 */
	function buildQueryStringRequest($url, $expires = null) {
		$auth = new S3_Connection_Authorization($this);
		$qs = $auth->generateQueryStringAuthorization($expires);
		return $url.'?'.$qs;
	}
	

	/*Getters and setters*/
	
	/**
	 * Set one of the amazon-recognised custom HTTP headers. 
	 * The supplied key is automatically prefixed with 'x-amz-' 
	 *
	 * @param String $key
	 * @param String $value
	 */
	public function setAmzHeader($key, $value) {
		$this->amz_headers[self::AMZ_HEADER_PREFIX.$key] = $value;
	}
	
	/**
	 * Returns the array of 'x-amz-' headers that have been set
	 *
	 * @return Array
	 */
	public function getAmzHeaders() {
		return $this->amz_headers;
	}
	
	/**
	 * Set the 'resource' part of the request (/{bucket}/{object})
	 * This is used to calculate the authentication information
	 *
	 * @param String $resource
	 */	
	public function setAmzResource($resource) {
		$this->amz_resource = $resource;
	}
	
	/**
	 * Returns the 'resource' part of the request (/{bucket}/{object})
	 *
	 * @return String
	 */
	public function getAmzResource() {
		return $this->amz_resource;
	}
	
	/**
	 * Specify the HTTP method to use (GET, PUT, DELETE)
	 *
	 * @param String $method
	 */
	public function setMethod($method = self::GET) {
		$this->method = $method;
	}
	
	/**
	 * Returns the HTTP method
	 *
	 * @return String
	 */
	public function getMethod() {
		return $this->method;
	}
	
	/**
	 * Returns the Zend_Http_Client instance used for the connection
	 *
	 * @return Zend_Http_Client
	 */
	public function getClient() {
		return $this->client;
	}
	
	/**
	 * Setter for the Http_Client instance
	 *
	 * @param Zend_Http_Client $client
	 */
	public function setHttpClient(Zend_Http_Client $client) {
		$this->client = $client;
	}
	
	/**
	 * Returns the Amazon-supplied access key
	 *
	 * @return String
	 */
	public function getAccessKey() {
		return $this->access_key;
	}
	
	/**
	 * Return the 'secret' used for generating the signature-hash
	 *
	 * @return String
	 */
	public function getSecret() {
		return $this->secret;
	}
}

?>
