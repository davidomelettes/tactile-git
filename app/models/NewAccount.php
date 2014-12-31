<?php

require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Null.php';

class NewAccount {
	
	public static function create($form_data, &$errors, $apiEnabled=false, $logger=null) {
		if (is_null($logger)) {
			$logger = new Zend_Log(new Zend_Log_Writer_Null());
		}
		$logger->info($_SERVER['REMOTE_ADDR']. ' - Account creation task started');
		
		$db = DB::Instance();
		$db->StartTrans();
		
		$errors = array();
		$account = DataObject::Factory($form_data, $errors, 'TactileAccount');
		if (FALSE === $account) {
			$logger->info($_SERVER['REMOTE_ADDR']. ' - Account creation failed at creating Account');
			$db->FailTrans();
			$db->CompleteTrans();
			return false;
		}
		
		// Create a Person
		$person = NewPerson::create($account);
		if (FALSE === $person) {
			$logger->info($_SERVER['REMOTE_ADDR']. ' - Account creation failed at creating Person');
			$errors[] = "Failed to create Person record during Account creation";
			$db->FailTrans();
			$db->CompleteTrans();
			return false;
		}
		
		// Turn that person into a User
		$user = NewUser::create($account, $person);
		if (FALSE === $user) {
			$logger->info($_SERVER['REMOTE_ADDR']. ' - Account creation failed at creating User');
			$errors[] = "Failed to create User record during Account creation";
			$db->FailTrans();
			$db->CompleteTrans();
			return false;
		}
		$person->setOwner($user);
		EGS::setUsername($user->username);
		
		// Create a housing Organisation
		$organisation = NewOrganisation::create($account);
		if (FALSE === $organisation) {
			$logger->info($_SERVER['REMOTE_ADDR']. ' - Account creation failed at creating Organisation');
			$errors[] = "Failed to create Organisation record during Account creation";
			$db->FailTrans();
			$db->CompleteTrans();
			return false;
		}
		EGS::setCompanyId($organisation->id);
		$organisation->usercompanyid = $organisation->id;
		$organisation->save();
		$organisation->addUser($user);
		ViewedPage::createOrUpdate(ViewedPage::TYPE_ORGANISATION, $organisation->id, EGS::getUsername(), $organisation->name);
		
		// Update the Person and attach them to the new Organisation
		$person->organisation_id = $organisation->id;
		$person->usercompanyid = $organisation->id;
		$person->save();
		ViewedPage::createOrUpdate(ViewedPage::TYPE_PERSON, $person->id, EGS::getUsername(), $person->firstname . ' ' . $person->surname);
		
		// Switch on API?
		if ($apiEnabled){
			$account->tactile_api_enabled = true;
		}
		$account->organisation_id = $organisation->id;
		$account->save();
		
		// Create initial User roles and default data
		$roles = NewRole::createInitialRoles($account, $organisation, $user);
		self::_insertExampleData($account, $person);
		self::_insertDefaultData($account, $person);
		
		$logger->info($_SERVER['REMOTE_ADDR']. ' - Account creation succeeded: '. $account->site_address);
		
		// All done!
		$_SESSION['NEW_ACCOUNT'] = $account;
		if (!$db->CompleteTrans()) {
			$errors[] = 'Failed to complete Account creation, please try again later.';
			return false;
		}
		$logger->info($_SERVER['REMOTE_ADDR']. ' - ' . $db->getOne("SELECT id FROM tactile_accounts WHERE site_address = " . $db->qstr($account->site_address)));
		return $account;
	}
	
	private function _insertExampleData(TactileAccount $account, NewPerson $person) {
		$saver = new ModelSaver();
		
		$data = array(
			'name'			=> 'Tactile CRM',
			'description'	=> 'Easy contact and sales management.'
		);
		$omelettes = $saver->save($data, 'Organisation', $errors);
		ViewedPage::createOrUpdate(ViewedPage::TYPE_ORGANISATION, $omelettes->id, EGS::getUsername(), $omelettes->name);

		$data = array(
			'type'				=> 'E',
			'contact'			=> 'support@tactilecrm.com',
			'organisation_id'	=> $omelettes->id,
			'main'				=> true
		);
		$omelettes_cm = $saver->save($data, 'Organisationcontactmethod', $errors);
		
		$data = array(
			'type'				=> 'W',
			'contact'			=> 'http://www.tactilecrm.com',
			'organisation_id'	=> $omelettes->id,
			'main'				=> true
		);
		$omelettes_cm = $saver->save($data, 'Organisationcontactmethod', $errors);
		
		$data = array(
			'firstname'			=> 'George',
			'surname'			=> 'Step',
			'jobtitle'			=> 'Community Manager',
			'organisation_id'	=> $omelettes->id,
			'language_code'		=> 'EN'
		);
		$omelettes_person = $saver->save($data, 'Person', $errors);
		ViewedPage::createOrUpdate(ViewedPage::TYPE_PERSON, $omelettes_person->id, EGS::getUsername(), $omelettes_person->firstname . ' ' . $omelettes_person->surname);

		$data = array(
			'type'		=> 'E',
			'contact'	=> 'george@tactilecrm.com',
			'person_id'	=> $omelettes_person->id,
			'main'		=> true
		);
		$omelettes_person_cm = $saver->save($data, 'Personcontactmethod', $errors);		

		$data = array(
			'type'		=> 'I',
			'contact'	=> 'georgestep',
			'person_id'	=> $omelettes_person->id,
			'main'		=> true
		);
		$omelettes_person_cm = $saver->save($data, 'Personcontactmethod', $errors);		

		$data = array(
			'person_id'			=> $omelettes_person->id,
			'organisation_id'	=> $omelettes->id,
			'usercompanyid'		=> EGS::getCompanyId(),
			'owner'				=> EGS::getUsername(),
			'email_from'		=> $omelettes_person_cm->contact,
			'email_to'			=> $account->email,
			'body'				=> "We just wanted to let you know that your Tactile CRM account has now been set\nup and you can start using it straight away.\n\nWe're sure you want to take Tactile CRM for a spin and see what it can do for\nyou - but before you do, you might find the following useful:\n\n1. Check out the forums at http://forums.tactilecrm.com if you have any questions\n2. Our help pages http://www.tactilecrm.com/help are a great way to find out how to make the most of Tactile CRM\n3. Check out the 60 second introduction at  http://www.tactilecrm.com/tour if you haven't already\n\nMany Thanks\nThe Tactile CRM Team.",
			'subject'			=> "Welcome to Tactile CRM",
			'received'			=> date('Y-m-d H:i:s'),
			'created'			=> date('Y-m-d H:i:s')
		);
		$email = new Email();
		foreach ($data as $k=>$v){
			$email->$k = $v;
		}
		$email->save();
		
		$data = array(
			'name'				=> 'Create Tactile CRM Account',
			'usercompanyid'		=> EGS::getCompanyId(),
			'owner'				=> EGS::getUsername(),
			'person_id'			=> $person->id,
			'date'				=> date('Y-m-d'),
			'time'				=> date('H:i:s'),
			'completed'			=> date('Y-m-d H:i:s'),
			'assigned_to'		=> EGS::getUsername(),
			'assigned_by'		=> EGS::getUsername(),
			'owner'				=> EGS::getUsername(),
			'created'			=> date('Y-m-d H:i:s'),
			'lastupdated'		=> date('Y-m-d H:i:s'),
			'alteredby'			=> EGS::getUsername(),
			'class'				=> 'todo',
			'later'				=> 'false'
		);
		$activity = new Tactile_Activity();
		foreach ($data as $k=>$v){
			$activity->$k = $v;
		}
		$activity->save();
		ViewedPage::createOrUpdate(ViewedPage::TYPE_ACTIVITY, $activity->id, EGS::getUsername(), $activity->name);

		$data = array(
			'name'			=> 'Import Contacts',
			'usercompanyid'	=> EGS::getCompanyId(),
			'owner'			=> EGS::getUsername(),
			'person_id'		=> $person->id,
			'assigned_to'	=> EGS::getUsername(),
			'assigned_by'	=> EGS::getUsername(),
			'owner'			=> EGS::getUsername(),
			'created'		=> date('Y-m-d H:i:s'),
			'lastupdated'	=> date('Y-m-d H:i:s'),
			'alteredby'		=> EGS::getUsername(),
			'class'			=> 'todo',
			'later'			=> 'true'
		);
		$activity = new Tactile_Activity();
		foreach ($data as $k=>$v){
			$activity->$k = $v;
		}
		$activity->save();
		ViewedPage::createOrUpdate(ViewedPage::TYPE_ACTIVITY, $activity->id, EGS::getUsername(), $activity->name);
	}
	
	private function _insertDefaultData(TactileAccount $account, NewPerson $person) {
		$set = new SetOfFixtures(APP_ROOT.'fixtures/defaults.yml');
		$set->bindAll('usercompanyid', EGS::getCompanyId());
		
		$converter = new FixtureToSQLConverter();
		$converter->setColumns(Spyc::YAMLLoad(APP_ROOT.'fixtures/columns.yml'));
		
		$sqls = $converter->getSQL();
		$db = DB::Instance();
		foreach ($sqls as $tablename => $sql) {
			$stmt = $db->Prepare($sql);
			$rows = $set->getForColumns($tablename, $converter->getColumns($tablename));
			foreach ($rows as $row) {
				$db->Execute($stmt, $row) or die($db->ErrorMsg() . $stmt . print_r($row, true));
			}
		}
	}
	
	public static function sendWelcomeEmail($plan, $account) {
		$notification = new NotificationEmail($plan->name.' Tactile Signup ('.$account->site_address.')', 'signups@tactilecrm.com');
		$notification->set('Account-id', $account->id);
		$notification->set('Company', $account->company);
		$notification->set('Amount', $plan->cost_per_month);
		$notification->set('Email', $account->email);
		$notification->set('Signup Code', 'n/a');
		try {
			$notification->send();
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

}
