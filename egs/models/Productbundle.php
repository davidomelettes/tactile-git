<?php
class Productbundle extends DataObject {

	function __construct() {
		parent::__construct('product_bundles');
		$this->idField='id';
		
		$this->hasManyThrough('Product','products_in_bundles','products');
		$this->setEnum('discount_type',array('fixed'=>'Fixed','percent'=>'Percent'));
	}


}
?>
