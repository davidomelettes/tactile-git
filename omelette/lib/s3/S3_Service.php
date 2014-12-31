<?php

/**
 * Base class for use with Amazon's S3 service, contains some constants
 * 
 * @author gj
 * @package S3
 */
class S3_Service {

	/**
	 * The URL that requests for S3 operations are sent to
	 *
	 */
	const URL = 'https://s3.amazonaws.com/';
	
	const UNSECURE_URL = 'http://s3.amazonaws.com/';
	
	/**
	 * The instance of S3_Connection used for creating objects and buckets
	 *
	 * @var S3_Connection
	 */
	protected $connection;
	
	/**
	 * An S3_Object instance
	 *
	 * @var S3_Object
	 */
	protected $object;
	
	/**
	 * An S3_Bucket instance
	 *
	 * @var S3_Bucket
	 */
	protected $bucket;
	
	/**
	 * Constructor
	 * Takes the access-key and the secret and instantiates an S3_Connection instance for use when creating objects
	 *
	 * @param String $access_key
	 * @param String $secret
	 */
	public function __construct($access_key, $secret) {
		$this->connection = new S3_Connection($access_key, $secret);
	}
	
	/**
	 * Allow for manual setting of the connection
	 *
	 * @param S3_Connection $connection
	 */
	public function setConnection(S3_Connection $connection) {
		$this->connection = $connection;
	}
	
	/**
	 * Return the connection instance
	 *
	 * @return S3_Connection
	 */
	public function getConnection() {
		return $this->connection;
	}
	
	/**
	 * Intercepts calls to 'object' and bucket' to return an appropriate instance
	 *
	 * @param String $key The key to return
	 * @return mixed
	 */
	public function __get($key) {
		switch($key) {
			case 'object': {
				if(!isset($this->object)) {
					$this->object = new S3_Object($this->connection);
				} 
				return $this->object;
			}
			case 'bucket': {
				if(!isset($this->bucket)) {
					$this->bucket = new S3_Bucket($this->connection);
				}
				return $this->bucket;
			}
		}
	}
	
}

?>
