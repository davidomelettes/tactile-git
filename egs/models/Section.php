<?php
class Section extends DataObject {
	protected $defaultDisplayFields=array('title','shortdescription','visible','created');
	function __construct() {
		parent::__construct('store_sections');
		$this->idField='id';
		
		$this->identifierField='title';
 		$this->actsAsTree('parent_id'); 
		$this->hasOne('File','image','imagefile');
		$this->hasMany('Product','products');
	}


	public function getProductCount() {
		return count($this->products);		
	}

}
?>
