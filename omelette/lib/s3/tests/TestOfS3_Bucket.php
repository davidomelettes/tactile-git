<?php


/**
 *
 */
class TestOfS3_Bucket extends S3_UnitTestCase  {
	
	public function setup() {
		parent::setup();
		$s3 = new S3_Service(S3_ACCESS_KEY, S3_SECRET);
		$s3->bucket->delete('tactile_test_create');
		$s3->bucket->put('tactile_test_delete');
	}
	
	public function __destruct() {
		$s3 = new S3_Service(S3_ACCESS_KEY, S3_SECRET);
		$s3->bucket->delete('tactile_test_create');
		$s3->bucket->delete('tactile_test_delete');
	}
	
	public function testInstantiation() {
		$bucket = new S3_Bucket($this->fake_connection);
		$this->assertIsA($bucket,'S3_Bucket');
	}
	
	public function testBucketCreateSuccessReport() {
		$this->adapter->setResponse('HTTP/1.1 200 OK
X-amz-id-2: 1Q63wZjeUrjtEhTNNnrelnJ5obGuPQmHNEzq/2wyPGoZvdb1HnBsE9XeTUxnHXpF
X-amz-request-id: 22D771E39B905444
Date: Thu, 15 Nov 2007 14:39:36 GMT
Location: /tactile_test
Content-length: 0
Server: AmazonS3');
		$bucket = new S3_Bucket($this->fake_connection);
		$success = $bucket->put('tactile_test');
		$this->assertTrue($success);
	}
	
	public function testBucketCreate() {
		$bucket = new S3_Bucket($this->real_connection);
		$success = $bucket->put('tactile_test');
		$this->assertTrue($success);
	}
	
	public function testBucketCreateWhenNameExists() {
		$bucket = new S3_Bucket($this->real_connection);
		$success = $bucket->put('johnsmith');
		$this->assertFalse($success);
		$this->assertEqual('BucketAlreadyExists', $bucket->getError()->Code);
	}
	
	public function testBucketDelete() {
		$bucket = new S3_Bucket($this->real_connection);
		$success = $bucket->delete('tactile_test_delete');
		$this->assertTrue($success, "Failed to delete a bucket (tactile_test_delete): {$bucket->getError()->Code}");
	}
	
	public function testBucketDeleteNotOurs() {
		$bucket = new S3_Bucket($this->real_connection);
		$success = $bucket->delete('johnsmith',"Shouldn't return true for a Bucket created by somebody else");
		$this->assertFalse($success);
		$this->assertEqual($bucket->getError()->Code,'AccessDenied',"Didn't receive AccessDenied as Error-code, code was: {$bucket->getError()->Code}");
	}
	
	public function testSimpleBucketGet() {
		$bucket = new S3_Bucket($this->real_connection);
		$list = $bucket->get('tactile_test');
		$this->assertIsA($list, 'S3_ObjectList');
	}
	
	public function testGettingListOfBuckets() {
		$s3 = new S3_Service(S3_ACCESS_KEY, S3_SECRET);
		$bucket_list = $s3->bucket->getList();
		$this->assertIsA($bucket_list, 'S3_BucketList');
	}
	
	
}

?>
