<?php
Autoloader::Instance()->addPath(FILE_ROOT.'omelette/lib/payment/');
class OmeletteAccount extends DataObject {
	
	private static $reserved_words = array(
		'forum(s)?',
		'help',
		'mail',
		'mx\d+',
		'ns\d+',
		'www',
		'tactile'
	);
	
	public function __construct() {
		parent::__construct('tactile_accounts');
		/*$this->getField('site_address')->addValidator(
			new ReservedWordValidator(
				self::$reserved_words,
				'The Site Address you have chosen is already in use'
			)
		);
		$this->getField('site_address')->addValidator(new AlphaNumericValidator('Site address can only contain letters (A-Z) and numbers (0-9)'));
		$this->validateUniquenessOf('site_address','The Site Address you have chosen is already in use');
		$this->getField('account_expires')->setDefault(date(DATE_TIME_FORMAT,strtotime('+30 days')));
*/
	}
	
	/**
	 * Return the most recent successful paymentrecord for the account, or false if none exist
	 * 
	 * @return PaymentRecord
	 */
	public function getLatestPayment() {
		$db = DB::Instance();
		$query = 'SELECT * FROM payment_records WHERE account_id='.$db->qstr($this->id).' AND (authorised OR pre_authed)  ORDER BY created DESC LIMIT 1';
		$row = $db->GetRow($query);
		if($row!==false&&count($row)>0) {
			$record = new PaymentRecord();
			$record->_data = $row;
			$record->load($row['id']);
			return $record;
		}
		return false;
	}
	
	
	/**
	 * Cancels any outstanding deferred transactions against the account 
	 * (that haven't already been released or cancelled)
	 * 
	 * @param Zend_Log optional $logger - 'warn' on failure, 'info' on success
	 * @return Int the number of cancellations or FALSE on failure
	 */
	public function cancelOutstandingDeferred(Zend_Log $logger = null) {
		$db = DB::Instance();
		$query = 'SELECT * FROM payment_records 
			WHERE type='.$db->qstr(PaymentRecord::TYPE_DEFERRED).'
			AND account_id='.$db->qstr($this->id).'
			AND id NOT IN (
				SELECT payment_id FROM payment_records 
				WHERE account_id='.$db->qstr($this->id).'
				AND payment_id is not null 
			)';
		$transaction_ids = $db->GetArray($query);
		foreach ($transaction_ids as $row) {
			$payment = new PaymentRecord();
			$payment->_data = $row;
			$payment->load($row['id']);
			
			$delete = new SecPayCancelDeferred(SECPAY_MID, SECPAY_VPN_PASSWORD);
			if(!is_null($logger)) {
				$delete->setLogger($logger);
			}
			$delete->setTransId($payment->trans_id);
			$delete->setRemotePswd(SECPAY_REMOTE);
			$response = $delete->send();
			if($response===false) {
				if(!is_null($logger)) {
					$logger->warn('CancelDeferred failed for account '.$this->id);
				}
				return false;
			}
			$payment->generateCancellationRecord($response);
			if(!is_null($logger)) {
				$logger->info("CancelDeferred didn't fail for account ".$this->id);
			}
		}
		return count($transaction_ids);	
	}
	
	
	/**
	 * Returns true iff the account_plan of the account is free, i.e. cost_per_month=='0'
	 *
	 * @return Boolean
	 */
	public function is_free() {
		return $this->getPlan()->is_free();
	}
	
	/**
	 * Return true iff the account is enabled
	 *
	 * @return Boolean
	 */
	public function is_enabled() {
		return $this->enabled == 't';
	}
	
	/**
	 * Returns the AccountPlan associated with the account
	 *
	 * @return AccountPlan
	 */
	public function getPlan() {
		return Omelette::getAccountPlan();
	}
	
	/**
	 * Extend the expiry date of an account by some number of days
	 *
	 * @param Int $extension The number of days to extend by, default=30
	 * @return Boolean
	 */
	public function extend($extension=30) {
		$this->account_expires = date('Y-m-d H:i:s',strtotime('+'.$extension.' days'));
		return $this->save();
	}
	
	/**
	 * Stop people being able to login to the account
	 *
	 * @return Boolean
	 */
	public function suspend() {
		$sys_company = new Systemcompany();
		$sys_company->loadBy('organisation_id',$this->organisation_id);
		$sys_company->enabled = 'false';
		return $sys_company->save();
	}
	
	public function unsuspend() {
		$sys_company = new Systemcompany();
		$sys_company->loadBy('organisation_id',$this->organisation_id);
		$sys_company->enabled = 'true';
		return $sys_company->save();
	}
	
	/**
	 * Set the account status to disabled. Disabled accounts don't even try to have payments taken
	 *
	 * @return void
	 */
	public function disable() {
		$this->enabled = 'false';
		$this->save();
	}
	
	public function enable() {
		$this->enabled = 'true';
		$this->save();
	}
	
	public function disableAllUsers() {
		$db = DB::Instance();
		$query = 'UPDATE users SET enabled=false WHERE username like '.$db->qstr('%//'.Omelette::getUserSpace());
		$db->Execute($query);
	}
	
}
?>
