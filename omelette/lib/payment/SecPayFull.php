<?php
/**
 * For making a 'Full' transaction- this is also used for Deferred:
 * https://www.secpay.com/xmlrpc/realtimeTransaction.html
 * 
 * @author gj
 * @package Payment
 */
class SecPayFull extends SecPayRequest_Abstract {
	
	protected $IP = 3;
	
	/**
	 * The cardholders name as it is on their card.
	 */
	protected $NAME = 4;
	
	/**
	 * Credit card number
	 */
	protected $CARD_NUMBER = 5;
	
	/**
	 * The amount for the transaction, no currency or formatting
	 */
	protected $AMOUNT = 6;
	
	/**
	 * Card expiry date, either mm/yy or mmyy
	 */
	protected $EXP_DATE = 7;
	
	/**
	 * Switch/solo have an issue number, empty string for other card types
	 */
	protected $ISSUE_NUMBER = 8;
	
	/**
	 * Card start date, empty string otherwise
	 */
	protected $START_DATE = 9;
	
	/**
	 * Order details relevant to transaction: http://www.secpay.com/sc_api.html#order
	 */
	protected $ORDER = 10;
	
	/**
	 * Shipping details relevant to transaction: http://www.secpay.com/sc_api.html#shipping
	 */
	protected $SHIPPING = 11;
	
	/**
	 * Billing details relevant to transaction: http://www.secpay.com/sc_api.html#billing
	 */
	protected $BILLING = 12;
	
	/**
	 * Various other options, including deferred and repeat
	 */
	protected $OPTIONS = 13;
	
	protected $NUM_PARAMS = 14;
	
	protected $method = 'SECVPN.validateCardFull';
}
?>