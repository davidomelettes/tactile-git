<?php
/**
 * Container for things that are nearly always constant
 */
class EGS {
	
	/**
	 * The currently-in-use system-company-id (EGS_COMPANY_ID)
	 * @access private
	 * @static int $company_id
	 */
	private static $company_id;
	
	/**
	 * The currently-in-use username (EGS_USERNAME)
	 * @access private
	 * @static string $username 
	 */
	private static $username;
	
	private static $date_format;
	
	private static $datetime_format;
	
	private static $currency_symbol;
	
	private static $country_code;
	
	/**
	 * Setter for company_id
	 *
	 * @param int $company_id
	 * @return void
	 */
	static public function setCompanyId($company_id) {
		self::$company_id = $company_id;
	}
	
	/**
	 * Accessor for company_id
	 * @return int
	 */
	static public function getCompanyId() {
		if(empty(self::$company_id)) {
			throw new Exception('EGS::company_id hasn\'t been set');
		}
		return self::$company_id;
	}
	
	/**
	 * Setter for username
	 * @param String $username
	 * @return void
	 */
	static public function setUsername($username) {
		self::$username = $username;
	}
	
	/**
	 * Accessor for username
	 *
	 * @return String
	 */
	static public function getUsername() {
		if(empty(self::$username)) {
			throw new Exception('EGS::username hasn\'t been set');
		}
		return self::$username;
	}
	
	static public function getDateFormat() {
		if(empty(self::$date_format)) {
			throw new Exception("EGS::date_format hasn't been set");
		}
		return self::$date_format;
	}
	
	static public function setDateFormat($format) {
		self::$date_format = $format;
	}
	
	static public function setDateTimeFormat($format) {
		self::$datetime_format = $format;
	}
	
	
	static public function getDateTimeFormat() {
		if(empty(self::$date_format)) {
			throw new Exception("EGS::datetime_format hasn't been set");
		}
		return self::$date_format.' H:i';
	}
	
	static function getCurrencySymbol() {
		return self::$currency_symbol;
	}
	
	static function setCurrencySymbol($symbol) {
		self::$currency_symbol = $symbol;
	}
	
	static function setCountryCode($code) {
		self::$country_code = $code;
	}
	
	static function getCountryCode() {
		if (empty(self::$country_code)) {
			//throw new Exception("EGS::country_code hasn't been set");
			return 'GB';
		}
		return self::$country_code;
	}
}
