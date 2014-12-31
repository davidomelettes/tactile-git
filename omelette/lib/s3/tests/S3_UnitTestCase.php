<?php
require_once 'simpletest/unit_tester.php';
require_once 'Zend/Http/Client.php';
require_once 'Zend/Http/Client/Adapter/Test.php';

/**
 *
 */
abstract class S3_UnitTestCase extends UnitTestCase {

	/**
	 * The access key used for the examples in the developer-guide, used for testing the hash-building
	 *
	 * @static String
	 */
	protected static $TEST_S3_ACCESS_KEY = '0PN6J17HBGXHT7JJ3X82';
	
	/**
	 * The 'secret' used for the examples in the developer-guide
	 *
	 * @var String
	 */
	protected static $TEST_S3_SECRET = 'uV3F3YluFJax1cknvbcGwgjvx4QpvB+leU8dUj2o';
	
	/**
	 * The S3_Connection instance, with a Test adapter that doesn't actually send requests
	 *
	 * @var S3_Connection
	 */
	protected $fake_connection;
	
	/**
	 * A Real S3_Connection instance, that does actual requests
	 *
	 * @var S3_Connection
	 */
	protected $real_connection;
	
	public function setup() {
		$this->client = new Zend_Http_Client();
		$this->fake_connection = new S3_Connection(self::$TEST_S3_ACCESS_KEY, self::$TEST_S3_SECRET);
		$this->fake_connection->setHttpClient($this->client);
		$this->adapter = new Zend_Http_Client_Adapter_Test();
		$this->client->setAdapter($this->adapter);
		
		$this->real_connection = new S3_Connection(S3_ACCESS_KEY, S3_SECRET);
	}
	
}

?>
