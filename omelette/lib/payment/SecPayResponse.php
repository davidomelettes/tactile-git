<?php
/**
 * Represents a response as sent by SecPay, either as an async callback, or as a repeat-callback
 * @author gj
 * @package Payment
 */
abstract class SecPayResponse {
	
	/**
	 * The details of the response that could be relevant for validation
	 * @access private
	 * @var Array
	 */
	protected $response_details = array();
	
	/**
	 * An array of any errors generated during validation
	 * @access private
	 * @var Array
	 */
	protected $errors = array(); 
	
	/**
	 * Constants for parameter names
	 */
	const VALID = 'valid';
	const TRANS_ID = 'trans_id';
	const CODE = 'code';
	const AUTH_CODE = 'auth_code';
	const AMOUNT = 'amount';
	const IP = 'ip';
	const TEST_STATUS = 'test_status';
	const HASH = 'hash';
	
	/**
	 * Constructor
	 * Supply the callback path (not including domain) and an array of parameters received from secpay
	 * (the response_details can include extra fields, they will be ignored if they're not needed)
	 * @param String $callback_path
	 * @param Array $response_details
	 */
	public function __construct($response_details) {
		$this->response_details = $response_details;
	}
	
	/**
	 * Returns the array of error messages generated during validation
	 * @return Array
	 */
	public function getErrors() {
		return $this->errors;
	}
	
	public function wasSuccessful() {
		
	}
	
	/**
	 * Returns true iff the response is valid, 
	 * i.e. has enough info to be worth checking whether it was successful or not
	 *
	 * @return Boolean
	 */
	abstract public function isValid();
	
	public static function isError($code) {
		return $code !== 'A';
	}
	
	public function getErrorMsg() {
		@list($code,$details) = explode(':',$this->response_details['code']);
		switch($code) {
			case 'C':
				$msg = 'The payment gateway had problems communicating with your bank, please try again.';
				break;
			case 'P': {
				$details=str_split($details);
				foreach($details as $detail) {
					switch($detail) {
						case 'S':
							$msg = 'The start date you provided for the card isn\'t valid';
							break;
						case 'E':
							$msg = 'The expiry date you provided for your card is invalid';
							break;
						case 'I':
							$msg = 'The Issue Number you provided is invalid';
							break;
						case 'C':
							$msg = 'The card number you provided is invalid';
							break;
						case 'T':
							$msg = 'The card type you selected doesn\'t match the card number';
							break;
						case 'V':
							$msg = 'The CV2 value you provided is invalid';
							break;
						case 'R':
							$msg = 'The payment gateway had problems communicating with your bank, please try again.';
							break;
						default:
							$msg = $this->response_details['message'];
					}
				}
				break;
			}
			default:
				$msg = 'There was an error trying to process your payment, please try again. If problems persist, please let us know by emailing support@tactilecrm.com';
				break;		
		}
		return $msg;
	}
	
	
	public function getErrorCode() {
		return $this->response_details[self::CODE];
	}
	
	public function getAuthCode() {
		return $this->response_details[self::AUTH_CODE];
	}
	
	public function getTestStatus() {
		return $this->response_details[self::TEST_STATUS];
	}
	
	public function getTransId() {
		return $this->response_details[self::TRANS_ID];
	}
	
	public function getAmount() {
		if(isset($this->response_details[self::AMOUNT])) {
			return $this->response_details[self::AMOUNT];
		}
		return false;
	}
}
?>