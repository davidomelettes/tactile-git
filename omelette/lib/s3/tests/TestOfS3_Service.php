<?php

/**
 *
 */
class TestOfS3_Service extends S3_UnitTestCase {
	
	public function testInstantiation() {
		$s3 = new S3_Service(self::$TEST_S3_ACCESS_KEY, self::$TEST_S3_SECRET);
		$this->assertIsA($s3, 'S3_Service');
	}
	
	public function testInterceptedProperties() {
		$s3 = new S3_Service(self::$TEST_S3_ACCESS_KEY, self::$TEST_S3_SECRET);
		$bucket = $s3->bucket;
		$object = $s3->object;
		
		$this->assertIsA($bucket, 'S3_Bucket');
		$this->assertIsA($object, 'S3_Object');	
	}
	
	public function testNotDistinctInstances() {
		$s3 = new S3_Service(self::$TEST_S3_ACCESS_KEY, self::$TEST_S3_SECRET);
		$b1 = $s3->bucket;
		$b2 = $s3->bucket;

		$this->assertIdentical($b1, $b2);	
	}
	
}

?>
