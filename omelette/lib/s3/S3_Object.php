<?php

/**
 * Responsible for operations on Objects using Amazon's S3 Service
 * 
 * @author gj
 * @package S3
 */
class S3_Object {

	/**
	 * The S3_Connection instance used for requests
	 *
	 * @var S3_Connection
	 */
	protected $connection;
	
	/**
	 * A SimpleXMLELement containing the response-body, in case of error
	 *
	 * @var SimpleXMLElement
	 */
	protected $error;
	
	/**
	 * Constructor
	 * 
	 * @param S3_Connection $connection The S3_Connection to use
	 */
	function __construct(S3_Connection $connection) {
		$this->connection = $connection;
	}
	
	/**
	 * Send a file to S3
	 * Returns true on success, false otherwise. If false, call getError() to get details
	 * 
	 * @param S3_Value_Object $object
	 * @return Boolean
	 */
	public function put(S3_Value_Object $object, $public=false) {
		foreach($object->amz_headers as $key=>$value) {
			$this->connection->setAmzHeader($key,$value);
		}
		$resource = urlencode($object->bucket.'/'.$object->name);
		$this->connection->setAmzResource("/".$resource);
		if ($public) {
			$this->connection->setAmzHeader('acl', 'public-read');
			$this->connection->getClient()->setHeaders('Cache-Control', 'public,max-age=864000');
		}
		$this->connection->getClient()->setHeaders('Content-Type',$object->content_type);
		$this->connection->setMethod(S3_Connection::TYPE_PUT);
		return $this->doRequest(S3_Service::URL.$resource, file_get_contents($object->filepath)) !== false;
	}
	
	/**
	 * Get a file from S3
	 * Returns the contents of the file, or false if the request fails. Calling getError() will give details.
	 * 
	 * @param String $name The name of the file to get
	 * @param String $bucket The name of the bucket to look in
	 * @return String|Boolean
	 */
	public function get($name, $bucket) {
		$resource = urlencode($bucket.'/'.$name);
		$this->connection->setAmzResource("/".$resource);
		$this->connection->setMethod(S3_Connection::TYPE_GET);	
		$response = $this->doRequest(S3_Service::URL.$resource);
		if($response === false) {
			return false;
		}
		/* @var $response Zend_Http_Response */
		$response;
		return $response->getBody();
	}
	
	/**
	 * Returns a time-limited URL for accessing the file
	 *
	 * @param String $name The name of the file to get
	 * @param String $bucket The name of the bucket to look in
	 * @param Int $expires A timestamp after which requests will be invalid
	 * @return String
	 */
	public function getRequestURL($name, $bucket, $expires=null, $public=false) {
		$resource = urlencode($bucket.'/'.$name);
		$this->connection->setAmzResource("/".$resource);
		$this->connection->setMethod(S3_Connection::TYPE_GET);
		if ($public) {
			$url = S3_Service::URL.$resource;
		} else {
			$url = $this->connection->buildQueryStringRequest(S3_Service::URL.$resource, $expires);
		}
		return $url;
	}
	
	/**
	 * Delete a file from a bucket
	 * Returns true on success, false otherwise. If false, call getError() to get details
	 * 
	 * @param String $name The name of the file to delete
	 * @param String $bucket The name of the bucket to look in
	 * @return unknown
	 */
	public function delete($name, $bucket) {
		$resource = urlencode($bucket.'/'.$name);
		$this->connection->setAmzResource("/".$resource);
		$this->connection->setMethod(S3_Connection::TYPE_DELETE);	
		return $this->doRequest(S3_Service::URL.$resource) !== false;
	}
	
	
	/**
	 * Performs a request relating to a object
	 *
	 * @param String $url
	 * @return Zend_Http_Response|Boolean
	 */
	protected function doRequest($url, $data='') {
		$response = $this->connection->send($url, $data);
		$success = $response->isSuccessful();
		if(!$success) {
			$msg = $response->getBody();
			//echo $msg;
			$this->error = new SimpleXMLElement($msg);
			return false;
		}
		return $response;
	}
	
	/**
	 * Returns the Response Body as XML
	 *
	 * @return SimpleXMLElement
	 */
	public function getError() {
		if(!isset($this->error)) {
			return false;
		}
		return $this->error;
	}
}

?>
