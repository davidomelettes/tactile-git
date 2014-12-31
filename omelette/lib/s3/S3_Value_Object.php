<?php

/**
 * Responsible for representing an object stored in, or to be stored in, S3
 * 
 * @author gj
 * @package S3
 */
class S3_Value_Object {

	public $name, $bucket, $content_type;	
	
	public $amz_headers = array();
	
	protected $filepath;
	
	/**
	 * Factory method for more easily creating the value-object
	 *
	 * @param String $name The name given to the file
	 * @param String $bucket The name of the bucket the file will be put in
	 * @param String $path The path of the file on local disk
	 * @param String optional $content_type The content-type of the file
	 * @return S3_Value_Object
	 */
	public static function create($name, $bucket, $path, $content_type='') {
		$object = new S3_Value_Object();
		$object->name = $name;
		$object->bucket = $bucket;
		$object->setFilepath($path);
		$object->content_type = $content_type;
		return $object;
	}
	
	/**
	 * Setter for filepath, checks that path exists and is writable
	 *
	 * @param String $path
	 */
	public function setFilepath($path) {
		if(!file_exists($path)) {
			throw new Exception('File not found: '.$path);
		}
		if(!is_readable($path)) {
			throw new Exception('File not readable: '.$path);
		}
		$this->filepath = $path;
	}
	
	/**
	 * To allow getting of the filepath
	 *
	 * @param String $key
	 * @return Mixed
	 */
	public function __get($key) {
		if(isset($this->$key)) {
			return $this->$key;
		}
	}
	
}

?>
