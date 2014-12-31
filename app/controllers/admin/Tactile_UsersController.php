<?php

require_once 'Zend/Mail.php';

class Tactile_UsersController extends Controller {
	
	/**
	 * The 'used' user
	 *
	 * @var Omelette_User
	 */
	protected $user;

	public function __construct($module, $view = null) {
		parent::__construct($module, $view);
		$this->uses('User');
	}

	/**
	 * When creating a user, we create a person too, and give them some roles
	 *
	 */
	function _new() {
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		$plan = $account->getPlan();
		$this->view->set('plan', $plan);
		$checker = new UserUsageChecker($account);
		$this->view->set('users_used', $checker->calculateUsage());
		if ($plan->is_per_user()) {
			$this->view->set('users_limit', $account->per_user_limit);
		} else {
			$this->view->set('users_limit', $plan->user_limit);
		}
		if(!isset($this->_data['username'])) {
			// We are creating a new User
			AutoLoader::Instance()->addPath(APP_CLASS_ROOT.'usage/');
			
			if ($plan->is_per_user()) {
				$limit_checker = new LimitChecker(
					new UserUsageChecker($account), 
					$account);
				$criteria = 'per_user_limit';
			} else {
				$limit_checker = new LimitChecker(
					new UserUsageChecker($account), 
					$account->getPlan());
				$criteria = 'user_limit';
			}
			if(false === $limit_checker->isWithinLimit($criteria)) {
				if ($plan->is_per_user()) {
					if($user->isAccountOwner()) {
						if ($plan->is_free()) {
							Flash::Instance()->addError("You have as many Users as your account is allowed. To add more you need to upgrade your Plan.");
							sendTo('account', 'change_plan');
						} else {
							Flash::Instance()->addError("You have as many Users as your account is allowed. To add more you need to purchase more Users.");
							sendTo('users', 'purchase');
						}
						return;
					} else {
						Flash::Instance()->addError("You have as many users as your account is allowed. To add more your Account Owner will need to purchase more Users.");
						sendTo('users');
						return;
					}
				} else {
					if($user->isAccountOwner()) {
						Flash::Instance()->addError("You have as many users as your account is allowed. To add more you need to upgrade.");
						sendTo('account/change_plan');
						return;
					} else {
						Flash::Instance()->addError("You have as many users as your account is allowed. To add more your Account Owner will need to upgrade.");
						sendTo('users');
						return;
					}
				}
			}
		}
		parent::_new();
		$this->uses('Person');
		$role_model = DataObject::Construct('Role');
		$this->view->set('roles', $role_model->getAll());
		
		$this->view->set('google_domain', $account->google_apps_domain);
	}

	/**
	 * No filtering options for users yet, just the plain list
	 *
	 */
	public function index() {
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		$plan = $account->getPlan();
		$this->view->set('plan', $plan);
		$checker = new UserUsageChecker($account);
		$this->view->set('users_used', $checker->calculateUsage());
		if ($plan->is_per_user()) {
			$this->view->set('users_limit', $account->per_user_limit);
		} else {
			$this->view->set('users_limit', $plan->user_limit);
		}
		
		$users = new Omelette_UserCollection($this->user);
		$sh = new SearchHandler($users, false);
		$sh->extract();
		$sh->addConstraint(new Constraint('usercompanyid', '=', EGS::getCompanyId()));
		$sh->setOrderby(array('enabled', 'username'), array('desc', 'asc'));
		Controller::index($users, $sh);
		
		/*require_once 'OAuth.php';
		$oauth = new OAuthConsumer(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_KEY_SECRET, NULL);
		$google_user = $user->getModel()->google_apps_email;
		$feed_url = 'http://feedserver-enterprise.googleusercontent.com/licensenotification?bq=[appid='.GOOGLE_APPS_APPLICATION_ID.']';
		*/
	}

	/**
	 * Editing a user could involve Person edits too
	 *
	 */
	function edit() {
		if(!isset($this->_data['username']) || false === $this->user->load($this->_data['username'])) {
			Flash::Instance()->addError('Invalid username specified');
			sendTo();
			return;
		}
		parent::edit();
		$this->uses($p = DataObject::Construct('Person'));
		$p->load($this->user->person_id);
		
		$hr = DataObject::Construct('HasRole');
		$cc = new ConstraintChain();
		$cc->add(new Constraint('username','=',$this->user->getRawUsername()));
		$selected_roles = $hr->getAll($cc);
		$this->view->set('selected_roles',$selected_roles);
	}

	public function view() {
		if(!isset($this->_data['username']) || false === $this->user->load($this->_data['username'])) {
			Flash::Instance()->addError('Invalid username specified');
			sendTo();
			return;
		}
		
		$fields = array(
			'person',
			'enabled',
			'last_login',
			'is_admin'
		);
		$summary_groups = array($fields);
		$this->view->set('summary_groups', $summary_groups);
		$view_summary_info = Omelette_Magic::getAsBoolean('view_summary_info', EGS::getUsername(), 't', 't');
		$this->view->set('view_summary_info', $view_summary_info);
		
		$this->view->set('head_title', $this->user->getFormatted('username'));
		$this->uses($p = DataObject::Construct('Person'));
		$p->load($this->user->person_id);
	}

	/**
	 * Saving a user can involve creating a person, as well as editing/adding roles.
	 * New users need to be sent an email and have a password generated, existing ones don't!
	 *
	 */
	function save() {
		$db = DB::Instance();
		$db->StartTrans();
		$errors = array();
		
		//check for data
		$user_data = isset($this->_data['User']) ? $this->_data['User'] : array();
		$person_data = isset($this->_data['Person']) ? $this->_data['Person'] : array();
		
		if (!empty($user_data['username'])) {
			// Fix common issues
			$user_data['username'] = trim($user_data['username']);
			
			//see if we're editing...
			$editing = $this->user->load($user_data ['username']);

			if ($editing!==false && (!isset($user_data['person_id']) || $user_data['person_id']!==$editing->person_id)) {
				Flash::Instance()->addError("That username has been taken, choose another");
				$db->FailTrans();
				$db->CompleteTrans();
				sendTo('users', 'new', 'admin');
				return;
			}
			$editing = !!$editing;
		}
		else {
			$editing = false;
		}
		
		//first make the person
		$person = DataObject::Factory($person_data, $errors, 'Person');
		if($person !== false && false !== $person->save()) {
			
			//then connect to the user (person not saved yet...)
			$user_data['person_id'] = $person->id;

			//generate keys only for new users
			if(!$editing) {
				$user_data['dropboxkey'] = Omelette_User::generateDropBoxKey();
				$user_data['webkey'] = Omelette_User::generateWebKey();
				$user_data['api_token'] = Omelette_User::generateApiToken();
			}
			if(!empty($user_data['username'])) {
				$user_data['username'] .='//'.Omelette::getUserSpace();
			}
			
			
			// Google Apps stuff
			$account = CurrentlyLoggedInUser::Instance()->getAccount();
			$google_domain = $account->google_apps_domain;
			if (!empty($google_domain) && empty($user_data['username'])) {
				$email_address = trim($person_data['email']['contact']);
				if (!preg_match('/^(.+)@(.+)$/', $email_address, $matches)) {
					Flash::Instance()->addError("Please enter a valid email address");
					$db->FailTrans();
					$db->CompleteTrans();
					sendTo('users', 'new', 'admin');
					return;
				}
				$username = $matches[1];
				$domain = $matches[2];
				if ($domain !== $google_domain) {
					Flash::Instance()->addError("Please enter a " . $google_domain . " email address");
					$db->FailTrans();
					$db->CompleteTrans();
					sendTo('users', 'new', 'admin');
					return;
				}
				$user_data['username'] = $username;
				$user_data['google_apps_email'] = $email_address;
			}
			
			
			
			//then validate the user
			$user = DataObject::Factory($user_data, $errors, $this->user);
			
			//then make the email address record
			$email_data = $person_data['email'];
			if(!is_array($email_data)) {
				debug_print_backtrace();exit;
			}
			$email_data += array(
				'name'=>'Main', 
				'person_id'=>$person->id,
				'main'=>true,
				'type'=>'E'
			);
			$email = DataObject::Factory($email_data, $errors, 'Personcontactmethod');
			if($user !== false && $email !== false) {
				//then save them all...
				$success = $user->save() && $email->save();
				if($success) {
					if(!isset($this->_data['role_ids']) || !is_array($this->_data['role_ids'])) {
						$this->_data['role_ids'] = array();
					}
					$role_ids = $this->_data['role_ids'];
					$roles_saved = Omelette_User::setRoles($user, $role_ids);
					if($roles_saved === false) {
						$errors['role_ids'] = "Couldn't Save Roles";
					}
					else {
						if(!$editing) {
							
							$mail = new Omelette_Mail('account_details');
							$mail->getView()->set('Person', $person);
							$mail->getView()->set('username', $user->username);
							$mail->getView()->set('password', $user->getRawPassword());
							$mail->getView()->set('user_space', Omelette::getUserSpace());
							$mail->getView()->set('login_url', 'http://' . Omelette::getUserSpace() . '.tactilecrm.com');
							$mail->getMail()->addTo($email->contact)
								->setSubject('Tactile CRM: Your Account Details')
								->setFrom(TACTILE_EMAIL_FROM, TACTILE_EMAIL_NAME);
							$mail->send();
						}
						$db->CompleteTrans();
						sendTo('users', 'view', 'admin', array('username'=>$user->username));
						return;
					}
				}
			}
		}
		Flash::Instance()->addErrors($errors);
		$db->FailTrans();
		$db->CompleteTrans();
		if($editing) {
			sendTo('users', 'edit', 'admin', array('username'=>$user_data['username']));
			return;
		} else {
			sendTo('users', 'new', 'admin');
		}
	}

	protected function _calculateProRataDays() {
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		
		// How many days until this account expires?
		$expires = $account->account_expires;
		$expires_in_days = abs(ceil((strtotime($expires) - time()) / 86400));
		if ($expires_in_days == 0) {
			$expires_in_days = 1;
		}
		
		return $expires_in_days;
	}
	
	protected function _calculateProRataCost() {
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		$plan = $account->getPlan();
		
		$expires_in_days = $this->_calculateProRataDays();
		
		// How many 3-day (one-tenth of 30 days) periods is that? (round up)
		$tenths = ceil($expires_in_days / 3);
		if ($tenths > 10) {
			$tenths = 10; // Shouldn't happen, but don't ever over-charge
		}
		$pro_rata = round($plan->cost_per_month / 10 * $tenths, 2);
		if ($pro_rata < 1) {
			$pro_rata = 1; // SECPay min of 1 currency unit 
		}
		
		$this->view->set('billing_date', $account->account_expires);
		$this->view->set('days_to_next_bill', $expires_in_days);
		$this->view->set('pro_rata_days', $tenths * 3);
		$this->view->set('pro_rata_cost', $pro_rata);
		
		return $pro_rata;
	}
	
	public function purchase() {
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		$plan = $account->getPlan();
		if (!$plan->is_per_user()) {
			sendTo('account');
			return;
		}
		if ($plan->is_free()) {
			Flash::Instance()->addError("To increase your User limit you will need to upgrade your Plan.");
			sendTo('account', 'change_plan');
			return;
		}
		if (empty($_SERVER['HTTP_X_FARM'])) {
			$_SERVER['HTTP_X_FARM'] = 'HTTPS';
			sendTo('users', 'purchase');
			return;
		}
		
		$pro_rata = $this->_calculateProRataCost();
		
		$checker = new UserUsageChecker($account);
		$this->view->set('users_used', $checker->calculateUsage());
		$this->view->set('users_limit', $account->per_user_limit);
		$this->view->set('currency', '£');
		$this->view->set('cpupm', $plan->cost_per_month);
		
		$db = DB::Instance();
		$query = 'SELECT code, name FROM countries ORDER BY name';
		$country_list = $db->GetAssoc($query);
		$this->view->set('country_list', $country_list);
	}
	
	public function process_purchase() {
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		$plan = $account->getPlan();
		if (!$plan->is_per_user() || $plan->is_free()) {
			sendTo('account');
			return;
		}
		if (empty($this->_data['confirm'])) {
			Flash::Instance()->addError('Please tick the confirmation box');
			sendTo('users', 'purchase');
			return;
		}
		if (empty($this->_data['quantity']) || (((int) $this->_data['quantity']) != $this->_data['quantity'])) {
			Flash::Instance()->addError('You must provide a whole number quantity of at least 1');
			sendTo('users', 'purchase');
			return;
		}
		if ($this->_data['quantity'] < 1 || $this->_data['quantity'] > 100) {
			Flash::Instance()->addError('User quantity must be a whole number between 1 and 100');
			sendTo('users', 'purchase');
			return;
		}
		$qty = (int) $this->_data['quantity'];
	
		$bannedNames = array(
                        'chely kim',
                );

                if(in_array(strtolower(trim($this->_data['Card']['cardholder_name'])), $bannedNames)) {
                        Flash::Instance()->addError("There was a problem with the card details you entered");
                        sendTo('users','purchase');
                        return;
                }
	
		$expires = $account->account_expires;
		$pro_rata = $this->_calculateProRataCost();
		$amount_to_charge = $qty * $pro_rata;
		$amount_to_defer = ($account->per_user_limit + $qty) * $plan->cost_per_month;
		
		$logger = $this->logger;
		$logger->info("Adding {$qty} Users @ {$pro_rata} to {$account->site_address} (Expires: {$expires})");
		
		$card_details = isset($this->_data['Card']) ? $this->_data['Card'] : array();
		if (!empty($this->_data['Card']['card_number'])) {
			$this->_data['Card']['card_number'] = '*';
		}
		if (!empty($this->_data['Card']['cv2'])) {
			$this->_data['Card']['cv2'] = '*';
		}
		$card = new PaymentCard($card_details);
		if (!$card->isValid()) {
			Flash::Instance()->addErrors($card->getErrors());
			sendTo('users', 'purchase');
			return;
		}

		$db = DB::Instance();
		$db->StartTrans();
		
		// Request full payment now for the pro-rata amount
		$request = new SecPayFull(SECPAY_MID,SECPAY_VPN_PASSWORD);
		$request->setLogger($logger);
		$trans_id = 'Tactile'.date('Ymdhis') . 'f';
		$request->addPaymentCard($card);
		$request->setTransId($trans_id);
		$request->setCustomerIP(!empty($_SERVER['X_FORWARDED_FOR'])?$_SERVER['X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
		$request->setAmount($amount_to_charge);
		$request->setTest(SECPAY_TEST_STATUS);
		$request->setDigest(SECPAY_REMOTE);
		
		$response = $request->send();
		if ($response === false) {
			$logger->crit("Request Failed horribly for trans_id ".$trans_id);
			$db->FailTrans();
			$db->CompleteTrans();
			Flash::Instance()->addError("There was a problem communicating with the payment gateway, our technicians have been notified");
			sendTo('users', 'purchase');
			return;
		}
		if (!$response->isValid()) {
			$logger->info("Response was invalid for trans_id".$trans_id);
			$logger->info(print_r($response->getErrors(),true));
			$logger->info($request->getRawResponse()->saveXML());
			$logger->info($request->getRawRequest()->saveXML());
			$db->FailTrans();
			$db->CompleteTrans();
			Flash::Instance()->addErrors($response->getErrors());
			sendTo('users', 'purchase');
			return;
		}
		if (!$response->isSuccessful()) {
			$logger->info("Response was unsuccessful for trans_id".$trans_id);
			$logger->info(print_r($response->getErrors(),true));
			$logger->info($request->getRawResponse()->saveXML());
			$logger->info($request->getRawRequest()->saveXML());
			$db->FailTrans();
			$db->CompleteTrans();
			Flash::Instance()->addErrors($response->getErrors());
			sendTo('users', 'purchase');
			return;
		}
		
		// Payment has been successfully taken, from this point on do not roll back
		
		try {
			$errors = array();
			$record_data = array(
				'account_id'		=> $account->id,
				'amount'			=> $amount_to_charge,
				'pre_authed'		=> true,
				'auth_code'			=> $response->getAuthCode(),
				'test_status'		=> $response->getTestStatus(),
				'card_no'			=> substr($card->getCardNumber(),-5),
				'card_expiry'		=> $card->getExpiry(),
				'cardholder_name'	=> $card->getCardholderName(),
				'trans_id'			=> $trans_id,
				'type'				=> PaymentRecord::TYPE_FULL,
				'description'		=> "{$qty} Users @ {$pro_rata} ({$this->_calculateProRataDays()} days)",
				'repeatable'		=> 'f'
			);
			$record = DataObject::Factory($record_data, $errors, 'PaymentRecord');
			if ($record === false || $record->save() === false) {
				$logger->crit("Saving payment record failed: ".print_r($errors,true));
				$logger->crit("Record data: ".print_r($record_data, true));
				//$db->FailTrans();
				//$db->CompleteTrans();
			}
		} catch (Exception $e) {
			$logger->crit($e->getMessage());
			$logger->crit($e->getTraceAsString());
		}
		
		
		// Cancel outstanding deferred
		try {
			$cancel_success = $account->cancelOutstandingDeferred($this->logger);
		} catch (Exception $e) {
			$logger->crit($e->getMessage());
			$logger->crit($e->getTraceAsString());
			/*$db->FailTrans();
			$db->CompleteTrans();
			Flash::Instance()->addError("There was a problem communicating with the payment gateway, our technicians have been notified");
			sendTo('users', 'purchase');
			return;*/
		}

		try {
			if (FALSE !== $cancel_success) {
				// Request new deferred for full amount
				$request = new SecPayFull(SECPAY_MID,SECPAY_VPN_PASSWORD);
				$request->setLogger($logger);
				$trans_id = 'Tactile'.date('Ymdhis') . 'd';
				$request->setDeferred(SecPayRequest::DEFERRED_FULL);
				$request->addPaymentCard($card);
				$request->setTransId($trans_id);
				$request->setCustomerIP(!empty($_SERVER['X_FORWARDED_FOR'])?$_SERVER['X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
				$request->setAmount($amount_to_defer);
				$request->setTest(SECPAY_TEST_STATUS);
				$request->setDigest(SECPAY_REMOTE);
				
				$response = $request->send();
				if ($response === false) {
					$logger->crit("Request Failed horribly for trans_id ".$trans_id);
					/*$db->FailTrans();
					$db->CompleteTrans();
					Flash::Instance()->addError("There was a problem communicating with the payment gateway, our technicians have been notified");
					sendTo('users', 'purchase');
					return;*/
				}
				if (!$response->isValid()) {
					$logger->crit("Response was invalid for trans_id".$trans_id);
					$logger->crit(print_r($response->getErrors(),true));
					$logger->crit($request->getRawResponse()->saveXML());
					$logger->crit($request->getRawRequest()->saveXML());
					/*$db->FailTrans();
					$db->CompleteTrans();
					Flash::Instance()->addErrors($response->getErrors());
					sendTo('users', 'purchase');
					return;*/
				}
				if (!$response->isSuccessful()) {
					$logger->crit("Response was unsuccessful for trans_id".$trans_id);
					$logger->crit(print_r($response->getErrors(),true));
					$logger->crit($request->getRawResponse()->saveXML());
					$logger->crit($request->getRawRequest()->saveXML());
					/*$db->FailTrans();
					$db->CompleteTrans();
					Flash::Instance()->addErrors($response->getErrors());
					sendTo('users', 'purchase');
					return;*/
				}
				
				$errors = array();
				$record_data = array(
					'account_id'		=> $account->id,
					'amount'			=> $amount_to_defer,
					'pre_authed'		=> true,
					'auth_code'			=> $response->getAuthCode(),
					'test_status'		=> $response->getTestStatus(),
					'card_no'			=> substr($card->getCardNumber(),-5),
					'card_expiry'		=> $card->getExpiry(),
					'cardholder_name'	=> $card->getCardholderName(),
					'trans_id'			=> $trans_id,
					'type'				=> PaymentRecord::TYPE_DEFERRED,
					'description'		=> "{$qty} Users @ {$plan->cost_per_month} (30 Days)"
				);
				$record = DataObject::Factory($record_data, $errors, 'PaymentRecord');
				if ($record === false || $record->save() === false) {
					$logger->crit("Saving payment record failed: ".print_r($errors,true));
					$logger->crit("Record data: ".print_r($record_data, true));
					//$db->FailTrans();
					//$db->CompleteTrans();
				}
			}
		} catch (Exception $e) {
			$logger->crit($e->getMessage());
			$logger->crit($e->getTraceAsString());
		}
		
		// Everything is peachy
		$account->per_user_limit = $account->per_user_limit + $qty;
		if (false === $account->save()) {
			$logger->crit("Saving account failed!");
			$db->FailTrans();
			$db->CompleteTrans();
			Flash::Instance()->addError("There was a problem increasing your User limit, our technical staff are going to take a look and will be in touch as soon as possible.");
			sendTo('users', 'purchase');
			return;
		}
		
		// Create opportunity in out Tactile account
		// This has been moved to the invoicing script
		if(false){
			try {
				require_once 'Tactile/Api.php';
				require_once 'Tactile/Api/Organisation.php';
				$client = new Tactile_Api(TACTILE_API_DOMAIN, TACTILE_API_KEY);
				$org = $client->getOrganisations(array('accountnumber' => $user->getAccount()->site_address));
				
				$org_id = null;
				
				if(($org->status == "success") && ($org->total == 1)) {
					// We have found the organisation in Tactile CRM
					$org_id = $org->organisations[0]->id;
					$update_org = new Tactile_Api_Organisation();
					$update_org->status_id = 12939;
					
					// We need to loop over current details to push them up to Tactile
					foreach($org->organisations[0] as $key => $value) {
						if(!empty($value)) {
							$update_org->$key = $value;
						}
					}
					$client->saveOrganisation($update_org);
				} else {
					// Add a new organisation
					$org = new Tactile_Api_Organisation();
					$org->name = $user->getAccount()->company;
					$org->accountnumber = $user->getAccount()->site_address;
					$org->country_code = $user->getAccount()->country_code;
					$org->status_id = 12939;
					$new_org = $client->saveOrganisation($org);
					if($new_org->status == "success") {
						$org_id = $new_org->id;
					}
				}
				
	
				if(!empty($org_id)) {
					require_once 'Tactile/Api/Opportunity.php';
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
					
					
					$opportunity = new Tactile_Api_Opportunity();
					$opportunity->organisation_id = $org_id;
					$opportunity->person_id = $person_id;
					$opportunity->cost = $amount_to_charge;
					$opportunity->status_id = 4487;
					$opportunity->source_id = 2688;
					$opportunity->probability = 100;
					$opportunity->name ="Upgrade by $qty Users";
					$opportunity->archived = true;
					$opportunity->enddate = date('Y-m-d');
					$new_op = $client->saveOpportunity($opportunity);
					if($new_op->status == "success"){
						// Now we just need to add a note
						$note = new Tactile_Api_Note();
						$note->title = 'Account Upgraded';
						$note->note = $user->getAccount()->firstname.' upgraded '.$user->getAccount()->company.'\'s account by '.$qty.' users to '.$account->per_user_limit;
						
						$note->note .= '.';
						$note->organisation_id = $org_id;
						$note->opportunity_id = $new_op->id;
						$note->person_id = $person_id;
						$new_note = $client->saveNote($note);
						
					}
					
					
				}
	
			} catch (Exception $e) {
				  require_once 'Zend/Log.php';
				  $logger = new Zend_Log(new Log_Writer_Mail(NOTIFICATIONS_TO, 'Error updating our Tactile CRM via the API'));
				  $logger->crit($e->getMessage());
				  $logger->crit($e->getTraceAsString());
			}
		}
		
		Flash::Instance()->addMessage('User Limit increased successfully');
		$db->CompleteTrans();
		sendTo('users');
		return;
	}
	
	public function disable() {
		$user = $this->user;
		if (!isset($this->_data['username']) || false === $user->load($this->_data['username'])) {
			Flash::Instance()->addError('Invalid username specified');
			sendTo('users');
			return;
		}
		$current_user = CurrentlyLoggedInUser::Instance();
		if (!$current_user->canEdit($user)) {
			Flash::Instance()->addError("You don't have permission to do that");
			sendTo('users');
			return;
		}

		if($this->_data['username'] == CurrentlyLoggedInUser::Instance()->getAccount()->username){
			
			Flash::Instance()->addError("You cannot disable the account owner.");
			if(CurrentlyLoggedInUser::Instance()->username == CurrentlyLoggedInUser::Instance()->getAccount()->username){
				Flash::Instance()->addError("To change the owner please <a href='/account/change_owner/'>click here</a>.");
			}
			sendTo('users');
			return;
		}
				
		if($this->_data['username'] == CurrentlyLoggedInUser::Instance()->username){
			
			Flash::Instance()->addError("You cannot disable your own user account.");
			sendTo('users');
			return;
		}
		

		
		
		
		$user->enabled = 'f';
		if (FALSE === $user->save()) {
			Flash::Instance()->addError("Error saving User");
			sendTo('users');
			return;
		}
		Flash::Instance()->addMessage("User disabled");
		sendTo('users');
	}
	
	public function enable() {
		$user = $this->user;
		if (!isset($this->_data['username']) || false === $user->load($this->_data['username'])) {
			Flash::Instance()->addError('Invalid username specified');
			sendTo('users');
			return;
		}
		$current_user = CurrentlyLoggedInUser::Instance();
		$account = $current_user->getAccount();
		$plan = $account->getPlan();
		if (!$current_user->canEdit($user)) {
			Flash::Instance()->addError("You don't have permission to do that");
			sendTo('users');
			return;
		}
		
		// Do we have enough limit?
		if ($plan->is_per_user()) {
			$limit_checker = new LimitChecker(new UserUsageChecker($account), $account);
			$criteria = 'per_user_limit';
		} else {
			$limit_checker = new LimitChecker(new UserUsageChecker($account), $account->getPlan());
			$criteria = 'user_limit';
		}
		if (false === $limit_checker->isWithinLimit($criteria)) {
			if ($plan->is_per_user()) {
				if($current_user->isAccountOwner()) {
					if ($plan->is_free()) {
						Flash::Instance()->addError("You have as many enabled Users as your account is allowed. To enable more you need to upgrade your Plan.");
						sendTo('account', 'change_plan');
					} else {
						Flash::Instance()->addError("You have as many enabled Users as your account is allowed. To enable more you need to purchase more Users.");
						sendTo('users', 'purchase');
					}
					return;
				} else {
					Flash::Instance()->addError("You have as many enabled Users as your account is allowed. To enable more your Account Owner will need to purchase more Users.");
					sendTo('users');
					return;
				}
			} else {
				if($current_user->isAccountOwner()) {
					Flash::Instance()->addError("You have as many enabled Users as your account is allowed. To enable more you need to upgrade.");
					sendTo('account/change_plan');
					return;
				} else {
					Flash::Instance()->addError("You have as many enabled Users as your account is allowed. To enable more your Account Owner will need to upgrade.");
					sendTo('users');
					return;
				}
			}
		}
		
		
		$user->enabled = 't';
		if (FALSE === $user->save()) {
			Flash::Instance()->addError("Error saving User");
			sendTo('users');
			return;
		}
		Flash::Instance()->addMessage("User enabled");
		sendTo('users');
	}
	
}
