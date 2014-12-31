<?php

require_once ('simpletest/unit_tester.php');
require_once ('simpletest/test_case.php');
require_once('simpletest/reporter.php');
/**
 * Test-data comes from the Developer Guide PDF, with the key changed so as to actually match the result-data
 */
class TestOfS3_Connection_Authorization extends S3_UnitTestCase {

	protected static $TEST_S3_ACCESS_KEY = '0PN6J17HBGXHT7JJ3X82';
	protected static $TEST_S3_SECRET = 'uV3F3YluFJax1cknvbcGwgjvx4QpvB+leU8dUj2o';
	
	/**
	 * A connection to test with
	 *
	 * @var S3_Connection
	 */
	protected $connection;
	
	function setup() {
		parent::setup();
		$this->connection = new S3_Connection(self::$TEST_S3_ACCESS_KEY, self::$TEST_S3_SECRET);
	}
	
	function testInstantiation() {
		$auth = new S3_Connection_Authorization($this->connection);
		$this->assertTrue($auth instanceof S3_Connection_Authorization);
	}
	
	function testStringToSignGenerationForObjectGet() {
		$this->connection->getClient()->setHeaders('Date', 'Tue, 27 Mar 2007 19:36:42 +0000');
		$this->connection->setMethod('GET');
		$this->connection->setAmzResource('/johnsmith/photos/puppy.jpg');
		$auth = new S3_Connection_Authorization($this->connection);
		$this->assertEqual(
			$auth->generateHeaderStringToSign(), 
			"GET\n\n\nTue, 27 Mar 2007 19:36:42 +0000\n/johnsmith/photos/puppy.jpg");
	}
	
	function testSignatureBuildingForObjectGet() {
		$this->connection->getClient()->setHeaders('Date', 'Tue, 27 Mar 2007 19:36:42 +0000');
		$this->connection->setMethod('GET');
		$this->connection->setAmzResource('/johnsmith/photos/puppy.jpg');
		$auth = new S3_Connection_Authorization($this->connection);
		$this->assertEqual(
			$auth->generateHeaderAuthorization(), 
			'AWS 0PN6J17HBGXHT7JJ3X82:xXjDGYUmKxnwqr5KXNPGldn5LbA='
		);
	}
	
	function testSignatureBuildingForObjectPut() {
		$this->connection->getClient()->setHeaders('Date', 'Tue, 27 Mar 2007 21:15:45 +0000');
		$this->connection->getClient()->setHeaders('Content-Length','94328');
		$this->connection->getClient()->setHeaders('Content-Type','image/jpeg');
		$this->connection->setMethod('PUT');
		$this->connection->setAmzResource('/johnsmith/photos/puppy.jpg');
		$auth = new S3_Connection_Authorization($this->connection);
		$this->assertEqual(
			$auth->generateHeaderAuthorization(), 
			'AWS 0PN6J17HBGXHT7JJ3X82:hcicpDDvL9SsO6AkvxqmIWkmOuQ='
		);
	}
	
	function testQueryStringAuthorization() {
		$this->real_connection->setMethod('GET');
		$this->real_connection->setAmzResource('/tactile_test/testfile');
		$auth = new S3_Connection_Authorization($this->real_connection);
		$this->assertEqual(
			$auth->generateQueryStringAuthorization(1195218197),
			'AWSAccessKeyId=12CWNKWQR36VMW116MG2&Signature=ua00Mt17V%2FRY%2FYkbmx8vVV6b%2F%2FU%3D&Expires=1195218197'
		);
	}
}

?>
