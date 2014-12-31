<?php
/**
 * For making a call to SecPay to Repeat a previously placed transaction
 * https://www.secpay.com/xmlrpc/repeatTransaction.html
 * 
 * @author gj
 * @package Payment
 */
class SecPayRepeat extends SecPayRequest_Abstract {
	
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
	
	/**
	 * Allows a new expiry date to be provided
	 */
	protected $EXP_DATE = 6;
	
	/**
	 * Allows order-details to be re-submitted
	 */
	protected $ORDER = 7;
	
	/**
	 * Used for submitting shipping details
	 */
	protected $SHIPPING = 8;
	
	/**
	 * Used for submitting billing details
	 */
	protected $BILLING = 9;
	
	/**
	 * Used for submitting the extra options
	 */
	protected $OPTIONS = 10;
	
	/**
	 * The number of params used for this call
	 */
	protected $NUM_PARAMS = 11;
	
	/**
	 * The XMLRPC method-name for this call
	 *
	 * @var String
	 */
	protected $method = 'SECVPN.repeatCardFullAddr';
	
	
}
?>