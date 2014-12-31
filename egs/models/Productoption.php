<?php
class Productoption extends DataObject {

	function __construct() {
		parent::__construct('product_options');
		$this->idField='id';
		
		$this->view='';
		
 		$this->belongsTo('Option', 'category_id', 'category'); 

	}


}
?>
