<?php

/**
 * Deleting a transaction is actually a call to 'release', but with amount=-1
 * 
 * 
 * @author gj
 * @package Payment
 */
class SecPayCancelDeferred extends SecPayRelease {

	
	/**
	 * A delete will always want certain options
	 *
	 * @param String $mid
	 * @param String $vpn_password
	 * @see SecPayRequest_Abstract::__construct
	 */	
	public function __construct($mid=null, $vpn_password=null) {
		parent::__construct($mid, $vpn_password);
		$this->setAmount(-1);
	}
	
}
?>