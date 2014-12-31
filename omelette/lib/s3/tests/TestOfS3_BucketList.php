<?php

/**
 *
 */
class TestOfS3_BucketList extends S3_UnitTestCase {

	public function testInstantiation() {
		$test_response = '<?xml version="1.0" encoding="UTF-8"?><ListAllMyBucketsResult xmlns="http://s3.amazonaws.com/doc/2006-03-01/"><Owner><ID>5146d8353da7f4d49f864b248fafd8070e1df88b0bd019a8237f8b1569ea86a2</ID><DisplayName>jstrideuk</DisplayName></Owner><Buckets><Bucket><Name>tactile_test</Name><CreationDate>2007-11-16T09:31:00.000Z</CreationDate></Bucket></Buckets></ListAllMyBucketsResult>';
		$list = new S3_BucketList($test_response);
		$this->assertIsA($list, 'S3_BucketList');
	}
	
	public function testFailedInstantiation() {
		$test_invalid_response = '';
		$this->expectException();		
		$list = new S3_BucketList($test_invalid_response);
	}
	
	public function testGettingSingleBucket() {
		$test_response = '<?xml version="1.0" encoding="UTF-8"?><ListAllMyBucketsResult xmlns="http://s3.amazonaws.com/doc/2006-03-01/"><Owner><ID>5146d8353da7f4d49f864b248fafd8070e1df88b0bd019a8237f8b1569ea86a2</ID><DisplayName>jstrideuk</DisplayName></Owner><Buckets><Bucket><Name>tactile_test</Name><CreationDate>2007-11-16T09:31:00.000Z</CreationDate></Bucket></Buckets></ListAllMyBucketsResult>';
		$list = new S3_BucketList($test_response);
		$this->assertEqual($list->getOwnerName(),'jstrideuk');
		$this->assertEqual($list->getOwnerID(), '5146d8353da7f4d49f864b248fafd8070e1df88b0bd019a8237f8b1569ea86a2');
		$this->assertEqual(count($list->getBuckets()),1);
	}
	
	public function testWithMultipleBuckets() {
		//response-example from developer-guide p62
		$test_response = '<?xml version="1.0" encoding="UTF-8"?><ListAllMyBucketsResult xmlns="http://s3.amazonaws.com/doc/2006-03-01"><Owner><ID>bcaf1ffd86f41caff1a493dc2ad8c2c281e37522a640e161ca5fb16fd081034f</ID><DisplayName>webfile</DisplayName></Owner><Buckets><Bucket><Name>quotes</Name><CreationDate>2006-02-03T16:45:09.000Z</CreationDate></Bucket><Bucket><Name>samples</Name><CreationDate>2006-02-03T16:41:58.000Z</CreationDate></Bucket></Buckets></ListAllMyBucketsResult>';
		$list = new S3_BucketList($test_response);
		$this->assertEqual($list->getOwnerName(),'webfile');
		$this->assertEqual($list->getOwnerID(), 'bcaf1ffd86f41caff1a493dc2ad8c2c281e37522a640e161ca5fb16fd081034f');
		$this->assertEqual(count($list->getBuckets()),2);		
	}
	
	public function testListArrayAccess() {
		//same response as test above
		$test_response = '<?xml version="1.0" encoding="UTF-8"?><ListAllMyBucketsResult xmlns="http://s3.amazonaws.com/doc/2006-03-01"><Owner><ID>bcaf1ffd86f41caff1a493dc2ad8c2c281e37522a640e161ca5fb16fd081034f</ID><DisplayName>webfile</DisplayName></Owner><Buckets><Bucket><Name>quotes</Name><CreationDate>2006-02-03T16:45:09.000Z</CreationDate></Bucket><Bucket><Name>samples</Name><CreationDate>2006-02-03T16:41:58.000Z</CreationDate></Bucket></Buckets></ListAllMyBucketsResult>';
		$list = new S3_BucketList($test_response);
		
		$buckets = $list->getBuckets();
		
		$first = $buckets[0];
		$this->assertEqual($first->Name, 'quotes');
		$this->assertEqual($first->CreationDate, '2006-02-03T16:45:09.000Z');
		
		$second = $buckets[1];
		$this->assertEqual($second->Name, 'samples');
		$this->assertEqual($second->CreationDate, '2006-02-03T16:41:58.000Z');
	}
	
	public function testListArrayAccessWithOne() {
		$test_response = '<?xml version="1.0" encoding="UTF-8"?><ListAllMyBucketsResult xmlns="http://s3.amazonaws.com/doc/2006-03-01/"><Owner><ID>5146d8353da7f4d49f864b248fafd8070e1df88b0bd019a8237f8b1569ea86a2</ID><DisplayName>jstrideuk</DisplayName></Owner><Buckets><Bucket><Name>tactile_test</Name><CreationDate>2007-11-16T09:31:00.000Z</CreationDate></Bucket></Buckets></ListAllMyBucketsResult>';
		$list = new S3_BucketList($test_response);
		
		$buckets = $list->getBuckets();
		
		$first = $buckets[0];
		$this->assertEqual($first->Name, 'tactile_test');
		$this->assertEqual($first->CreationDate, '2007-11-16T09:31:00.000Z');
		
	}
	
	public function testListIteration() {
		//same response as test above
		$test_response = '<?xml version="1.0" encoding="UTF-8"?><ListAllMyBucketsResult xmlns="http://s3.amazonaws.com/doc/2006-03-01"><Owner><ID>bcaf1ffd86f41caff1a493dc2ad8c2c281e37522a640e161ca5fb16fd081034f</ID><DisplayName>webfile</DisplayName></Owner><Buckets><Bucket><Name>quotes</Name><CreationDate>2006-02-03T16:45:09.000Z</CreationDate></Bucket><Bucket><Name>samples</Name><CreationDate>2006-02-03T16:41:58.000Z</CreationDate></Bucket></Buckets></ListAllMyBucketsResult>';
		$list = new S3_BucketList($test_response);
		
		$buckets = $list->getBuckets();
		$i = 0;
		$names = array('quotes','samples');
		foreach($buckets as $bucket) {
			$this->assertEqual($bucket->Name, $names[$i]);
			$i++;
		}
		$this->assertEqual($i, 2);
	}
	
}

?>
