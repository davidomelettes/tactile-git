<?php
class Offercode extends DataObject {
	protected $defaultDisplayFields=array('code','valid_from','valid_to','discount_type','discount_amount');
	function __construct() {
		parent::__construct('store_offer_codes');
		$this->idField='id';
				
 		$this->validateUniquenessOf('code');
 		$this->belongsTo('Customer', 'customer_id', 'customer');
 		$this->belongsTo('Customertype', 'customer_type_id', 'customer_type');
 		$this->belongsTo('Product', 'product_id', 'product');
 		$this->belongsTo('Section', 'section_id', 'section');
 		$this->belongsTo('Campaign', 'campaign_id', 'campaign'); 
		$this->setEnum('discount_type',array('fixed'=>'Fixed','percent'=>'Percent'));
	}
}
?>
