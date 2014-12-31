<?php

/**
 * Represents a list of S3 Buckets, as returned from a 'getList()' call to S3_Bucket
 * 
 * @author gj
 * @package S3
 */
class S3_BucketList {
	
	/**
	 * The SimpleXMLElement representing the list
	 *
	 * @var SimpleXMLElement
	 */
	protected $xml;

	/**
	 * Constructor. Takes the XML response from the call
	 * 
	 * @param String $xml
	 */
	function __construct($xml) {
		$this->xml = new SimpleXMLElement($xml);
	}
	
	/**
	 * Return the DisplayName property of the account owner
	 *
	 * @return String
	 */
	public function getOwnerName() {
		return $this->xml->Owner->DisplayName;
	}
	
	/**
	 * Return the ID property of the account owner
	 *
	 * @return String
	 */
	public function getOwnerID() {
		return $this->xml->Owner->ID;
	}
	
	/**
	 * Return the 'Bucket' XML nodes
	 *
	 * @return SimpleXMLElement
	 */
	public function getBuckets() {
		return $this->xml->Buckets->Bucket;
	}
}

?>
