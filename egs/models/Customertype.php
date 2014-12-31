<?php
class Customertype extends DataObject {
	protected $is_default=false;
	function __construct() {
		parent::__construct('customer_types');
		$this->idField='id';
		$this->hasAndBelongsToMany('Customer','customers_in_types');
	}
	
	function load($id) {
		parent::load($id);
		$config=Storeconfig::Instance();
		if($config->default_customer_type_id==$this->id) {
			$this->is_default=true;
		}
	}

	public function is_default() {
		return $this->is_default;
	}
}
?>
