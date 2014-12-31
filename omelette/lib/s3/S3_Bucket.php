<?php

/**
 * Responsible for the creation and modification of 'Buckets' within Amazon's S3 Service
 * 
 * @author gj
 * @package S3
 */
class S3_Bucket {
	
	/**
	 * An S3_Connection instance
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
	 * Constructor, takes an S3_Connection instance
	 * 
	 * @param S3_Connection $connection
	 */
	public function __construct(S3_Connection $connection) {
		$this->connection = $connection;
	}
	
	/**
	 * Attempts to create a bucket with the supplied name
	 *
	 * @param String $name
	 * @return Boolean
	 */
	public function put($name, $acl = 'private') {
		$this->connection->setAmzHeader('acl',$acl);
		$this->connection->setAmzResource("/".$name);
		$this->connection->setMethod(S3_Connection::TYPE_PUT);
		return $this->doRequest(S3_Service::URL.$name) !== false;
	}
	
	/**
	 * Attempts to delete the bucket with the supplied name
	 * - This will return true even when the bucket didn't previously exist.
	 * 
	 * @param String $name
	 * @return Boolean 
	 */
	public function delete($name) {
		$this->connection->setAmzResource("/".$name);
		$this->connection->setMethod(S3_Connection::TYPE_DELETE);	
		return $this->doRequest(S3_Service::URL.$name) !== false;
	}
	
	/**
	 * Calling GET on a bucket returns a list of the Objects within it, or false if the request fails
	 *
	 * @param String $name The name of the bucket to list
	 * @return S3_ObjectList
	 */
	public function get($name) {
		$this->connection->setAmzResource("/".$name);
		$this->connection->setMethod(S3_Connection::TYPE_GET);	
		$response = $this->doRequest(S3_Service::URL.$name);
		if($response === false) {
			return false;
		}
		/* @var $response Zend_Http_Response */
		$response;
		$objects = new S3_ObjectList($response->getBody());
		return $objects;
	}
	
	/**
	 * Returns a list of all buckets (owned by the connection's key)
	 * by performing a GET on the service-endpoint
	 *
	 * @return S3_BucketList
	 */
	public function getList() {
		$this->connection->setAmzResource('/');
		$this->connection->setMethod(S3_Connection::TYPE_GET);
		$response = $this->doRequest(S3_Service::URL);
		if($response===false) {
			return false;
		}
		/* @var $response Zend_Http_Response */
		$response;
		$buckets = new S3_BucketList($response->getBody());
		return $buckets;
	}
	
	/**
	 * Performs a request relating to a bucket
	 *
	 * @param String $url
	 * @return Zend_Http_Response|Boolean
	 */
	protected function doRequest($url) {
		unset($this->error);
		$response = $this->connection->send($url);
		$success = $response->isSuccessful();
		if(!$success) {
			$msg = $response->getBody();
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
