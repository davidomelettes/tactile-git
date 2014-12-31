<?php
class Productattribute extends DataObject {

	protected $defaultDisplayFields = array('name'=>'Name','value'=>'Value','units'=>'Units');

	function __construct() {
		parent::__construct('product_attributes');
		$this->idField='id';
		
		
 		$this->belongsTo('Product', 'product_id', 'product');
 		$this->belongsTo('Product', 'product_feed_id', 'product_feed'); 

	}


}
?>
