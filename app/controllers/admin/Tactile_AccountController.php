<?php

/**
 * Responsible for actions undertaken by the account-owner to do with their Tactile account
 * - accessible to Account-Owners only, this is handled externally
 * 
 * @author gj
 */
class Tactile_AccountController extends Controller {

	/**
	 * Display a list of the options available
	 * - change_payment only if $current_plan isn't free
	 *
	 */
	public function index() {
		$this->view->set('plan', CurrentlyLoggedInUser::Instance()->getAccount()->getPlan());
	}
	
	/**
	 * Displays the user's usage as compared to the limits of their plan
	 *
	 */
	public function usage() {
		Autoloader::Instance()->addPath(APP_CLASS_ROOT.'usage/');
		
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$plan = $account->getPlan();
		
		$usages = array(
			'user_limit'=>new UserUsageChecker($account),
			'contact_limit'=>new ContactUsageChecker($account),
			'file_space'=>new FileUsageChecker($account),
			'opportunity_limit'=>new OpportunityUsageChecker($account)
		);
		
		$this->view->set('usages', $usages);
		$this->view->set('account', $account);
		$this->view->set('plan', $plan);
	}
	
	/**
	 * Displays the user's referrals/comission
	 *
	 */
	public function referrals() {
		if(isset($this->_data['tactile_referral_agree'])) {
			Tactile_AccountMagic::saveChoice('referral_terms_agreed', time());
		}
		
		$referral_date = Tactile_AccountMagic::getValue('referral_terms_agreed');

		if(!empty($referral_date)) {
			Autoloader::Instance()->addPath(APP_CLASS_ROOT.'referrals/');
	
			$account = CurrentlyLoggedInUser::Instance()->getAccount();
			
			$referrals = new ReferralsChecker($account);
	
			$this->view->set('total_referrals', $referrals->getTotalReferrals());
			$this->view->set('free_referrals', $referrals->getFreeReferrals());
			$this->view->set('paid_referrals', $referrals->getPaidReferrals());
			$this->view->set('statement', $referrals->getStatement());
			$this->view->set('site_address', $account->site_address);
		} else {
			Flash::Instance()->addError("You must agree to the Terms and Conditions to join the Referral Program");
			sendTo('admin');
		}
	}
	
	/** 
	 * Display the user's invoices
	 */
	function invoices() {
		Autoloader::Instance()->addPath(APP_CLASS_ROOT.'invoices/');
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$invoices = new Invoices($account);
		$this->view->set('invoices', $invoices->getInvoices());
		
		$account_details = $invoices->getAccountDetails();
		$this->view->set('account_email', $account_details['email']);
		$this->view->set('account_repeat', $account_details['repeat']);
		$this->view->set('cost_per_month', $account_details['cost_per_month']);
		$this->view->set('site_address', $invoices->site_address);
	}
	
	/**
	 * Display a form for the user to enter new payment details
	 * - only if they're not on a free plan
	 */
	public function payment_details() {
		if(empty($_SERVER['HTTP_X_FARM'])) {
			$_SERVER['HTTP_X_FARM'] = 'HTTPS';
			sendTo('account', 'payment_details');
			return;
		}
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$plan = $account->getPlan();
		if($plan->is_free()) {
			Flash::Instance()->addMessage("You can't change your payment details while on a free plan");
			sendTo('account');
		}
		$expires = date('jS F Y',strtotime($account->account_expires));
		$this->view->set('account_expires', $expires);
		
		$this->view->set('previous',$this->restoreData());
		unset($_SESSION['_controller_data']);
		
		$country = new Country();
		$this->view->set('country_list', $country->getAll());
	}
	
	/**
	 * Take the form values, and then sort out how this affects any existing payments:
	 * - if there are any outstanding deferred payments for the account, cancel them
	 *   - this will happen if someone changes their details before the end of the trial, or changes them again before the next payment-day
	 * - create a new payment, for the amount of the current_plan
	 *
	 * @see AccountChecker
	 */
	public function process_details_change() {
		$this->logger->info("Beginning change of payment details for ".EGS::getUsername());

		$bannedNames = array(
                        'chely kim',
                );

		if(in_array(strtolower(trim($this->_data['Card']['cardholder_name'])), $bannedNames)) {
			Flash::Instance()->addError("There was a problem with the card details you entered");
			sendTo('account','payment_details');
			return;
		}

		AutoLoader::Instance()->addPath(FILE_ROOT.'omelette/lib/payment/');
		$db = DB::Instance();
		$db->StartTrans();
		
		$user = CurrentlyLoggedInUser::Instance();
		
		$account = $user->getAccount();
		$cancelled = $account->cancelOutstandingDeferred($this->logger);
		if($cancelled === false) {
			$db->FailTrans();
			$db->CompleteTrans();
			Flash::Instance()->addError("There was a problem communicating with the payment gateway, our technicians have been notified");
			$this->saveData();
			sendTo('account','payment_details');
			return;
		}
		$this->logger->info($cancelled." cancellations carried out");
		
		$plan = $account->getPlan();		
		
		//then check the card bits are good
		$card_details = $this->_data['Card'];
		if(!empty($this->_data['Card']['card_number'])) {
			$this->_data['Card']['card_number'] = '*';
		}
		if(!empty($this->_data['Card']['cv2'])) {
			$this->_data['Card']['cv2'] = '*';
		}
		$card = new PaymentCard($card_details);
		if(!$card->isValid()) {
			$this->logger->info("Card-validation failed: ".print_r($card->getErrors(), true));
			$db->FailTrans();
			$db->CompleteTrans();
			Flash::Instance()->addErrors($card->getErrors());
			$this->saveData();
			sendTo('account','payment_details');
			return;
		}
		
		//only get here if account+card seem ok
		
		//then we build the request that will go to Secpay
		$request = new SecPayFull(SECPAY_MID,SECPAY_VPN_PASSWORD);
		$request->setLogger($this->logger);
		$trans_id = 'Tactile'.date('Ymdhis');
		$request->addPaymentCard($card);
		$request->setTransId($trans_id);
		$request->setCustomerIP(!empty($_SERVER['X_FORWARDED_FOR'])?$_SERVER['X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
		
		$request_amount = 0;
		if ($plan->is_per_user()) {
			$request_amount = $plan->cost_per_month * $account->per_user_limit;
		} else {
			$request_amount = $plan->cost_per_month;
		}
		$request->setAmount($request_amount);
		
		$request->setDeferred(SecPayRequest::DEFERRED_FULL);
		
		$request->setTest(SECPAY_TEST_STATUS);
		
		//this needs to be done after other things!!!
		$request->setDigest(SECPAY_REMOTE);
		//then make the request, we get back a SecPaySyncResponse()
		$response = $request->send();
		if($response === false) {
			$this->logger->info("Request Failed horribly for trans_id ".$trans_id);
			$db->FailTrans();
			$db->CompleteTrans();
			Flash::Instance()->addError("There was a problem communicating with the payment gateway, our technicians have been notified");
			$this->saveData();
			sendTo('account','payment_details');
			return;
		}
		
		//checks that there are enough fields sent back
		if(!$response->isValid()) {
			$this->logger->info("Response was invalid for trans_id".$trans_id);
			$this->logger->info(print_r($response->getErrors(),true));
			$this->logger->info($request->getRawResponse()->saveXML());
			$db->FailTrans();
			$db->CompleteTrans();
			Flash::Instance()->addErrors($response->getErrors());
			$this->saveData();
			sendTo('account','payment_details');
			return;
		}
		
		//then check that the response is 'valid' and with a good code, generates errors for user
		if(!$response->isSuccessful()) {
			$this->logger->info("Response was unsuccessful for trans_id".$trans_id);
			$this->logger->info(print_r($response->getErrors(),true));
			$this->logger->info($request->getRawResponse()->saveXML());
			$db->FailTrans();
			$db->CompleteTrans();
			Flash::Instance()->addErrors($response->getErrors());
			$this->saveData();
			sendTo('account','payment_details');
			return;
		}
		$errors = array();
		$record_data = array(
			'account_id'		=> $account->id,
			'amount'			=> $request_amount,
			'pre_authed'		=> true,
			'auth_code'			=> $response->getAuthCode(),
			'test_status'		=> $response->getTestStatus(),
			'card_no'			=> substr($card->getCardNumber(),-5),
			'card_expiry'		=> $card->getExpiry(),
			'cardholder_name'	=> $card->getCardholderName(),
			'trans_id'			=> $trans_id,
			'type'				=> PaymentRecord::TYPE_DEFERRED,
			'description'		=> "Changing payment details"
		);
		$record = DataObject::Factory($record_data,$errors,'PaymentRecord');
		if($record===false || $record->save()===false) {
			$this->logger->warn("Saving payment record failed: ".print_r($errors,true));
			$this->logger->warn("Record data: ".print_r($record_data, true));
			$db->FailTrans();
			$db->CompleteTrans();
		}
		
		Flash::Instance()->addMessage('Details changed successfully');
		$db->CompleteTrans();
		sendTo('account');
		return;
	}
	
	public function account_details() {
		$db = DB::Instance();
		
		$country = new Country();
		$this->view->set('country_list', $country->getAll());
		
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		
		$this->view->set(
			'current',
			array(
				'company' => $account->company,
				'firstname' => $account->firstname,
				'surname' => $account->surname,
				'email' => $account->email,
				'country_code' => $account->country_code,
				'vat_number' => $account->vat_number
			)
		);
		$this->view->set('default_country_code', 'US');
	}
	
	public function process_account_details_change() {
		$db = DB::Instance();
		$db->StartTrans();
		
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		
		$old_address = $db->getOne("SELECT email FROM tactile_accounts WHERE id = {$db->qstr($account->id)}");
		
		$details = $this->_data['Details'];
		$details['id'] = $account->id;
		
		foreach ($details as $key => $value) {
			if (empty($details[$key])) $details[$key] = null;
		}
		
		$db->replace('tactile_accounts', $details, 'id', true);
		
		$new_details = $db->getRow(
			"SELECT 
				ta.firstname,
				ta.surname,
				ta.email,
				ta.username,
				ta.site_address,
				ap.name AS plan_name
				FROM tactile_accounts ta
				LEFT JOIN account_plans ap ON ap.id = ta.current_plan_id
				WHERE ta.id = {$db->qstr($account->id)}"
		);
		
		// We update Tactile CRM before sending the email so that the persons details etc. are present
		if(PRODUCTION){
			try {
				require_once 'Tactile/Api.php';
				require_once 'Tactile/Api/Organisation.php';
				$user = CurrentlyLoggedInUser::Instance();
				$client = new Tactile_Api(TACTILE_API_DOMAIN, TACTILE_API_KEY);
				$org = $client->getOrganisations(array('accountnumber' => $user->getAccount()->site_address));
				
				$org_id = null;
				
				$update_org = new Tactile_Api_Organisation();
				if(($org->status == "success") && ($org->total == 1)) {
					// We have found the organisation in Tactile CRM
					$org_id = $org->organisations[0]->id;
					
					// We need to loop over current details to push them up to Tactile
					foreach($org->organisations[0] as $key => $value) {
						if(!empty($value)) {
							$update_org->$key = $value;
						}
					}
				} else {
					$org->accountnumber = $user->getAccount()->site_address;	
				}
				
				$update_org->name = $details['company'];
				$update_org->vatnumber=$details['vat_number'];
				$update_org->country_code = $details['country_code'];
				
				$new_org = $client->saveOrganisation($update_org);
			
				if($new_org->status == "success") {
					$org_id = $new_org->id;
				}
				
				if(!empty($org_id)) {
					// Nothing has gone wrong getting the org so we'll set the status to cancelled
					require_once 'Tactile/Api/Person.php';
			
					$people = $client->getPeople(array('email' => $user->getAccount()->email, 'organisation_id' => $org_id));
					
					$person_id = null;
					
					$person = new Tactile_Api_Person();
					if(($people->status == "success") && ($people->total == 1)) {
						// We have found the Person in Tactile CRM
						
						foreach($people->people[0] as $key => $value) {
							if(!empty($value)) {
								$person->$key = $value;
							}
						}
					} else {
						// Add a new person
						$person = new Tactile_Api_Person();
						$person->organisation_id = $org_id;
					}
					$person->firstname = $details['firstname'];
					$person->surname = $details['surname'];
					$person->email = $details['email'];
						
					$new_person = $client->savePerson($person);
					if($new_person->status == "success") {
						$person_id = $new_person->id;
					}
					
					
				}
	
			} catch (Exception $e) {
				  require_once 'Zend/Log.php';
				  $logger = new Zend_Log(new Log_Writer_Mail(NOTIFICATIONS_TO, 'Error updating our Tactile CRM via the API'));
				  $logger->crit($e->getMessage());
				  $logger->crit($e->getTraceAsString());
			}
					
			
			try {
				$client = @new SoapClient("http://api.createsend.com/api/api.asmx?wsdl");
				
				if ($old_address !== $new_details['email']) {
					$response = $client->Unsubscribe(
						array(
							'ApiKey' => OMELETTES_CM_API_KEY,
							'ListID' => OMELETTES_CM_LIST_ID,
							'Email' => $old_address
						)
					);
				}
				
				$response = $client->AddSubscriberWithCustomFields(
					array(
						'ApiKey' => OMELETTES_CM_API_KEY,
						'ListID' => OMELETTES_CM_LIST_ID,
						'Email' => $new_details['email'],
						'Name' => trim(ucwords($new_details['firstname'] . ' ' . $new_details['surname'])),
						'CustomFields' => array(
							array(
								'Key' => 'site_address',
								'Value' => $new_details['site_address']
							),
							array(
								'Key' => 'username',
								'Value' => $new_details['username']
							),
							array(
								'Key' => 'plan_name',
								'Value' => strtolower($new_details['plan_name'])
							)
						)
					)
				);
			} catch (Exception $e) {
				require_once 'Zend/Log.php';
				$logger = new Zend_Log(new Log_Writer_Mail(DEBUG_EMAIL_ADDRESS, 'Campaign monitor cancellation subscription problem'));
				$logger->crit($e->getMessage());
				$logger->crit($e->getTraceAsString());
			}
		}
		
		
		Flash::Instance()->addMessage('Details changed successfully');
		$db->CompleteTrans();
		sendTo('account');
		return;
	}
	
	/**
	 * Display the table of plans with their prices and links to 'change to'
	 *
	 */
	public function change_plan() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$current_plan = $account->getPlan();
		$this->view->set('current_plan', $current_plan);
		if ($current_plan->is_per_user() && !$current_plan->is_free()) {
			// Can't change plans if you're paid per-user
			sendTo('account');
			return;
		}
		
		if(empty($_SERVER['HTTP_X_FARM'])) {
			$_SERVER['HTTP_X_FARM'] = 'HTTPS';
			sendTo('account', 'change_plan');
			return;
		}
		
		$db = DB::Instance();
		if ($current_plan->is_per_user()) {
			// Upgrading from free per-user to paid per-user
			if(isset($this->_data['plan']) && ($this->_data['plan'] == 'enterprise')) {
				$sql = "SELECT id FROM account_plans WHERE cost_per_month != 0 AND per_user AND name='Enterprise'";
			} else {
				$sql = "SELECT id FROM account_plans WHERE cost_per_month != 0 AND per_user ORDER BY cost_per_month ASC";
			}
			$id = $db->getOne($sql);
			$paid_plan = new AccountPlan();
			if (false === $paid_plan->load($id)) {
				// Terrible error
				Flash::Instance()->addError('Failed to load plan details. Please contact support.');
				sendTo('account');
				return;
			} else {
				$this->view->set('paid_plan', $paid_plan);
				$this->view->set('currency', '£');
				$this->view->set('cpupm', $paid_plan->cost_per_month);
			}
		} else {
			$query = 'SELECT * FROM account_plans';
			$plans = $db->GetAssoc($query);
			
			$payment = $account->getLatestPayment();
			if($payment === false) {
				$this->view->set('needs_card_details', true);
			}
			
			$plan_names = array();
			$available = array();		
			Autoloader::Instance()->addPath(APP_CLASS_ROOT.'usage/');
			
			$usages = array(
				'user_limit'=>new UserUsageChecker($account),
				'contact_limit'=>new ContactUsageChecker($account),
				'file_space'=>new FileUsageChecker($account),
				'opportunity_limit'=>new OpportunityUsageChecker($account)
			);
			
			$plan = DataObject::Construct('AccountPlan');
			foreach($plans as $id=>$row) {
				$row['name'] = strtolower($row['name']);
				$plan_names[$row['name']] = $id;
				$plan_costs[$row['name']] = $row['cost_per_month'];
				$plan->_data = $row;
				$plan->load($id);
				$available[$row['name']] = true;
				if($id==$current_plan->id) {
					continue;
				}
				foreach($usages as $limit=>$usagechecker) {
					$limitchecker = new LimitChecker($usagechecker, $plan);
					if(!$limitchecker->isWithinLimit($limit, 0)) {
						$available[$row['name']] = false;
						break;
					}
				}
			}
			$available['beta'] = false;
			
			$this->view->set('available', $available);
			$this->view->set('plans', $plan_names);
			$this->view->set('costs', $plan_costs);
		}
		
		$country = new Country();
		$this->view->set('country_list', $country->getAll());
	}
	
	/**
	 * Take the form values, and handle the changing of plans:
	 * - if switching down, check usage vs. limits of new plan
	 * - change current_plan_id in tactile_accounts
	 * - if switching from free to paid, take payment (TYPE_FULL) and extend expires to +30
	 */
	public function process_plan_change() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$old_plan = $account->getPlan();
		if ($old_plan->is_per_user() && !$old_plan->is_free()) {
			sendTo('account');
			return;
		}
		
		$new_plan = DataObject::Construct('AccountPlan');
		/* @var $new_plan AccountPlan */
		
		if ($old_plan->is_per_user()) {
			$db = DB::Instance();
			$sql = "SELECT id FROM account_plans WHERE cost_per_month != 0 AND per_user ORDER BY cost_per_month ASC";
			$id = $db->getOne($sql);
			if (false === $new_plan->load($id)) {
				// Terrible error
				Flash::Instance()->addError('Failed to load plan details. Please contact support.');
				sendTo('account');
				return;
			}
		} else {
			//check id is valid
			if (!isset($this->_data['plan']) || false === $new_plan->load($this->_data['plan'])) {
				Flash::Instance()->addError("Invalid ID specified");
				sendTo('account');
				return;
			}
		}
		
		//check they're actually changing plan
		if ($old_plan->id === $new_plan->id) {
			Flash::Instance()->addError("You're already on that plan");
			sendTo('account', 'change_plan');
			return;
		}
		
		// Check we're not jumping from per-user to non-per-user or vice-versa
		if ($old_plan->is_per_user() !== $new_plan->is_per_user()) {
			Flash::Instance()->addError("You are not allowed to switch to that plan");
			sendTo('account', 'change_plan');
			return;
		}
		
		if ($new_plan->is_per_user()) {
			if (empty($this->_data['quantity']) || ((int) $this->_data['quantity']) != $this->_data['quantity']) {
				Flash::Instance()->addError('You must provide a whole number quantity of at least 1');
				sendTo('account', 'change_plan');
				return;
			}
			if ($this->_data['quantity'] < 1 || $this->_data['quantity'] > 100) {
				Flash::Instance()->addError('User quantity must be a whole number between 1 and 100');
				sendTo('account', 'change_plan');
				return;
			}
			
		
			$users = new Omelette_UserCollection(new Omelette_User());
			$sh = new SearchHandler($users, false);
			$sh->extract();
			$sh->addConstraint(new Constraint('usercompanyid', '=', EGS::getCompanyId()));
			$sh->addConstraint(new Constraint('enabled','=',true));
			$users->load($sh);
			$current_users = count($users);
			
			if($this->_data['quantity'] < $current_users){
				Flash::Instance()->addError('User quantity must at least '.$current_users.' as you have this number of active users.');
				sendTo('account', 'change_plan');
				return;
			}
			$user_qty = (int) $this->_data['quantity'];
		}
		
		$this->logger->info("Changing plan for ".$account->id." from ".$old_plan->name." to ".$new_plan->name);
		
		
		
		//check they're within the limits of the new plan
		Autoloader::Instance()->addPath(APP_CLASS_ROOT.'usage/');
		
		if($new_plan->name=='Beta') {
			Flash::Instance()->addError("We're not currently accepting Beta signups");
			sendTo('account', 'change_plan');
			return;
		}
		
		$usages = array(
			'user_limit'=>new UserUsageChecker($account),
			'contact_limit'=>new ContactUsageChecker($account),
			'file_space'=>new FileUsageChecker($account),
			'opportunity_limit'=>new OpportunityUsageChecker($account)
		);
		
		$errors = array();
		
		foreach($usages as $limit=>$usagechecker) {
			$limitchecker = new LimitChecker($usagechecker, $new_plan);
			if(!$limitchecker->isWithinLimit($limit, 0)) {
				$errors[] = "The new plan has a ".prettify($limit)." of ".$new_plan->getFormatted($limit)." which is more than you're currently using";
			}
		}
		if(count($errors)>0) {
			Flash::Instance()->addErrors($errors);
			sendTo('account', 'change_plan');
			return;
		}
		Autoloader::Instance()->addPath(FILE_ROOT.'omelette/lib/payment/');
		if ($new_plan->is_free()) {
			//cancel any deferred payments
			try {
				$account->cancelOutstandingDeferred($this->logger);
			}
			catch(Exception $e) {
				$logger->crit($e->getMessage());
				$logger->crit($e->getTraceAsString());
				Flash::Instance()->addError("There was a problem communicating with the payment gateway, our technicians have been notified");
				$this->saveData();
				sendTo('account','change_plan');
				return;
			}
			//then change the plan_id
			$account->current_plan_id = $new_plan->id;
			if ($new_plan->is_per_user()) {
				$account->per_user_limit = $new_plan->user_limit;
			}
			$success = $account->save();
			if($success===false) {
				$logger->crit("Changing Plan failed for account: ".$account->id.". Changing to: ".$new_plan->id);
				Flash::Instance()->addError("There was a problem changing your plan, our technical staff are going to take a look and will be in touch");
				sendTo('account', 'change_plan');
				return;
			}
		}
		//so not free...
		
		//is there a deferred payment we can use or do we need the card details?
		$payment = $account->getLatestPayment();
		if ($new_plan->is_per_user() || $payment === false) {
			//then we need to make sure the form sent through some card details...
			$card_details = isset($this->_data['Card']) ? $this->_data['Card'] : array();
			if(!empty($this->_data['Card']['card_number'])) {
				$this->_data['Card']['card_number'] = '*';
			}
			if(!empty($this->_data['Card']['cv2'])) {
				$this->_data['Card']['cv2'] = '*';
			}
			$card = new PaymentCard($card_details);
			if(!$card->isValid()) {
				Flash::Instance()->addErrors($card->getErrors());
				$this->saveData();
				sendTo('account','change_plan');
				return;
			}			
			//only get here if account+card seem ok
			//then we build the request that will go to Secpay
			$request = new SecPayFull(SECPAY_MID,SECPAY_VPN_PASSWORD);
			
			$request->setLogger($this->logger);
			
			$trans_id = 'Tactile'.date('Ymdhis');
			$request->addPaymentCard($card);
			$request->setTransId($trans_id);
			$request->setCustomerIP(!empty($_SERVER['X_FORWARDED_FOR'])?$_SERVER['X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
			
			if ($new_plan->is_per_user()) {
				$amount_to_charge = $user_qty * $new_plan->cost_per_month;
			} else {
				$amount_to_charge = $new_plan->cost_per_month;
			}
			$request->setAmount($amount_to_charge);
			
			$request->setTest(SECPAY_TEST_STATUS);
			
			//this needs to be done after other things!!!
			$request->setDigest(SECPAY_REMOTE);
			//then make the request, we get back a SecPaySyncResponse()
			$response = $request->send();
			if($response===false) {
				Flash::Instance()->addError("There was a problem communicating with the payment gateway, our technicians have been notified");
				$this->saveData();
				sendTo('account','change_plan');
				return;
			}
			
			//checks that there are enough fields sent back
			if(!$response->isValid()) {
				Flash::Instance()->addErrors($response->getErrors());
				$this->saveData();
				sendTo('account','change_plan');
				return;
			}
			
			//then check that the response is 'valid' and with a good code, generates errors for user
			if(!$response->isSuccessful()) {
				Flash::Instance()->addErrors($response->getErrors());
				$this->saveData();
				sendTo('account','payment_details');
				return;
			}
			
			if ($new_plan->is_per_user()) {
				$description = "Changing plan from {$old_plan->name} to {$new_plan->name} with {$user_qty} Users";
			} else {
				$description = "Changing plan from {$old_plan->name} to {$new_plan->name}";
			}
			$record = PaymentRecord::saveFull($account, $response, $card, $errors, $description);
			if($record===false) {
				$this->logger->crit("Saving payment record failed for account ".$account->id.", changing to ".$new_plan->name.", trans_id=".$trans_id);
				$this->logger->crit(print_r($errors,true));
				Flash::Instance()->addError("There was a problem changing your plan, our technical staff are going to take a look and will be in touch");
				sendTo('account', 'change_plan');
				return;
			}
			
			//then change the plan_id
			$account->current_plan_id = $new_plan->id;
			if ($new_plan->is_per_user()) {
				$account->per_user_limit = $user_qty;
			}
			$success = $account->extend();	//extend performs save
			
			if($success===false) {
				$logger->crit("Changing Plan failed for account: ".$account->id.". Changing to: ".$new_plan->id);
				Flash::Instance()->addError("There was a problem changing your plan, our technical staff are going to take a look and will be in touch");
				sendTo('account', 'change_plan');
				return;
			}
			
		}
		else {
			//we've had one payment, so we can just change the plan_id and let the repeater take care of it
			$account->current_plan_id = $new_plan->id;
			if ($new_plan->is_per_user()) {
				$account->per_user_limit = $user_qty;
			}
			$success = $account->save();
			if($success===false) {
				$logger->crit("Changing Plan failed for account: ".$account->id.". Changing to: ".$new_plan->id);
				Flash::Instance()->addError("There was a problem changing your plan, our technical staff are going to take a look and will be in touch");
				sendTo('account', 'change_plan');
				return;
			}
		}
		
		// Done the change, now do CM stuff
		if(PRODUCTION){
			try {
				$account = CurrentlyLoggedInUser::Instance()->getAccount();
				$account_plan = new AccountPlan();
				$account_plan->load($account->current_plan_id);
	
				$client = @new SoapClient("http://api.createsend.com/api/api.asmx?wsdl");
				$response = $client->AddSubscriberWithCustomFields(
						array(
								'ApiKey' => OMELETTES_CM_API_KEY,
								'ListID' => OMELETTES_CM_LIST_ID,
								'Email' => $account->email,
								'Name' => trim(ucwords($account->firstname . ' ' . $account->surname)),
								'CustomFields' => array(
										array(
										   'Key' => 'site_address',
										   'Value' => $account->site_address
										),
										array(
												'Key' => 'username',
												'Value' => $account->username
										),
										array(
												'Key' => 'plan_name',
												'Value' => strtolower($account_plan->name)
										)
								)
						 )
				);
			} catch (Exception $e) {
				require_once 'Zend/Log.php';
				$logger = new Zend_Log(new Log_Writer_Mail(NOTIFICATIONS_TO, 'Campaign monitor cancellation subscription problem'));
				$logger->crit($e->getMessage());
				$logger->crit($e->getTraceAsString());
			}
		}
		Omelette::setAccountPlan($new_plan);
		$this->logger->info('Change succeeded');
		Flash::Instance()->addMessage("Account changed successfully");
		sendTo('account');
	}
	
	/**
	 * Just for the display of text + form
	 *
	 */
	function cancel() {
		$user = CurrentlyLoggedInUser::Instance();
		$google_apps_domain = $user->getAccount()->google_apps_domain;
		$google_apps_email = $user->getModel()->google_apps_email;
		if (!empty($google_apps_domain) && !empty($google_apps_email)) {
			// Confirm with email address instead of password
			$this->view->set('google_apps_domain', $user->getAccount()->google_apps_domain);
			$this->view->Set('google_apps_email', $user->getModel()->google_apps_email);
		}
	}
	
	function process_cancel() {
		Autoloader::Instance()->addPath(FILE_ROOT.'omelette/lib/payment/');
		
		$db = DB::Instance();
		if(!isset($this->_data['confirm'])) {
			Flash::Instance()->addError("Please tick the box to confirm if you wish to cancel");
			sendTo('account', 'cancel');
			return;
		}
		
		$user = CurrentlyLoggedInUser::Instance();
		$password = $user->getModel()->password;
		$google_apps_domain = $user->getAccount()->google_apps_domain;
		$google_apps_email = $user->getModel()->google_apps_email;
		
		if (!empty($google_apps_domain) && !empty($google_apps_email)) {
			if (empty($this->_data['password']) || $google_apps_email !== $this->_data['password']) {
				$this->logger->info("Cancellation attempt failed for ".EGS::getUsername()." with wrong email address: ".$this->_data['password']);
				Flash::Instance()->addError("The email address you entered is incorrect");
				sendTo('account', 'cancel');
				return;
			}
		} else {
			if (empty($this->_data['password']) || $password!==md5($this->_data['password'])) {
				$this->logger->info("Cancellation attempt failed for ".EGS::getUsername()." with wrong password: ".$this->_data['password']);
				Flash::Instance()->addError("The password you entered is incorrect");
				sendTo('account', 'cancel');
				return;
			}
		}
		
		$account_plan = new AccountPlan();
		$account_plan->load($user->getAccount()->current_plan_id);
			
		// We update Tactile CRM before sending the email so that the persons details etc. are present
		try {
			require_once 'Tactile/Api.php';
			require_once 'Tactile/Api/Organisation.php';
			if (defined('PRODUCTION') && PRODUCTION == true) {
				$client = new Tactile_Api(TACTILE_API_DOMAIN, TACTILE_API_KEY);
			} else {
				$client = new Tactile_Api(TACTILE_API_DOMAIN, TACTILE_API_KEY, null, TACTILE_API_TEST_DOMAIN);
			}

			$org = $client->getOrganisations(array('accountnumber' => $user->getAccount()->site_address));
			
			$org_id = null;
			
			if(($org->status == "success") && ($org->total == 1)) {
				// We have found the organisation in Tactile CRM
				$org_id = $org->organisations[0]->id;
				$update_org = new Tactile_Api_Organisation();
				$update_org->status_id = 13705;
				
				// We need to loop over current details to push them up to Tactile
				foreach($org->organisations[0] as $key => $value) {
					if(!empty($value)) {
						$update_org->$key = $value;
					}
				}
				//echo '<pre>';var_dump($update_org);die();
				$client->saveOrganisation($update_org);
			} else {
				// Add a new organisation
				$org = new Tactile_Api_Organisation();
				$org->name = $user->getAccount()->company;
				$org->accountnumber = $user->getAccount()->site_address;
				$org->country_code = $user->getAccount()->country_code;
				$org->status_id = 13705;

				$new_org = $client->saveOrganisation($org);

				if($new_org->status == "success") {
					$org_id = $new_org->id;
				}
			}
			
			if(!empty($org_id)) {
				// Nothing has gone wrong getting the org so we'll set the status to cancelled
				require_once 'Tactile/Api/Person.php';
				require_once 'Tactile/Api/Note.php';
				$person = $client->getPeople(array('email' => $user->getAccount()->email, 'organisation_id' => $org_id));
				
				$person_id = null;
				
				if(($person->status == "success") && ($person->total == 1)) {
					// We have found the Person in Tactile CRM
					$person_id = $person->people[0]->id;
				} else {
					// Add a new person
					$person = new Tactile_Api_Person();
					$person->firstname = $user->getAccount()->firstname;
					$person->surname = $user->getAccount()->surname;
					$person->email = $user->getAccount()->email;
					$person->organisation_id = $org_id;

					$new_person = $client->savePerson($person);
					if($new_person->status == "success") {
						$person_id = $new_person->id;
					}
				}
				
				// Now we just need to add a note
				$note = new Tactile_Api_Note();
				$note->title = 'Account Cancelled ('.$account_plan->name;
				$note->note = $user->getAccount()->firstname.' cancelled '.$user->getAccount()->company.'\'s account on the '.$account_plan->name.' plan';
				if($account_plan->per_user && ($user->getAccount()->per_user_limit > 1)) {
					$note->note .= '. They had '.$user->getAccount()->per_user_limit.' users';
					$note->title .= ' - '.$user->getAccount()->per_user_limit.' Users';
				} else if($account_plan->per_user) {
					$note->title .= ' - Single User';
				}
				
				$note->note .= '.';
				$note->title .= ')';
				$note->organisation_id = $org_id;
				$note->person_id = $person_id;
				
				$new_note = $client->saveNote($note);
			}

		} catch (Exception $e) {
			  require_once 'Zend/Log.php';
			  $logger = new Zend_Log(new Log_Writer_Mail(NOTIFICATIONS_TO, 'Error updating our Tactile CRM via the API'));
			  $logger->crit($e->getMessage());
			  $logger->crit($e->getTraceAsString());
		}
		
		try {
			$client = @new SoapClient("http://api.createsend.com/api/api.asmx?wsdl");
			$response = $client->AddSubscriberWithCustomFields(
				array(
					'ApiKey' => OMELETTES_CM_API_KEY,
					'ListID' => OMELETTES_CM_LIST_ID,
					'Email' => $user->getAccount()->email,
					'Name' => trim(ucwords($user->getAccount()->firstname . ' ' . $user->getAccount()->surname)),
					'CustomFields' => array(
						array(
						   'Key' => 'site_address',
						   'Value' => $user->getAccount()->site_address
						),  
						array(
							'Key' => 'username',
							'Value' => $user->getAccount()->username
						),  
						array(
							'Key' => 'plan_name',
							'Value' => strtolower($account_plan->name)
						),
						array(
							'Key' => 'cancelled',
							'Value' => date('Ymd')
						)
					)
				 )
			);
		} catch (Exception $e) {
			  require_once 'Zend/Log.php';
			  $logger = new Zend_Log(new Log_Writer_Mail(NOTIFICATIONS_TO, 'Campaign monitor cancellation subscription problem'));
			  $logger->crit($e->getMessage());
			  $logger->crit($e->getTraceAsString());
		}

		// We also want to remove them from the free trial reminder emails
		try {
			$client = @new SoapClient("http://api.createsend.com/api/api.asmx?wsdl");
			
			$response = $client->Unsubscribe(
				array(
					'ApiKey' => OMELETTES_CM_API_KEY,
					'ListID' => '5230d0f588eda4a723fc81418f421919',
					'Email' => $user->getAccount()->email
				)
			);
		} catch (Exception $e) {
			require_once 'Zend/Log.php';
			$logger = new Zend_Log(new Log_Writer_Mail(DEBUG_EMAIL_ADDRESS, 'Campaign monitor removing from free trial reminder'));
			$logger->crit($e->getMessage());
			$logger->crit($e->getTraceAsString());
		}
		
		// We also want to remove them from the paid trial reminder emails
		try {
			$client = @new SoapClient("http://api.createsend.com/api/api.asmx?wsdl");
			
			$response = $client->Unsubscribe(
				array(
					'ApiKey' => OMELETTES_CM_API_KEY,
					'ListID' => '73634f379b469c71e8e31bf78104c777',
					'Email' => $user->getAccount()->email
				)
			);
		} catch (Exception $e) {
			require_once 'Zend/Log.php';
			$logger = new Zend_Log(new Log_Writer_Mail(DEBUG_EMAIL_ADDRESS, 'Campaign monitor removing from paid trial reminder'));
			$logger->crit($e->getMessage());
			$logger->crit($e->getTraceAsString());
		}
		
		$mail = new Omelette_Mail('account_cancelled');
		$mail->getMail()->setSubject("Sorry to see you're leaving, I'd appreciate some feedback if you have time.");
		$mail->getMail()->setFrom(TACTILE_EMAIL_ADDRESS, TACTILE_EMAIL_SENDER);

		if (defined('PRODUCTION') && PRODUCTION == true) {
			$mail->getMail()->addTo($user->getAccount()->email);
		} else {
			$mail->getMail()->addTo(DEBUG_EMAIL_ADDRESS);
		}
	
		if (defined('PRODUCTION') && PRODUCTION == true) {
			$mail->addBcc(TACTILE_DROPBOX);
		}
		
		$mail->getView()->set('firstname', ucwords($user->getAccount()->firstname));
		
		$mail->send();
		
		$db->StartTrans();
		$account = $user->getAccount();
		$this->logger->info("Cancellation of account {$account->site_address} by ".EGS::getUsername().", confirm={$this->_data['confirm']}");
		
		$account->cancelOutstandingDeferred($this->logger);
		$account->cancel();
		$this->logger->info("Account suspended and disabled");
		
		RememberedUser::destroyAllMemories($user->getRawUsername());
		$this->logger->info("Removed rembember-me entries");
		
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		
		Flash::Instance()->addMessage("Your account has been cancelled");
		$db->CompleteTrans();
		header('Location: http://www.tactilecrm.com/cancelled/');
		exit;
		
	}
	
	public function change_owner() {
		$db = DB::Instance();
		$users = $db->getCol(
			"SELECT u.username FROM users u
			LEFT JOIN people p ON p.id = u.person_id
			WHERE u.enabled IS TRUE
			AND u.is_admin IS TRUE
			AND u.username != " . $db->qstr(EGS::getUsername()) . "
			AND p.usercompanyid = " . $db->qstr(EGS::getCompanyId())
		);
		
		$user_list = array();
		foreach ($users AS $user) {
			$x = explode('//', $user);
			
			$user_list[$user] = $x[0];
		}
		
		if(empty($user_list)) {
			Flash::Instance()->addError("Only admin users can become the new account owner but no admin users are available.");
			sendTo('account', 'index');
			return;
		}
		
		$this->view->set('users', $user_list);
	}
	
	public function process_change_owner() {
		if(!isset($this->_data['confirm'])) {
			Flash::Instance()->addError("Please tick the box to confirm if you wish to change account owner");
			sendTo('account', 'change_owner');
			return;
		}
		
		$db = DB::Instance();

		// Check the specified user is an admin belonging to this account
		$check_user = $db->getOne(
			"SELECT u.username FROM users u
			LEFT JOIN people p ON p.id = u.person_id
			WHERE u.enabled IS TRUE
			AND u.is_admin IS TRUE
			AND u.username = " . $db->qstr($this->_data['user']) . "
			AND p.usercompanyid = " . $db->qstr(EGS::getCompanyId())
		);
		if (empty($check_user) || $check_user != $this->_data['user']) {
			Flash::Instance()->addError("Invalid user");
			sendTo('account', 'change_owner');
			return;
		}
		
		$user_parts = explode('//', $this->_data['user']);
		
		$db->StartTrans();
		
		// Grab current account details
		$tactile_account = $db->getRow(
			"SELECT * FROM tactile_accounts
			WHERE id = " . $db->qstr(CurrentlyLoggedInUser::Instance()->getAccount()->id)
		);
		$old_address = $tactile_account['email'];
		
		// Grab new details from the User/Person
		$new_details = $db->getRow(
			"SELECT
				p.firstname,
				p.surname,
				pcm.contact AS email
			FROM people p
			LEFT JOIN users u ON u.person_id = p.id
			LEFT JOIN person_contact_methods pcm ON pcm.person_id = p.id
			AND pcm.main = 't'
			AND pcm.type = 'E'
			WHERE u.username = " . $db->qstr($this->_data['user'])
		);
		
		// Process the change
		$amended_account_details = array(
			'id'		=> CurrentlyLoggedInUser::Instance()->getAccount()->id,
			'username'	=> $user_parts[0],
			'firstname'	=> $new_details['firstname'],
			'surname'	=> $new_details['surname'],
			'email'		=> $new_details['email']
		);
		$db->replace('tactile_accounts', $amended_account_details, 'id', true);
		
		unset($_SESSION['is_owner']);
		
		// Re-grab the new details from the account
		$new_details = $db->getRow(
			"SELECT 
				ta.firstname,
				ta.surname,
				ta.email,
				ta.username,
				ta.site_address,
				ap.name AS plan_name
				FROM tactile_accounts ta
				LEFT JOIN account_plans ap ON ap.id = ta.current_plan_id
				WHERE ta.id = {$db->qstr($tactile_account['id'])}"
		);
		
		// Change the email subscription details we have on record with Campaign Monitor 
		if (defined('PRODUCTION') && PRODUCTION) {
			try {
				$client = @new SoapClient("http://api.createsend.com/api/api.asmx?wsdl");
				
				if ($old_address !== $new_details['email']) {
					$response = $client->Unsubscribe(
						array(
							'ApiKey' => OMELETTES_CM_API_KEY,
							'ListID' => OMELETTES_CM_LIST_ID,
							'Email' => $old_address
						)
					);
				}
				
				$response = $client->AddSubscriberWithCustomFields(
					array(
						'ApiKey' => OMELETTES_CM_API_KEY,
						'ListID' => OMELETTES_CM_LIST_ID,
						'Email' => $new_details['email'],
						'Name' => trim(ucwords($new_details['firstname'] . ' ' . $new_details['surname'])),
						'CustomFields' => array(
							array(
								'Key' => 'site_address',
								'Value' => $new_details['site_address']
							),
							array(
								'Key' => 'username',
								'Value' => $new_details['username']
							),
							array(
								'Key' => 'plan_name',
								'Value' => strtolower($new_details['plan_name'])
							)
						)
					)
				);
			} catch (Exception $e) {
				require_once 'Zend/Log.php';
				$logger = new Zend_Log(new Log_Writer_Mail(DEBUG_EMAIL_ADDRESS, 'Campaign monitor cancellation subscription problem'));
				$logger->crit($e->getMessage());
				$logger->crit($e->getTraceAsString());
			}
		}
		
		Flash::Instance()->addMessage("The account owner has been changed");
		$db->CompleteTrans();
		sendTo('admin','index');
	}
}
