<?php

class Storeconfig extends DataObject {
	
	function __construct() {
		parent::__construct('store_config');
		$this->belongsTo('Customertype','default_customer_type_id','default_customer_type');
		$this->setEnum('site_status',array('online'=>'Online','no_ordering'=>'Online - No Ordering','offline'=>'Offline'));
		$this->setEnum('default_products_per_page',getRange(0,100,10,true));
		
	}

	static function Instance() {
		static $config;
		if($config==null) {
			$config=new Storeconfig();
			$config->loadBy('usercompanyid',EGS_COMPANY_ID);		
		}
		return $config;
	}

}
?>
