<?php
class BasketItem extends DataObject {
	
	function __construct() {
		parent::__construct('store_basket_items');
		$this->hasOne('Product');
		$this->belongsTo('Basket');
	}

}
?>