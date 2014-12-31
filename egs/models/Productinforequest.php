<?php
class Productinforequest extends DataObject {

	function __construct() {
		parent::__construct('store_product_information_requests');
		$this->idField='id';
		
		
 		$this->belongsTo('Product', 'product_id', 'product'); 

	}


}
?>
