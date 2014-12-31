<?php
class PaymentRecord extends DataObject {

	const TYPE_FULL = 'FULL';
	const TYPE_DEFERRED = 'DEFERRED';
	const TYPE_RELEASE = 'RELEASE';
	const TYPE_REPEAT = 'REPEAT';
	
	const TYPE_RELEASE_FAILED = 'RELEASE_FAILED';
	const TYPE_REPEAT_FAILED = 'REPEAT_FAILED';
	
	const TYPE_FALSE_PAYMENT = 'FALSE_RECORD';
	
	const TYPE_CANCELLED = 'CANCELLED';
	
	public function __construct() {
		parent::__construct('payment_records');
	}
	
	/**
	 * Returns true iff the payment is authorised
	 *
	 * @return Boolean
	 */
	public function isAuthorised() {
		return $this->authorised=='t';
	}
	
	/**
	 * Returns a collection of other payment-records that are attempted-repeats of this one
	 *
	 * @return PaymentRecordCollection
	 */
	public function getRepeatAttempts() {
		$payments = new PaymentRecordCollection();
		$sh = new SearchHandler($payments,false);
		$sh->addConstraint(new Constraint('payment_id','=',$this->id));
		$payments->load($sh);
		return $payments;
	}
	
	/**
	 * Returns a collection of other payment-records that are attempted-releases of this one
	 *
	 * @return PaymentRecordCollection
	 */
	public function getReleaseAttempts() {
		$payments = new PaymentRecordCollection();
		$sh = new SearchHandler($payments,false);
		$sh->addConstraint(new Constraint('payment_id','=',$this->id));
		$payments->load($sh);
		return $payments;
	}
	
	/**
	 * Generate a payment-record for the release of a deferred payment
	 *
	 * @param SecPayResponse $response
	 * @return Boolean
	 */
	public function generateReleaseRecord(SecPayResponse $response, $amount=null, $description='') {
		$errors = array();
		$record_data = array(
			'account_id'		=> $this->account_id,
			'amount'			=> is_null($amount) ? $this->amount : $amount,
			'pre_authed'		=> false,
			'auth_code'			=> $response->getAuthCode(),
			'test_status'		=> $this->test_status,
			'card_no'			=> $this->card_no,
			'card_expiry'		=> $this->card_expiry,
			'cardholder_name'	=> $this->cardholder_name,
			'trans_id'			=> $response->getTransId(),
			'payment_id'		=> $this->id,
			'authorised'		=> true,
			'type'				=> self::TYPE_RELEASE,
			'description'		=> $description
		);
		$record = DataObject::Factory($record_data,$errors,'PaymentRecord');
		if($record===false || $record->save()===false) {
			return false;
		}
		return $record->save();
	}
	
	/**
	 * Generates a record for a failed attempt at releasing the payment
	 *
	 * @param SecPayResponse $response
	 * @return Boolean
	 */
	public function generateFailedReleaseRecord(SecPayResponse $response, $description='') {
		$errors = array();
		$record_data = array(
			'account_id'		=> $this->account_id,
			'amount'			=> 0,
			'pre_authed'		=> false,
			'auth_code'			=> $response->getErrorCode(),
			'test_status'		=> $this->test_status,
			'card_no'			=> $this->card_no,
			'card_expiry'		=> $this->card_expiry,
			'cardholder_name'	=> $this->cardholder_name,
			'trans_id'			=> $response->getTransId(),
			'payment_id'		=> $this->id,
			'authorised'		=> false,
			'type'				=> self::TYPE_RELEASE_FAILED,
			'description'		=> $description,
			'repeatable'		=> 'f'
		);
		$record = DataObject::Factory($record_data,$errors,'PaymentRecord');
		if($record===false || $record->save()===false) {
			return false;
		}
		return $record->save();
	}
	
	
	/**
	 * Generate a payment-record for the cancellation of a deferred payment
	 *
	 * @param SecPayResponse $response
	 * @return Boolean
	 */
	public function generateCancellationRecord(SecPayResponse $response, $description='') {
		$errors = array();
		$record_data = array(
			'account_id'		=> $this->account_id,
			'amount'			=> -1,
			'pre_authed'		=> false,
			'auth_code'			=> $response->getAuthCode(),
			'test_status'		=> $this->test_status,
			'card_no'			=> $this->card_no,
			'card_expiry'		=> $this->card_expiry,
			'cardholder_name'	=> $this->cardholder_name,
			'trans_id'			=> $response->getTransId(),
			'payment_id'		=> $this->id,
			'authorised'		=> false,
			'type'				=> self::TYPE_CANCELLED,
			'description'		=> $description,
			'repeatable'		=> 'f'
		);
		$record = DataObject::Factory($record_data,$errors,'PaymentRecord');
		if($record===false || $record->save()===false) {
			return false;
		}
		return $record->save();
	}
	
	/**
	 * Generate the payment-record for the repeat of the payment
	 *
	 * @param SecPayResponse $response
	 * @param Amount $amount Specify this if the repeat-amount is different
	 * @return Boolean
	 */
	public function generateRepeatRecord(SecPayResponse $response, $amount = null, $description='') {
		$errors = array();
		$record_data = array(
			'account_id'		=> $this->account_id,
			'amount'			=> is_null($amount) ? $this->amount : $amount,
			'pre_authed'		=> false,
			'auth_code'			=> $response->getAuthCode(),
			'test_status'		=> $this->test_status,
			'card_no'			=> $this->card_no,
			'card_expiry'		=> $this->card_expiry,
			'cardholder_name'	=> $this->cardholder_name,
			'trans_id'			=> $response->getTransId(),
			'payment_id'		=> $this->id,
			'authorised'		=> true,
			'type'				=> self::TYPE_REPEAT,
			'description'		=> $description
		);
		$record = DataObject::Factory($record_data,$errors,'PaymentRecord');
		if($record===false || $record->save()===false) {
			return false;
		}
		return $record->save();
	}
	
	/**
	 * Generates a record for a failed attempt at repeating the payment
	 *
	 * @param SecPayResponse $response
	 * @return Boolean
	 */
	public function generateFailedRepeatRecord(SecPayResponse $response, $description='') {
		$errors = array();
		$record_data = array(
			'account_id'		=> $this->account_id,
			'amount'			=> 0,
			'pre_authed'		=> false,
			'auth_code'			=> $response->getErrorCode(),
			'test_status'		=> $this->test_status,
			'card_no'			=> $this->card_no,
			'card_expiry'		=> $this->card_expiry,
			'cardholder_name'	=> $this->cardholder_name,
			'trans_id'			=> $response->getTransId(),
			'payment_id'		=> $this->id,
			'authorised'		=> false,
			'type'				=> self::TYPE_REPEAT_FAILED,
			'description'		=> $description,
			'repeatable'		=> 'f'
		);
		$record = DataObject::Factory($record_data,$errors,'PaymentRecord');
		if($record===false || $record->save()===false) {
			return false;
		}
		return $record->save();
	}
	
	public function generateFalseRecord() {
		$errors = array();
		$record_data = array(
			'account_id'		=> $this->account_id,
			'amount'			=> 0,
			'pre_authed'		=> true,
			'auth_code'			=> 'XX',
			'card_no'			=> 'XXXX',
			'cardholder_name'	=> 'XXXX',
			'authorised'		=> false,
			'type'				=> self::TYPE_FALSE_PAYMENT,
			'repeatable'		=> 't'
		);
		$record = DataObject::Factory($record_data,$errors,'PaymentRecord');
		if($record===false || $record->save()===false) {
			return false;
		}
		return $record->save();
	}
	
	/**
	 * Builds and Saves a payment-record for a 'FULL' transaction
	 *
	 * @param TactileAccount $account
	 * @param AccountPlan $new_plan
	 * @param SecpayResponse $response
	 * @param PaymentCard $card
	 * @param String $trans_id
	 * @return PaymentRecord|false
	 */
	public static function saveFull($account, $response, $card, &$errors = array(), $description='') {
		$record_data = array(
			'account_id'		=> $account->id,
			'amount'			=> $response->getAmount(),
			'pre_authed'		=> false,
			'authorised'		=> true,
			'auth_code'			=> $response->getAuthCode(),
			'test_status'		=> $response->getTestStatus(),
			'card_no'			=> substr($card->getCardNumber(),-5),
			'card_expiry'		=> $card->getExpiry(),
			'cardholder_name'	=> $card->getCardholderName(),
			'trans_id'			=> $response->getTransId(),
			'type'				=> PaymentRecord::TYPE_FULL,
			'description'		=> $description 
		);

		$record = DataObject::Factory($record_data,$errors,'PaymentRecord');
		if($record===false || $record->save()===false) {
			$db->FailTrans();
			$db->CompleteTrans();
			return false;
		}
		return $record;
	}
	
}
