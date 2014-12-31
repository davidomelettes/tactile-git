<?php
/**
 * Stolen from DelayedAccountCreation
 * @author pb 
 */
class AccountCreator{
	
	/**
	 * The loaded account
	 * @access private
	 * @var TactileAccount
	 */
	private $account;
	private $user;
	/**
	 * Setter for the account_id
	 * All the information needed for execution is in the database, so this is the only field needed 
	 * @param int $id
	 * @return void
	 */
	public function setAccountId($id) {
		$this->data['account_id'] = $id;
	}
	
	/**
	 * Carries out the account creation
	 * @return void
	 */
	public function execute($enableApi=false) {
		$this->pullData();
		$db = DB::Instance();
		$db->StartTrans();
		//start off with everything owned by the 'tactile' sys-company
		EGS::setUsername('tactile');
		EGS::setCompanyId(1);
		
		$person = NewPerson::create($this->account);

		if($person===false) {
			throw new Exception('Person creation failed');
		}
		
		$user = NewUser::create($this->account,$person);

		if($user===false) {
			throw new Exception('User creation failed');
		}
		$this->user = $user;
		
		$person->setOwner($user);
		EGS::setUsername($user->username);
		
		$company = NewCompany::create($this->account);
		if($company===false) {
			throw new Exception('Company creation failed');
		}
		
		EGS::setCompanyId($company->id);
		$company->usercompanyid = $company->id;
		$company->save();
		ViewedPage::createOrUpdate(ViewedPage::TYPE_ORGANISATION, $company->id, EGS::getUsername(), $company->name);
		
		if($enableApi){
			$this->account->tactile_api_enabled = true;
		}
		$this->account->organisation_id = $company->id;
		$this->account->save();
		
		$person->organisation_id = $company->id;
		$person->usercompanyid = $company->id;
		$person->save();
		ViewedPage::createOrUpdate(ViewedPage::TYPE_PERSON, $person->id, EGS::getUsername(), $person->firstname . ' ' . $person->surname);
	
		
		$company->addUser($user);
		
		
		$roles = Role::createInitialRoles($this->account,$company,$user);
		$now = date('Y-m-d H:i:s');
		// Add omelett.es record
		$saver = new ModelSaver();
		$data = array(
			'name'=>'Tactile CRM',
			'description'=>'Easy contact and sales management.'
		);
		$omelettes = $saver->save($data,'Organisation',$errors);
		$db->Execute("INSERT INTO recently_viewed (owner,label,type,link_id,created) VALUES ('{$user->username}','Tactile CRM','organisations',{$omelettes->id},'{$now}')");		

		$data = array(
			'type'=>'E',
			'contact'=>'support@tactilecrm.com',
			'organisation_id'=>$omelettes->id,
			'main'=>true
		);
		$omelettes_cm = $saver->save($data,'Organisationcontactmethod',$errors);
		
		$data = array(
			'type'=>'W',
			'contact'=>'http://www.tactilecrm.com',
			'organisation_id'=>$omelettes->id,
			'main'=>true
		);
		$omelettes_cm = $saver->save($data,'Organisationcontactmethod',$errors);
		
		$data = array(
			'firstname'=>'George',
			'surname'=>'Step',
			'jobtitle'=>'Community Manager',
			'organisation_id'=>$omelettes->id,
			'language_code'=>'EN'
		);
		$omelettes_person = $saver->save($data,'Person',$errors);

		$data = array(
			'type'=>'E',
			'contact'=>'george@tactilecrm.com',
			'person_id'=>$omelettes_person->id,
			'main'=>true
		);
		$omelettes_person_cm = $saver->save($data,'Personcontactmethod',$errors);		

		$data = array(
			'type'=>'I',
			'contact'=>'georgestep',
			'person_id'=>$omelettes_person->id,
			'main'=>true
		);
		$omelettes_person_cm = $saver->save($data,'Personcontactmethod',$errors);		

		$data = array(
			'type'=>'L',
			'contact'=>'georgestep',
			'person_id'=>$omelettes_person->id,
			'main'=>true
		);
		//$omelettes_person_cm = $saver->save($data,'Personcontactmethod',$errors);		

		$db->Execute("INSERT INTO recently_viewed (owner,label,type,link_id,created) VALUES ('{$user->username}','George Step','people',{$omelettes_person->id},'{$now}')");
		
		
		
		// create note
		$data = array(
			'person_id'=>$omelettes_person->id,
			'organisation_id'=>$omelettes->id,
			'usercompanyid'=>$company->id,
			'owner'=>$user->username,
			'title'=>"Tactile CRM Webinar",
			'note'=>"Check out http://www.tactilecrm.com/webinar for their weekly webinars to find out more and ask questions.",
			'subject'=>"Welcome to Tactile CRM",
			'created'=>date('Y-m-d H:i:s'),
			'lastupdated'=>date('Y-m-d H:i:s'),

		);
		//$note = $saver->save($data,'Note',$errors);
		
		$data = array(
			'person_id'=>$omelettes_person->id,
			'organisation_id'=>$omelettes->id,
			'usercompanyid'=>$company->id,
			'owner'=>$user->username,
			'email_from'=>$omelettes_person_cm->contact,
			'email_to'=>$this->account->email,
			'body'=>"We just wanted to let you know that your Tactile CRM account has now been set\nup and you can start using it straight away.\n\nWe're sure you want to take Tactile CRM for a spin and see what it can do for\nyou - but before you do, you might find the following useful:\n\n1. Check out the forums at http://forums.tactilecrm.com if you have any questions\n2. Our help pages http://www.tactilecrm.com/help are a great way to find out how to make the most of Tactile CRM\n3. Check out the 60 second introduction at  http://www.tactilecrm.com/tour if you haven't already\n\nMany Thanks\nThe Tactile CRM Team.",
			'subject'=>"Welcome to Tactile CRM",
			'received'=>date('Y-m-d H:i:s'),
			'created'=>date('Y-m-d H:i:s')
		);
		
		
		$email = new Email();
		foreach($data as $k=>$v){
			$email->$k=$v;
		}
		$email->save();
		

		$data = array(
			'name'=>'Create Tactile CRM Account',
			'usercompanyid'=>$company->id,
			'owner'=>$user->username,
			'person_id'=>$person->id,
			'date'=>date('Y-m-d'),
			'time'=>date('H:i:s'),
			'completed'=>date('Y-m-d H:i:s'),
			'assigned_to'=>$user->username,
			'assigned_by'=>$user->username,
			'owner'=>$user->username,
			'created'=>date('Y-m-d H:i:s'),
			'lastupdated'=>date('Y-m-d H:i:s'),
			'alteredby'=>$user->username,
			'class'=>'todo',
			'later'=>'false'
		);
		
		
		$activity = new Tactile_Activity();
		foreach($data as $k=>$v){
			$activity->$k=$v;
		}
		$activity->save();
		$db->Execute("INSERT INTO recently_viewed (owner,label,type,link_id,created) VALUES ('{$user->username}','Create Tactile CRM Account','activities',{$activity->id},'{$now}')");

		$data = array(
			'name'=>'Import Contacts',
			'usercompanyid'=>$company->id,
			'owner'=>$user->username,
			'person_id'=>$person->id,
			'assigned_to'=>$user->username,
			'assigned_by'=>$user->username,
			'owner'=>$user->username,
			'created'=>date('Y-m-d H:i:s'),
			'lastupdated'=>date('Y-m-d H:i:s'),
			'alteredby'=>$user->username,
			'class'=>'todo',
			'later'=>'true'
		);
		
		
		
		$activity = new Tactile_Activity();
		foreach($data as $k=>$v){
			$activity->$k=$v;
		}
		$activity->save();

		$db->Execute("INSERT INTO recently_viewed (owner,label,type,link_id,created) VALUES ('{$user->username}','Import Contacts','activities',{$activity->id},'{$now}')");

		//insert the default values
		$set = new SetOfFixtures(APP_ROOT.'fixtures/defaults.yml');
		$set->bindAll('usercompanyid', $company->id);
		
		$converter = new FixtureToSQLConverter();
		$converter->setColumns(Spyc::YAMLLoad(APP_ROOT.'fixtures/columns.yml'));
		
		$sqls = $converter->getSQL();

		foreach($sqls as $tablename => $sql) {
			$stmt = $db->Prepare($sql);
			$rows = $set->getForColumns($tablename, $converter->getColumns($tablename));
			foreach($rows as $row) {
				$db->Execute($stmt, $row) or die($db->ErrorMsg() . $stmt . print_r($row, true));
			}
		}
		
		// This is getting done in a routine now
		//$this->account->sendWelcomeEmail();
		$db->CompleteTrans();
		
		/* This is getting done in a routine now
		try {
			$account_plan = new AccountPlan();
			$account_plan->load($this->account->current_plan_id);

			if (defined('PRODUCTION') && PRODUCTION == true) {
				$CM_api_key = '631cc52a3ed14b21cac26b0b46807028';
				$CM_list_id = '58ba2364c8dc21b1fa3c9e9ed17569fe';
			} else {
				$CM_api_key = 'b88e7158f71fee7151e0363f076fb2ac';
				$CM_list_id = 'de417cb5a11d0db6720633bae25a9b1e';
			}

			$client = @new SoapClient("http://api.createsend.com/api/api.asmx?wsdl");
			$response = $client->AddSubscriberWithCustomFields(
					array(
					'ApiKey' => $CM_api_key,
					'ListID' => $CM_list_id,
					'Email' => $this->account->email,
					'Name' => trim(ucwords($this->account->firstname . ' ' . $this->account->surname)),
					'CustomFields' => array(
						array(
						   'Key' => 'site_address',
						   'Value' => $this->account->site_address
						),  
						array(
							'Key' => 'username',
							'Value' => $this->account->username
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
			  $logger = new Zend_Log(new Log_Writer_Mail(NOTIFICATIONS_TO, 'Campaign monitor sign-up subscription problem'));
			  $logger->crit($e->getMessage());
			  $logger->crit($e->getTraceAsString());
		}
		*/
		
		$_SESSION['NEW_ACCOUNT'] = $this->account;
		
		$this->cleanup();
	}
	
	/**
	 * Uses the account-id to load the TactileAccount object that contains everything necessary for
	 * account creation
	 */
	private function pullData() {
		$account = new TactileAccount();
		$account->load($this->data['account_id']);
		if($account===false) {
			throw new Exception('Invalid account id specified for account-creation');
		}
		$this->account = $account;
	}
	
	
	public function getAccount(){
		return $this->account;
	}
	
	public function getUser(){
		return $this->user;
	}
	
	private function cleanup(){
		
	}

	
}
?>
