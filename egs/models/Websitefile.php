<?php
class Websitefile extends DataObject {
	protected $defaultDisplayFields=array('file');
	
	function __construct() {
		parent::__construct('website_files');
		$this->idField='id';
		$this->identifierField='file';

 		$this->belongsTo('File', 'file_id', 'file');
 		$this->belongsTo('Website', 'website_id', 'website');
	}
}
?>
