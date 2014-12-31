<?php

require_once ('simpletest/unit_tester.php');

/**
 *
 */
class TestOfS3_Object extends S3_UnitTestCase  {

	public function __construct($label = false) {
		parent::__construct($label);
		
		$this->s3 = new S3_Service(S3_ACCESS_KEY, S3_SECRET);
		$this->s3->bucket->put('tactile_object_test');
	}
	
	public function __destruct() {
		$this->s3->bucket->delete('tactile_object_test');
		@unlink('/tmp/s3testfile');
	}
	
	public function testObjectPut() {
		$path = $this->writeTestFile();
		
		$object = new S3_Value_Object();
		$object->name = 'test';
		$object->bucket = 'tactile_object_test';
		$object->setFilepath($path);
		$object->content_type = 'text/plain';
		
		$success = $this->s3->object->put($object);
		$this->assertTrue($success);
	}
	
	public function testObjectPutWithSpaces() {
		$path = $this->writeTestFile();
		
		$object = new S3_Value_Object();
		$object->name = 'test with spaces';
		$object->bucket = 'tactile_object_test';
		$object->setFilepath($path);
		$object->content_type = 'text/plain';
		
		$success = $this->s3->object->put($object);
		$this->assertTrue($success);
	}
	
	public function testObjectGetWithSpaces() {
		$path = $this->writeTestFile();
		
		$object = S3_Value_Object::create('test with spaces', 'tactile_object_test', $path);
		
		$this->s3->object->put($object);
		
		$file = $this->s3->object->get('test', 'tactile_object_test');
		$this->assertEqual($file, file_get_contents($path));
	}
	
	
	public function testObjectDelete() {
		$success = $this->s3->object->delete('test', 'tactile_object_test');
		$this->assertTrue($success);
	}
	
	public function testObjectDeleteWithSpaces() {
		$success = $this->s3->object->delete('test with spaces', 'tactile_object_test');
		$this->assertTrue($success);
	}
	
	public function testObjectGet() {
		$path = $this->writeTestFile();
		
		$object = S3_Value_Object::create('test', 'tactile_object_test', $path);
		
		$this->s3->object->put($object);
		
		$file = $this->s3->object->get('test', 'tactile_object_test');
		$this->assertEqual($file, file_get_contents($path));
	}
	
	public function testObjectGetRequestURL() {
		$path = $this->writeTestFile();		
		$object = S3_Value_Object::create('test', 'tactile_object_test', $path);		
		$this->s3->object->put($object);
		
		$url = $this->s3->object->getRequestURL('test', 'tactile_object_test');
		$client = new Zend_Http_Client($url);
		$file = $client->request()->getBody();
		$this->assertEqual($file, file_get_contents($path));
	}
	
	public function testObjectGetWithInvalidExpiry() {
		$path = $this->writeTestFile();		
		$object = S3_Value_Object::create('test', 'tactile_object_test', $path);		
		$this->s3->object->put($object);
		
		$url = $this->s3->object->getRequestURL('test', 'tactile_object_test',strtotime('-5 seconds'));
		$client = new Zend_Http_Client($url);
		$file = $client->request()->getBody();
		$this->assertNotEqual($file, file_get_contents($path));
	}
	
	
	private function writeTestFile($name='s3testfile') {
		$content = 'This is the content for a test file';
		$path = '/tmp/'.$name;
		$fp = fopen($path,'w+');
		fwrite($fp, $content);
		fclose($fp);
		return $path;
	}
	
}

?>
