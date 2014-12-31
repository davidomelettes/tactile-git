<?php
class TactileAccount extends OmeletteAccount {
	
	const TRIAL_DAYS = 14;
	
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
		$this->getField('site_address')->addValidator(
			new ReservedWordValidator(
				self::$reserved_words,
				'The Site Address you have chosen is already in use'
			)
		);
		$this->getField('site_address')->addValidator(new AlphaNumericValidator('Site Address must be at least 4 characters long, and can only contain letters (a-z) and numbers (0-9)'));
		$this->validateUniquenessOf('site_address','The Site Address you have chosen is already in use');
		$this->getField('account_expires')->setDefault(date(DATE_TIME_FORMAT,strtotime('+30 days')));
	}
	
	public function sendWelcomeEmail() {
		require_once 'Zend/Mail.php';
					
		$mail = new Omelette_Mail('welcome');
		$mail->getView()->set('Account',$this);
		
		$mail->getMail()->addTo(
			$this->email,
			$this->firstname.' '.$this->surname
		);
		$mail->setFrom(TACTILE_EMAIL_FROM,TACTILE_EMAIL_NAME);
		$mail->setSubject('Welcome to Tactile!');
		$mail->send();
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
	
	
	public function getLatestRepeatablePayment() {
		$db = DB::Instance();
		$query = 'SELECT * FROM payment_records WHERE account_id='.$db->qstr($this->id).' AND (authorised OR pre_authed) AND repeatable ORDER BY created DESC LIMIT 1';
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
	 * Returns the number of days since an account was created, if more than 14 it returns 15
	 *
	 * @return Integer
	 */
	public function account_age_days() {
		$now = time();
		$restart_trial_date = '2009-11-09';

		if (strtotime($this->created) <= strtotime($restart_trial_date)) {
			$created = strtotime($restart_trial_date);
		} else {
			$created = strtotime($this->created);
		}
		$days = round(($now-$created)/(60*60*24));
		return $days;
	}
	
	/**
	 * Returns true if in trial period (14 days) for free accounts, or true for paid plans
	 *
	 * @return Boolean
	 */
	public function in_trial() {
		if (!$this->is_free()) {
			return true;
		} else if ($this->account_age_days() < self::TRIAL_DAYS) {
			return true;
		}
		return false;
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
	 * Extend the expiry date of an account by some number of days
	 *
	 * @param Int $extension The number of days to extend by, default=30
	 * @return Boolean
	 */
	public function extend($extension=30, $originalDate = null) {
		if(isset($originalDate)) {
			$this->account_expires = date('Y-m-d H:i:s',strtotime($originalDate.'+'.$extension.' days'));
		} else {
			$this->account_expires = date('Y-m-d H:i:s',strtotime('+'.$extension.' days'));
		}

		return $this->save();
	}
	
	/**
	 * Send a message to the account owner telling them that payment couldn't be taken for a 2nd time,
	 * and that their account is locked
	 *
	 * @return void
	 */
	public function notifyOwnerOfSuspension() {
		$mail = new Omelette_Mail('account_suspension');
		$mail->getView()->set('account',$this);
		
		$mail->getMail()->addTo($this->email,$this->firstname.' '.$this->surname)
			->setFrom(TACTILE_EMAIL_FROM,TACTILE_EMAIL_NAME)
			->setSubject('Tactile CRM: Notification of Tactile Account Suspension');
		$mail->send();
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
	
	/**
	 * Should only be run if an admin cancels their account
	 */
	public function disableAllUsers() {
		$db = DB::Instance();
		$query = 'UPDATE users SET enabled=false WHERE username like '.$db->qstr('%//'.Omelette::getUserSpace());
		$db->Execute($query);
	}
	
	/**
	 * Disables the account, and then disables all the account's users
	 *
	 * @return boolean 
	 */
	public function cancel() {
		$db = DB::Instance();
		$db->startTrans();
		
		$this->disable();
		$this->disableAllUsers();
		
		$this->cancelled = date('Y-m-d H:i:s');
		$this->save();
		
		return $db->completeTrans();
	}
	
	/**
	 * Send an email to the account owner telling them that payment couldn't be taken; that we'll retry tomorrow
	 * but that if they want to change their card details they can do
	 *
	 * @return void
	 */
	public function notifyOwnerOfFailedPayment() {
		require_once 'Zend/Mail.php';
		$mail = new Omelette_Mail('payment_failed');
		$mail->getView()->set('account',$this);
		
		$mail->addTo($this->email,$this->firstname.' '.$this->surname)
			->setFrom(TACTILE_EMAIL_FROM,TACTILE_EMAIL_NAME)
			->setSubject('Tactile CRM: Notification of Tactile Payment Failure');
		$mail->send();	
	}
	
	public function setFreshbooksDetails($accountname, $token) {
		return (
			Tactile_AccountMagic::saveChoice('freshbooks_account', $accountname) &&
			Tactile_AccountMagic::saveChoice('freshbooks_token', $token)
		);
	}
	
	public function setZendeskDetails($siteaddress, $email, $password) {
		return (
			Tactile_AccountMagic::saveChoice('zendesk_siteaddress', $siteaddress) &&
			Tactile_AccountMagic::saveChoice('zendesk_email', $email) &&
			Tactile_AccountMagic::saveChoice('zendesk_password', $password)
		);
	}
	
	public function setCampaignMonitorDetails($cm_key, $cm_client_id, $cm_client) {
		return (
			Tactile_AccountMagic::saveChoice('cm_key', $cm_key) &&
			Tactile_AccountMagic::saveChoice('cm_client_id', $cm_client_id) &&
			Tactile_AccountMagic::saveChoice('cm_client', $cm_client)
		);
	}
	
	public function setXeroDetails($xero_key) {
		return Tactile_AccountMagic::saveChoice('xero_key', $xero_key);
	}
	
	public function clearFreshbooksDetails() {
		$db = DB::Instance();
		$db->StartTrans();
		$this->setFreshbooksDetails('','');
		$query = 'UPDATE organisations SET freshbooks_id = null WHERE usercompanyid = ' . $db->qstr(EGS::getCompanyId());
		$db->execute($query);
		return $db->CompleteTrans();
	}
	
	public function clearZendeskDetails() {
		$db = DB::Instance();
		$db->StartTrans();
		$this->setZendeskDetails('','', '');
		return $db->CompleteTrans();
	}
	
	public function clearCampaignMonitorDetails() {
		$db = DB::Instance();
		$db->StartTrans();
		$this->setCampaignMonitorDetails('', '', '');
		return $db->CompleteTrans();
	}
	
	public function clearXeroDetails() {
		$db = DB::Instance();
		$db->StartTrans();
		$this->setXeroDetails('');
		return $db->CompleteTrans();
	}
	
	public function isFreshbooksEnabled() {
		$name = Tactile_AccountMagic::getValue('freshbooks_account');
		$token = Tactile_AccountMagic::getValue('freshbooks_token');
		return !empty($name) && !empty($token);
	}
	
	public function isZendeskEnabled() {
		$siteaddress = Tactile_AccountMagic::getValue('zendesk_siteaddress');
		$email       = Tactile_AccountMagic::getValue('zendesk_email');
		$password    = Tactile_AccountMagic::getValue('zendesk_password');
		
		return !empty($siteaddress) && !empty($email) && !empty($password);
	}
	
	public function isCampaignMonitorEnabled() {
		$cm_key = Tactile_AccountMagic::getValue('cm_key');
		$cm_client_id = Tactile_AccountMagic::getValue('cm_client_id');
		$cm_client = Tactile_AccountMagic::getValue('cm_client');
		
		return !empty($cm_key) && !empty($cm_client_id) && !empty($cm_client); 
	}
	
	public function isResolveEnabled() {
		return $this->resolve_enabled == 't';
	}
	
	public function setEntanetDetails($domain, $code) {
		$this->entanet_domain = $domain;
		$this->entanet_code = $code;
		return $this->save();
	}
	
	public function clearEntanetDetails() {
		$db = DB::Instance();
		$db = DB::Instance();
		$db->StartTrans();
		$this->setEntanetDetails('','');
		$this->updateEntanetExtensions(array());
		return $db->CompleteTrans();
	}
	
	public function isEntanetEnabled() {
		$domain = $this->entanet_domain;
		$code = $this->entanet_code;
		return !empty($domain) && !empty($code);
	}
	
	public function getEntanetExtensionMapping() {
		$db = DB::Instance();
		$query = 'SELECT username, extension FROM entanet_extensions WHERE usercompanyid = ' . $db->qstr(EGS::getCompanyId());
		$rows = $db->GetAssoc($query);
		return $rows;
	}
	
	public function updateEntanetExtensions($extensions) {
		$db = DB::Instance();
		$db->StartTrans();
		$query = 'DELETE FROM entanet_extensions WHERE usercompanyid = ' . $db->qstr(EGS::getCompanyId());
		$db->Execute($query);
		
		$query = 'INSERT INTO entanet_extensions (username, extension, usercompanyid) VALUES (?, ?, ' . $db->qstr(EGS::getCompanyId()) . ')';
		$stmt = $db->Prepare($query);
		foreach($extensions as $username => $extension) {
			$username = str_replace('//' . Omelette::getUserSpace(), '', $username) . '//' . Omelette::getUserSpace();
			$db->Execute($stmt, array(
				$username,
				$extension
			));
		}
		
		return $db->CompleteTrans();
	}
	
	public function enableApiAccess() {
		$this->tactile_api_enabled = 'true';
		return $this->save();
	}
	
	public function disableApiAccess() {
		$db = DB::Instance();
		$db->StartTrans();
		
		// Delete all the API tokens for this account's users
		$sql = "UPDATE users SET api_token = NULL WHERE username LIKE " . $db->qstr('%//'.Omelette::getUserSpace());
		$db->execute($sql);
		$this->tactile_api_enabled = 'false';
		$this->save();
		
		return $db->CompleteTrans();
	}
	
	public function isApiEnabled() {
		return ($this->tactile_api_enabled == 't');
	}
	
	public function setTheme($theme) {
		if ($this->is_free() && !$this->in_trial()) {
			$theme = 'green';
		}
		switch ($theme) {
			case 'red':
			case 'blue':
			case 'grey':
			case 'orange':
			case 'purple':
			case 'custom':
				break;
			case 'green':
			default:
				$theme = 'green';
		}

		return Tactile_AccountMagic::saveChoice('theme', $theme);
	}
	
	public function getThemeCss() {
		if ($this->is_free() && !$this->in_trial()) {
			return 'themes/green.css';
		} else {
			$theme = Tactile_AccountMagic::getValue('theme', 'green');
			return 'themes/' . $theme . '.css';
		}
	}
	
	public function getCustomThemePrimary() {
		return Tactile_AccountMagic::getValue('theme_custom_primary', '#0F5E15');
	}
	
	public function getCustomThemeSecondary() {
		return Tactile_AccountMagic::getValue('theme_custom_secondary', '#569C30');
	}
	
	public function getFileSpaceLimit($formatted=false) {
		$limit_per_user = $this->getPlan()->file_space;
		$limit = $limit_per_user * $this->per_user_limit;
		if ($formatted) {
			$formatter = new FilesizeFormatter();
			$limit = $formatter->format($limit);
		}
		return $limit;
	}
	
	public function isTactileMailEnabled() {
		return Tactile_AccountMagic::getAsBoolean('tactilemail_enabled', 't', 't');
	}
	
	public function getLogoUrl() {
		$file = new S3File();
		if (FALSE !== $file->loadBy('account_id', $this->id)) {
			$protocol = (empty($_SERVER['HTTP_X_FARM']) || $_SERVER['HTTP_X_FARM'] != 'HTTPS') ? 'http' : 'https';
			return $protocol . '://s3.amazonaws.com/tactile_public/' .
				EGS::getCompanyId() . '/' . $file->id . '/' . $file->filename;
		} else {
			return false;
		}
	}
}
