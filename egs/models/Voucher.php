<?php
class Voucher extends DataObject {

	protected $defaultDisplayFields = array('code'=>'Code','buyer'=>'Buyer','redeemer'=>'Redeemer','redeemed'=>'Redeemed','expiry'=>'Expiry');

	function __construct() {
		parent::__construct('store_vouchers');
		$this->idField='id';
		
		
 		$this->belongsTo('Customer', 'buyer_id', 'buyer');
 		$this->belongsTo('Customer', 'redeemed_by', 'redeemer'); 

	}


}
?>
