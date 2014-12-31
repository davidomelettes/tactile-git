<?php
class Productfeature extends DataObject {

	protected $defaultDisplayFields = array('description'=>'Description','sequence'=>'Sequence');

	function __construct() {
		parent::__construct('product_features');
		$this->idField='id';
		
		
 		$this->belongsTo('Product', 'product_id', 'product');
 		$this->belongsTo('Product', 'product_feed_id', 'product_feed'); 

	}


}
?>
