<?php
/**
 * Represents a card used for payment.
 * Handles validation of the various bits, and gives accessors for Transactions/Requests
 * @author gj
 * @package Payment
 */
class PaymentCard {
	
	/**
	 * The details provided about the card
	 * @access private
	 * @var Array
	 */
	private $card_details=array();
	
	/**
	 * A record of errors encountered during validation
	 * @access private
	 * @var Array
	 */
	private $errors = array();
	
	private $has_start_date = false;
	/**
	 * Constants for card names
	 */
	const MASTERCARD = 'Master Card';
	const MAESTRO = 'Maestro';
	const VISA = 'Visa';
	
	/**
	 * Mastercards begin 51-55 and have 16 digits in total
	 */
	const MASTERCARD_PATTERN = '#^5[1-5]\d{14}$#';
	
	
	/**
	 * Maestro card have one of these 4 prefixes, and either 16, 18, or 19 digits in total
	 */
	const MAESTRO_PATTERN = '#^(?:5020|5038|6304|6759)(?:\d{14}|\d{12})$#';
	
	/**
	 * Visa cards begin with a 4, and have either 13 or 16 digits in total
	 */
	const VISA_PATTERN = '#^4(?:\d{12}|\d{15})$#';
	
	private static $CARD_TYPES = array(
		self::MASTERCARD,
		//self::MAESTRO,
		self::VISA
	);
	
	public function __construct($card_details) {
		$this->card_details = $card_details;
	}
	
	public function isValid() {
		$this->normalize();
		if(!$this->isKnownType()) {
			$this->errors['card_type'] = 'Unknown card type';
			return false;
		}
		if($this->hasAnyMissingFields()) {
			return false;
		}
		if(!$this->cardNumberIsValid()) {
			$this->errors['card_number'] = 'You entered an invalid '.$this->card_details['card_type'].' number: '.$this->card_details['card_number'];
			return false;
		}
		if(!$this->expiryIsValid()) {
			return false;
		}
		if($this->has_start_date && !$this->startIsValid()) {
			return false;
		}
		return true;
	}
	
	
	/**
	 * Takes card values and puts them into known formats
	 * @return void
	 */
	private function normalize() {
		//card number
		if(isset($this->card_details['card_number'])) {
			$this->card_details['card_number'] = preg_replace('#[^0-9]#','',$this->card_details['card_number']);
		}
		
		//expiry
		if(isset($this->card_details['card_expiration_month']) && isset($this->card_details['card_expiration_month'])) {
			$this->card_details['card_expiry'] = $this->card_details['card_expiration_month']
												.$this->card_details['card_expiration_year'];
		}
		
		//and start date if there is one
		if(!empty($this->card_details['card_start_month'])&&!empty($this->card_details['card_start_year'])) {
			$this->has_start_date = true;
			$this->card_details['card_start'] =  $this->card_details['card_start_month']
												.$this->card_details['card_start_year'];											
		}

		//card-type
		if(isset($this->card_details['card_type'])) {		
			switch(strtolower($this->card_details['card_type'])) {
				case 'visa':
					$this->card_details['card_type'] = self::VISA;
					break;
				case 'switch':		//fall through
				case 'maestro':
					$this->card_details['card_type'] = self::MAESTRO;
					break;
				case 'mastercard':	//fall through
				case 'master card':
					$this->card_details['card_type'] = self::MASTERCARD;
					break;
			}
		}
		//Maestros need to have 0 as issue number if they don't have one
		if(!isset($this->card_details['card_issue'])||$this->card_details['card_issue']=='') {
			if(isset($this->card_details['card_type']) && $this->card_details['card_type'] == self::MAESTRO) {
				$this->card_details['card_issue'] = '0';
			}
			$this->card_details['card_issue'] = null;
		}
	}
	
	/**
	 * Returns true iff the card_type is one the class knows what to do with
	 * @return Boolean
	 */
	private function isKnownType() {
		return isset($this->card_details['card_type']) && in_array($this->card_details['card_type'],self::$CARD_TYPES);
	}
	
	/**
	 * Checks the supplied card data for any of the standard missing fields
	 * @return Boolean
	 */
	private function hasAnyMissingFields() {
		$required = array(
			'cardholder_name',
			'country',
			'card_number',
			'cv2',
			'card_expiration_month',
			'card_expiry'
		);
		$missing = false;
		foreach($required as $fieldname) {
			if(empty($this->card_details[$fieldname])) {
				$this->errors[$fieldname] = prettify($fieldname).' is a required field, you must enter a value';
				$missing = true;
			}
		}
		return $missing;
	}
	
	/**
	 * Checks if the card-number is valid against the format for card_type and the Luhn checksum
	 * http://en.wikipedia.org/wiki/Credit_card_number
	 * http://en.wikipedia.org/wiki/Luhn_algorithm
	 * @return Boolean
	 */
	public function cardNumberIsValid() {
		$number = $this->card_details['card_number'];
		switch($this->card_details['card_type']) {
			case self::MASTERCARD:
				$pattern = self::MASTERCARD_PATTERN;
				break;
			case self::MAESTRO:
				$pattern = self::MAESTRO_PATTERN;
				break;
			case self::VISA:
				$pattern = self::VISA_PATTERN;
				break;
		}
		if(0 == preg_match($pattern,$number)) {
			return false;
		}		
		
		$length = strlen($number);
		$parity = $length % 2;
		$sum = 0;
		for($i=0;$i<$length;$i++) {
			$digit = $number{$i};
			if($i%2 == $parity) {
				$digit*=2;
			}
			if($digit > 9) {
				$digit-=9;
			}
			$sum+=$digit;
		}
		return ($sum%10 == 0);
	}
	
	
	/**
	 * Returns true iff the given expiry date is a valid one
	 * @return Boolean
	 */
	private function expiryIsValid() {
		$year = $this->card_details['card_expiration_year'];
		$month = $this->card_details['card_expiration_month'];
		if($month>12 || $month < 1) {
			$this->errors['card_expiration_month'] = 'Invalid expiry date';
			return false; 
		}
		if($year > date('y') || ($year == date('y') && $month >= date('m'))) {
			return true;
		}
		$this->errors['card_expiration_year'] = 'Expiry date can\'t be in the past';
		return false;
	}
	
	private function startIsValid() {
		$year = $this->card_details['card_start_year'];
		$month = $this->card_details['card_start_month'];
		if($month>12 || $month < 1) {
			$this->errors['card_start_month'] = 'Invalid start date';
			return false; 
		}
		if($year < date('y') || ($year == date('y') && $month <= date('m'))) {
			return true;
		}
		$this->errors['card_start_year'] = 'Start date can\'t be in the future';
		return false;
	}
	
	public function getErrors() {
		return $this->errors;
	}
	
	
	/**
	 * Accessors for properties
	 */

	public function getCardNumber() {
		return $this->card_details['card_number'];
	}
	
	public function getCV2() {
		return $this->card_details['cv2'];
	}
	
	public function getExpiry() {
		return $this->card_details['card_expiry'];
	}
	
	public function getCardholderName() {
		return $this->card_details['cardholder_name'];
	}
	
	public function getCardType() {
		return $this->card_details['card_type'];
	}
	
	public function getCountry() {
		return $this->card_details['country'];
	}
	
	/**
	 * Return the address parts as an associative array
	 *
	 * @return Array
	 */
	public function getAddress() {
		$address = array(
			'addr_1'=>'',
			'addr_2'=>'',
			'city'=>'',
			'state'=>'',
			'post_code'=>'',
			'country'=>'',
			'phone'=>''
		);
		$address['name'] = $this->getCardholderName();
		foreach($address as $key=>$value) {
			if(!empty($this->card_details[$key])) {
				$address[$key] = $this->card_details[$key];
			}
		}
		return $address;
	}
	
}
?>