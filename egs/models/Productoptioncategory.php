<?php
class Productoptioncategory extends DataObject {

	function __construct() {
		parent::__construct('product_option_categories');
		$this->idField='id';
		
		
 		$this->belongsTo('Product', 'product_id', 'product'); 
		$this->hasMany('Productoption','options','category_id');
	}


}
?>
