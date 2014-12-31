<?php
class Bundledproduct extends DataObject {

	function __construct() {
		parent::__construct('products_in_bundles');
		$this->idField='id';
		
		
 		$this->belongsTo('Product', 'product_id', 'product');
 		$this->belongsTo('Productbundle', 'bundle_id', 'bundle'); 
		$this->setEnum('discount_type',array('fixed'=>'Fixed','percent'=>'Percent'));
	}


}
?>
