<?php

/**
 * Represents a list of objects stored in S3, as returned from calling GET on a bucket
 * 
 * @author gj
 * @package S3
 */
class S3_ObjectList {

	/**
	 * The SimpleXMLElement representing the list
	 *
	 * @var SimpleXMLElement
	 */
	protected $xml;
	
	/**
	 * Constructor
	 * 
	 * @param String $xml
	 */
	function __construct($xml) {
		$this->xml = new SimpleXMLElement($xml);
	}
}

?>
