<?php
class SecPayASyncResponse extends SecPayResponse {
	
	/**
	 * The path (not including hostname) of the callback script - used in the hash
	 */
	private $callback_path;
	
	/**
	 * The fields used to generate the hash
	 * @access private
	 * @var Array
	 */
	private $hash_fields = array(
		self::VALID,
		self::TRANS_ID,
		self::CODE,
		self::AUTH_CODE,
		self::AMOUNT,
		self::IP,
		self::TEST_STATUS
	);
	
	public function setCallbackPath($callback_path) {
		$this->callback_path = $callback_path;
	}
	
	/**
	 * Returns true iff the response is valid
	 * @return Boolean
	 */
	public function isValid() {
		if($this->hasAnyMissingFields()){
			return false;
		}
		if(!$this->hashIsValid()) {
			return false;
		}
		return true;
	}
	
	/**
	 * Override which fields, and in what order, are used for the hash
	 * @param Array $fields
	 * @return void
	 */
	public function setHashFields($fields) {
		$this->hash_fields = $fields;
	}
	
	/**
	 * @todo
	 */
	private function hasAnyMissingFields() {
		return false;
	}
	
	/**
	 * Compares the secpay-supplied hash against the one we put together and returns true if they match
	 * @return Boolean
	 */
	private function hashIsValid() {
		$their_hash = $this->response_details['hash'];
		$pre_hash = $this->callback_path . '?';
		
		$hash_array = array();
		foreach($this->hash_fields as $key) {
			$hash_array[$key] = $this->response_details[$key];
		}
		$pre_hash.=http_build_query($hash_array,null,'&');

		$pre_hash .= '&'.SECPAY_DIGEST;
		
		$my_hash = md5($pre_hash);
		
		$valid = $their_hash === $my_hash;
		if(!$valid) {
			$this->errors[] = 'Hash invalid';
			return false;
		}
		return true;
	}
}
?>