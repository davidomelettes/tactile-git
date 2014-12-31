<?php
/**
 * For making a request to SecPay to release a previously deferred transaction
 * https://www.secpay.com/xmlrpc/releaseTransaction.html
 * 
 * @author gj
 * @package Payment
 */
class SecPayRelease extends SecPayRequest_Abstract {
	//0-2 are in Base
	
	/**
	 * The amount to release (no symbols or ,s)
	 */
	protected $AMOUNT = 3;
	
	/**
	 * The SecPay 'Remote Password'
	 *
	 */
	protected $REMOTE_PSWD = 4;
	
	/**
	 * The Transaction id of the new transaction created by performing the release
	 */
	protected $NEW_TRANS_ID = 5;
	
	protected $NUM_PARAMS = 6;
	
	protected $method = 'SECVPN.releaseCardFull';
	
}
?>