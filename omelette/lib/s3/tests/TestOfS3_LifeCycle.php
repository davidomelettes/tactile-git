<?php

/**
 *
 */
class TestOfS3_LifeCycle extends S3_UnitTestCase {

	/**
	 * The S3_Service instance
	 *
	 * @var S3_Service
	 */
	protected $s3;
	
	public function __construct($label = false) {
		parent::__construct($label);
		$this->s3 = new S3_Service(S3_ACCESS_KEY, S3_SECRET);
	}
	
	public function setup() {
		@$this->s3->bucket->delete('tactile_full_test');
	}
	
	public function testFullCycle() {
		$success = $this->s3->bucket->put('tactile_full_test');

		$this->assertTrue(!!$success);
		
		$content = 'This is the content for a test file';
		$path = '/tmp/s3testfile';
		$fp = fopen($path,'w+');
		fwrite($fp, $content);
		fclose($fp);
		
		$object = new S3_Value_Object();
		$object->name = 'fulltestfile';
		$object->bucket = 'tactile_full_test';
		$object->setFilepath($path);
		$object->content_type = 'text/plain';
		
		$success = $this->s3->object->put($object);
		$this->assertTrue(!!$success);
		
		$success = $this->s3->bucket->delete('tactile_full_test');
		$this->assertFalse($success);
		$this->assertEqual($this->s3->bucket->getError()->Code, 'BucketNotEmpty');
		
		$success = $this->s3->object->delete('fulltestfile', 'tactile_full_test');
		$this->assertTrue(!!$success);
		
		$success = $this->s3->bucket->delete('tactile_full_test');
		$this->assertTrue(!!$success);
		$this->assertFalse($this->s3->bucket->getError());
	}
	
}

?>
