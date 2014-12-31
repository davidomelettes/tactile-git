<?php
/**
 * Represents a Synchronouse response from SecPay- i.e. a direct response to a request, rather than a callback
 * 
 * @author gj
 * @package Payment
 */
class SecPaySyncResponse extends SecPayResponse {
	
	
	/**
	 * Returns true iff the response is valid, 
	 * i.e. has enough info to be worth checking whether it was successful or not
	 *
	 * @return Boolean
	 */
	public function isValid() {
		if($this->hasAnyMissingFields()) {
			return false;
		}
		return true;
	}
	
	/**
	 *
	 * @return Boolean
	 */
	private function hasAnyMissingFields() {
		$required = array(
			'valid',
			'code'
		);
		foreach($required as $fieldname) {
			if(empty($this->response_details[$fieldname])) {
				return true;
			}
		}
		return false;
	}
	
	
	public function isSuccessful() {
		if($this->response_details['valid']==='true'&&$this->response_details['code']=='A') {
			return true;
		}
		if(self::isError($this->response_details['code'])) {
			$this->errors[] = $this->getErrorMsg();
		}
	}
}
?>