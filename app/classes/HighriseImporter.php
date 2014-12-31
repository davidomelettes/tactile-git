<?php
require_once('Service/Highrise.php');
require_once('Service/Highrise/Collection/Users.php');
require_once('Service/Highrise/Collection/Companies.php');
require_once('Service/Highrise/Collection/People.php');
require_once('Service/Highrise/Collection/Tasks.php');
require_once('Service/Highrise/Collection/Deals.php');
require_once('Service/Highrise/Collection/Notes.php');
require_once('Service/Highrise/Collection/Tags.php');
require_once('Service/Highrise/Collection/Emails.php');
require_once('Service/Highrise/Collection/Task/Types.php');
require_once('Service/Highrise/Collection/Deal/Types.php');
/**
 * @author Paul M Bain
 * @package Highrise
 *
 */
class HighriseImporter{
	
	protected $_people=array();
	protected $_companies=array();
	protected $_notes=array();
	protected $_users=array();
	protected $_deals=array();
	protected $_tasks=array();
	protected $_dealTypes=array();
	protected $_taskTypes=array();
	protected $_dealStatusMap=array();
	protected $_countries = array();
	protected $_config = array();
	protected $_addressFields = array(
		'city'		=> 'town',
		'country'	=> 'country',
		'state'		=> 'street3',
		'street'	=> 'street1',
		'zip'		=> 'postcode'
	);
	
	/**
	 * @var Service_Highrise
	 */
	protected $_service;
	
	protected $_logger;
	
	public function __construct($config, $logger = null){
		$this->_config = $config;
		$this->_logger = $logger;
		$this->_service = new Service_Highrise($this->_config['hr_site'],$this->_config['hr_user'],$this->_config['hr_pass'], $logger);
		Service_Highrise_Collection::setDefaultService($this->_service);
		
		$country = new Country();
		$this->_countries = array_flip($country->getAll());
		

	}
		
	public function import(){
		$db = DB::Instance();
		$db->BeginTrans();

		if(!$this->categories()){
			$db->FailTrans();
			$db->CommitTrans();
			$this->_logger->debug('Failed on categories');
			return false;
		}

		if(!$this->companies()){
			$db->FailTrans();
			$db->CommitTrans();
			$this->_logger->debug('Failed on companies');
			return false;
		}

		if(!$this->people()){
			$db->FailTrans();
			$db->CommitTrans();
			$this->_logger->debug('Failed on people');
			return false;
		}
		
		if(!$this->deals()){
			$db->FailTrans();
			$db->CommitTrans();
			$this->_logger->debug('Failed on deals');
			return false;
		}

		if(!$this->tasks()){
			$db->FailTrans();
			$db->CommitTrans();
			$this->_logger->debug('Failed on tasks');
			return false;
		}
	
		if(!$this->tags()){
			$db->FailTrans();
			$db->CommitTrans();
			$this->_logger->debug('Failed on tags');
			return false;
		}
		
		$db->CommitTrans();
		return true;
	}
	
	/**
	 * Insert Users
	 *
	 * @return boolean
	 */
	protected function users(){
		$users = new Service_Highrise_Collection_Users();
		if ($users->fetchAll()) {
			return false;
		}
		
		if(!empty($users)){
			foreach($users as $user){
				// ???
			}
		}
		return false;
	}
	
	protected function companies(){
		$companies = new Service_Highrise_Collection_Companies();
		if (!$companies->fetchAll()) {
			$this->_logger->debug('FALSE at Companies fetch');
			return false;
		}
		$this->_logger->debug(count($companies) . ' companies fetched');
		$saver = new ModelSaver();

		if (!empty($companies)) {
			foreach ($companies as $company) {
				$row_errors = array();
				
				$company_data = array(
					'name'			=> (string) $company->name,
					'owner'			=> $this->getOwner((string) $company->{"owner-id"}),
					'description'	=> (string) $company->background
				);
				$tactile_company = $saver->save($company_data, 'Organisation', $row_errors);
				
			
				if ($tactile_company !==false) {
					$this->_companies[(string) $company->id] = $tactile_company->id;
					
					foreach ($company->contact_data->addresses->address as $address) {
						$address_data = array('organisation_id' => $tactile_company->id, 'name' => (string)$address->location);
						foreach ($this->_addressFields as $h => $t) {
							$value = (string) $address->$h; 
							if (!empty($value)) {
								if ($h == 'street') {
									$parts = explode("\n",$value);
									$address_data['street1']=$parts[0];
								if (isset($parts[1])) {
									$address_data['street2']=$parts[1];
								} 
							} else if($h == 'country'){
								$address_data['country_code'] = $this->decodeCountry($value);
							} else 
								$address_data[$t] = $value;
							}
						}
						$adr = $saver->save($address_data, 'Organisationaddress', $these_errors);
						if (!$adr) {
								$row_errors = array_merge($row_errors, $these_errors);
								$this->_logger->debug('Failed to create Address: ' . print_r($these_errors, 1));
								$this->_logger->debug('Failed to create Address: ' . print_r($address_data, 1));
							}
					}
					
					if (!empty($company->{"contact-data"}->{"email-addresses"}->{"email-address"})) {
						foreach ($company->{"contact-data"}->{"email-addresses"}->{"email-address"} as $data) {
							$contact_data = array();
							$contact_data['organisation_id'] = $tactile_company->id;
							$contact_data['type'] = 'E';
							$contact_data['contact'] = (string) $data->address;
							$contact_data['name'] = (string) $data->location;
							$these_errors = array();
							$contact = $saver->save($contact_data, 'Organisationcontactmethod', $these_errors);
							if (!$contact) {
								$row_errors = array_merge($row_errors, $these_errors);
								$this->_logger->debug('Failed to create Contact Method: ' . print_r($these_errors, 1));
								$this->_logger->debug('Failed to create Contact Method: ' . print_r($contact_data, 1));
							}
						}
					}
					
					if (!empty($company->{"contact-data"}->{"web-address"}->{"web-address"})) {
						foreach ($company->{"contact-data"}->{"web-address"}->{"web-addres"} as $data) {
							$contact_data = array();
							$contact_data['organisation_id'] = $tactile_company->id;
							$contact_data['type'] = 'W';
							$contact_data['contact'] = (string) $data->address;
							$contact_data['name'] = (string) $data->location;
							$these_errors = array();
							$contact = $saver->save($contact_data, 'Organisationcontactmethod', $these_errors);
							if (!$contact) {
								$row_errors = array_merge($row_errors, $these_errors);
								$this->_logger->debug('Failed to create Contact Method: ' . print_r($these_errors, 1));
								$this->_logger->debug('Failed to create Contact Method: ' . print_r($contact_data, 1));
							}
						}
					}
					
					if (!empty($company->{"contact-data"}->{"twitter-accounts"}->{"twitter-account"})) {	
						foreach($company->{"contact-data"}->{"twitter-accounts"}->{"twitter-account"}as $data) {
							$contact_data = array();
							$contact_data['organisation_id'] = $tactile_company->id;
							$contact_data['type'] = 'I';
							$contact_data['contact'] = $data->username;
							$contact_data['name'] = (string) $data->location;
							$these_errors = array();
							$contact = $saver->save($contact_data, 'Organisationcontactmethod', $these_errors);
							if (!$contact) {
								$row_errors = array_merge($row_errors, $these_errors);
								$this->_logger->debug('Failed to create Contact Method: ' . print_r($these_errors, 1));
								$this->_logger->debug('Failed to create Contact Method: ' . print_r($contact_data, 1));
							}
						}
					}
					
					if (!empty($company->{"contact-data"}->{"phone-numbers"}->{"phone-number"})) {
						foreach ($company->{"contact-data"}->{"phone-numbers"}->{"phone-number"} as $data) {
							$contact_data = array();
							$contact_data['organisation_id'] = $tactile_company->id;
							switch ($data['location']) {
								case 'Fax':
									$contact_data['type'] = 'F';
									break;
								case 'Mobile':
									$contact_data['type'] = 'M';
									break;
								case 'Skype':
									$contact_data['type'] = 'S';
									break;
								default:
									$contact_data['type'] = 'T';
							}
							
							$contact_data['contact'] = (string)$data->number;
							$contact_data['name'] = (string) $data->location;
							$these_errors = array();
							$contact = $saver->save($contact_data, 'Organisationcontactmethod', $these_errors);
							if (!$contact) {
								$row_errors = array_merge($row_errors, $these_errors);
								$this->_logger->debug('Failed to create Contact Method: ' . print_r($these_errors, 1));
								$this->_logger->debug('Failed to create Contact Method: ' . print_r($contact_data, 1));
							}
						}
					}

					$notes = $company->getNotes();
					if (!empty($notes)) {
						foreach ($notes as $note) {
							$note_data = array(
								'note'				=> (string) $note->body,
								'organisation_id'	=> $tactile_company->id,
								'owner'				=> $this->getOwner((string)$note->{"owner-id"}),
								'created'			=> (string) $note->{"created-at"}
							);
							
							if (strlen($note_data['note']) > 100) {
								$note_data['title']= substr($note_data['note'],100)."...";
							} else {
								$note_data['title'] = $note_data['note'];
							}
							$newnote = $saver->save($note_data, 'Note', $these_errors);
							if ($newnote !== false) {
								$newnote->update($newnote->id, array('created','lastupdated'),array((string)$note->{"created-at"},(string)$note->{"created-at"}));
							}
						}
					}
					
					$emails = $company->getEmails();
					if (!empty($emails)) {
						foreach($emails as $email) {
							$email_data = array(
								'subject'			=> (string) $email->title,
								'body'				=> (string) $email->body,
								'organisation_id'	=> $tactile_company->id,
								'email_from'		=> "Unknown (Highrise Import)",
								'email_to'			=> "Unknown (Highrise Import)",
								'owner'				=> $this->getOwner((string)$email->{"owner-id"}),
								'created'			=> $email->{"created-at"}
							);
							$tactile_email = $saver->save($email_data, 'Email', $these_errors);
							if (false !== $tactile_email) {
								$tactile_email->update($tactile_email->id, array('created', 'received'), array($email_data['created'], $email_data['created']));
							}
						}
					}
				} else {
					// failed to create org
					$this->_logger->debug('Failed to create Org: ' . print_r($row_errors, 1));
					$this->_logger->debug('Failed to create Org: ' . print_r($company_data, 1));
				}
			}
		}
		return true;
	}
	
	protected function people(){
		$people = new Service_Highrise_Collection_People();
		if (!$people->fetchAll()) {
			return false;
		}
		$saver = new ModelSaver();
		
		$row_errors = array();
		if(!empty($people)){
			foreach($people as $person){
				$person_data = array(
					'jobtitle'	=> (string) $person->title,
					'firstname'	=>	(string) $person->{"first-name"},
					'surname'	=>	(string) $person->{"last-name"},
					'owner'	=>	$this->getOwner((string) $person->{"owner-id"}),
					'organisation_id'=> $this->getCompany((string)$person->{"company-id"}),
					'description'	=> (string) $person->background
				);	
				
				if(empty($person_data['surname'])){
					$person_data['surname']='-';
				}
				
				$tactile_person = $saver->save($person_data, 'Person', $row_errors);
				if($tactile_person !== false){
					$this->_people[(string)$person->id]=$tactile_person->id;
					
					foreach($person->contact_data->addresses->address as $address){
						$address_data = array('person_id' => $tactile_person->id, 'name' => (string)$address->location);
						foreach($this->_addressFields as $h=>$t){
							$value = (string) $address->$h; 
							if(!empty($value)){
								if($h == 'street'){
									$parts = explode("\n",$value);
									$address_data['street1']=$parts[0];
								if(isset($parts[1])){
									$address_data['street2']=$parts[1];
								} 
							} else if($h == 'country'){
								$address_data['country_code'] = $this->decodeCountry($value);
							} else 
								$address_data[$t] = $value;
							}
						}
						$adr = $saver->save($address_data, 'Personaddress', $these_errors);
						$row_errors = array_merge($row_errors, $these_errors);
					}

					if(!empty($person->{"contact-data"}->{"email-addresses"}->{"email-address"})){
						foreach($person->{"contact-data"}->{"email-addresses"}->{"email-address"} as $data) {
							$email_data = array();
							$email_data['person_id'] = $tactile_person->id;
							$email_data['type'] = 'E';
							$email_data['contact']=(string)$data->address;
							$email_data['name'] = (string) $data->location;
							$these_errors = array();
							$email = $saver->save($email_data, 'Personcontactmethod', $these_errors);
							$row_errors = array_merge($row_errors, $these_errors);
						}
					}
					
					
					
					if(!empty($person->{"contact-data"}->{"web-address"}->{"web-address"})){
						foreach($person->{"contact-data"}->{"web-address"}->{"web-addres"} as $data) {
							$web_data = array();
							$web_data['person_id'] = $tactile_person->id;
							$web_data['type'] = 'W';
							$web_data['contact']=(string) $data->address;
							$web_data['name'] = (string) $data->location;
							$these_errors = array();
							$email = $saver->save($web_data, 'Personcontactmethod', $these_errors);
							$row_errors = array_merge($row_errors, $these_errors);
						}
					}
					
					
					if(!empty($person->{"contact-data"}->{"twitter-accounts"}->{"twitter-account"})){	
						foreach($person->{"contact-data"}->{"twitter-accounts"}->{"twitter-account"}as $data) {
							$twitter_data = array();
							$twitter_data['person_id'] = $tactile_person->id;
							$twitter_data['type'] = 'I';
							$twitter_data['contact']=$data->username;
							$twitter_data['name'] = (string) $data->location;
							$these_errors = array();
							$email = $saver->save($twitter_data, 'Personcontactmethod', $these_errors);
							$row_errors = array_merge($row_errors, $these_errors);
						}
					}
										
					
					
					if(!empty( $person->{"contact-data"}->{"phone-numbers"}->{"phone-number"})){
						foreach( $person->{"contact-data"}->{"phone-numbers"}->{"phone-number"} as $data) {
							$contact_data['person_id'] = $tactile_person->id;
							
							switch($data['location']){
								case 'Fax':
									$contact_data['type'] = 'F';
									break;
								case 'Mobile':
									$contact_data['type'] = 'M';
									break;
								case 'Skype':
									$contact_data['type'] = 'S';
									break;
								default:
									$contact_data['type'] = 'T';
							}
							
							$contact_data['contact']=(string)$data->number;
							$contact_data['name'] = (string) $data->location;
							if(empty($contact_data['contact'])){
								continue;
							}
							$these_errors = array();
							$email = $saver->save($contact_data, 'Personcontactmethod', $these_errors);
							$row_errors = array_merge($row_errors, $these_errors);
						}
					}

					
					$notes = $person->getNotes();
					if(!empty($notes)){
						foreach($notes as $note){
							
							$note_data = array(
								'note'	=>	(string) $note->body,
								'person_id'=>$tactile_person->id,
								'owner'=>$this->getOwner((string)$person->{"owner-id"}),
								'created'=>(string)$note->{"created-at"}
							);
							
							if(strlen($note_data['note']) > 100){
								$note_data['title']= substr($note_data['note'],100)."...";
							} else {
								$note_data['title'] = $note_data['note'];
							}
							$newnote = $saver->save($note_data, 'Note', $these_errors);
							if($newnote !== false){
								$newnote->update($newnote->id, array('created','lastupdated'),array((string)$note->{"created-at"},(string)$note->{"created-at"}));
							}
						}
					}
					
					$emails = $person->getEmails();
					if(!empty($emails)){
						foreach($emails as $email){
							
							$email_data = array(
								'subject'	=>	(string) $email->title,
								'body'	=>	(string) $email->body,
								'person_id'=>$tactile_person->id,
								'email_from' => "Unknown (Highrise Import)",
								'email_to'	=> "Unknown (Highrise Import)",
								'owner'=>$this->getOwner((string)$email->{"owner-id"}),
								'created'=>$email->{"created-at"}
							);
							$tactile_email = $saver->save($email_data, 'Email', $these_errors);
							if(false !== $tactile_email){
								$tactile_email->update($tactile_email->id, array('created','received'),array($email_data['created'],$email_data['created']));
							}
						}
					}
					
				} else {
					
				}
			}
			
		}
		return true;
	}

	
	public function categories(){
		$saver = new ModelSaver();
		$these_errors = array();
		
		$deals = new Service_Highrise_Collection_Deal_Types();
		if (!$deals->fetchAll()) {
			return false;
		}
		$x=1;
		if(!empty($deals)){
			foreach($deals as $deal){
				$deal_model = DataObject::Construct('Opportunitytype');
				$cc = new ConstraintChain();
				$cc->add(new Constraint('name', '=', (string)$deal->name));
				$existing = $deal_model->loadBy($cc);
				
				if($existing){
					$this->_dealTypes[(string)(string)$deal->id] = $existing->id;
				} else {
					$data = array(
						'name'=>(string)$deal->name,
						'position'=>$x
					);
					$tactile_deal = $saver->save($data, 'Opportunitytype', $these_errors);
					$this->_dealTypes[(string)(string)$deal->id] = $tactile_deal->id;
					$x++;
				}
			}
		}
		
		$tasks = new Service_Highrise_Collection_Task_Types();
		if (!$tasks->fetchAll()) {
			return false;
		}
		$x=1;
		if(!empty($tasks)){
			foreach($tasks as $task){
				$task_model = DataObject::Construct('Activitytype');
				$cc = new ConstraintChain();
				$cc->add(new Constraint('name', '=', (string)$task->name));
				$existing = $task_model->loadBy($cc);
				
				if($existing){
					$this->_taskTypes[(string)(string)$task->id] = $existing->id;
				} else {
					$data = array(
						'name'=>(string)$task->name,
						'position'=>$x
					);
					$tactile_type = $saver->save($data, 'Activitytype', $these_errors);
					$this->_taskTypes[(string)(string)$task->id] = $tactile_type->id;
					$x++;
				}
			}
		}
		return true;
		
	}
	
	
	public function deals(){
		$saver = new ModelSaver();
		$these_errors = array();
		
		$deals = new Service_Highrise_Collection_Deals();
		if (!$deals->fetchAll()) {
			return false;
		}

		if(!empty($deals)){

			foreach($deals as $deal){
				
				$deal_data = array(
					'background'	=>	(string)$deal->description,
					'type_id'		=>	$this->getDealType((string)$deal->{"category-id"}),
					'owner'			=>	$this->getOwner((string)$deal->{"owner-id"}),
					'cost'			=>	(string)$deal->price,
					'name'			=>	(string)$deal->name
				);
				
				switch((string)$deal->status){
					case 'pending':
							$deal_data['status_id'] = $this->_dealStatusMap['pending'];
							$deal_data['enddate']=date('Y-m-d',mktime(0,0,0,date('m'),date('d')+30,date('Y')));
						break;
					case 'won':
							$deal_data['status_id'] = $this->_dealStatusMap['won'];
							$deal_data['enddate']=(string)$deal->{"status-changed-on"};
						break;
					case 'lost':
							$deal_data['status_id'] = $this->_dealStatusMap['lost'];
							$deal_data['enddate']=(string)$deal->{"status-changed-on"};
						break;
					
				}
				
				if(key_exists((string)$deal->{"party-id"},$this->_people)){
					
					$deal_data['person_id']=$this->_people[(string)$deal->{"party-id"}];
				} else if(key_exists((string)$deal->{"party-id"},$this->_companies)){
					$deal_data['organisation_id']=$this->_people[(string)$deal->{"party-id"}];
				}
				
				$op = $saver->save($deal_data, 'Opportunity', $these_errors);
				
				
				if(!is_null($op) && $op !== false){
					$this->_deals[(string)$deal->id]=$op->id;
				}
			}
		}
		
		return true;
	}
	
	public function tasks(){
		$saver = new ModelSaver();
		$these_errors = array();
		
		$tasks = new Service_Highrise_Collection_Tasks();
		if (!$tasks->fetchAll()) {
			return false;
		}
		
		if(!empty($tasks)){
			foreach($tasks as $task){
				$task_data = array(
					'assigned_to'		=>	$this->getOwner((string)$task->{"owner-id"}),
					'name'		=>	$task->body,
					'type_id'	=>	$this->getTaskType((string)$task->{"category-id"})
				);
				
				$due = (string)$task->{"due-at"};
				if(!empty($due)){
					$time = strtotime($due);
					$task_data['date'] = date('Y-m-d',$time);
					$task_data['time'] = date('H:i:s',$time);
				}
				
				$done = (string)$task->{"done-at"};
				if(!empty($done)){
					$task_data['completed']=$done;
				}
				
				switch((string)$task->{"subject-type"}){
					case 'Party':
							if(key_exists((string)$task->{'subject-id'},$this->_companies)){
								$task_data['organisation_id']=$this->_companies[(string)$task->{'subject-id'}];
							} else if(key_exists((string)$task->{'subject-id'},$this->_companies)){
								$task_data['person_id']=$this->_companies[(string)$task->{'subject-id'}];
							}
						break;
					case 'Deal':
							if(key_exists((string)$task->{'subject-id'},$this->_deals)){
								$task_data['opportunity_id']=$this->_deals[(string)$task->{'subject-id'}];
							} 
						break;
				}
				
				$tatile_task = $saver->save($task_data, 'Tactile_Activity', $these_errors);
				if(false !== $task){
					$this->_tasks[(string)$task->id] = $tatile_task->id;
				}
				
			}
		}
		
		return true;
		
		
	}
	
	public function tags(){
		$saver = new ModelSaver();
		$these_errors = array();
		
		$tags = new Service_Highrise_Collection_Tags();
		if (!$tags->fetchAll()) {
			return false;
		}
		
		if(!empty($tags)){
			foreach($tags as $tag){
				$contactTags = new Service_Highrise_Collection_Tags();
				$contactTags->fetchAll(array('id'=>(string)$tag->id));
				if(empty($contactTags)){
					continue;
				}
				foreach($contactTags as $contact){
					if(key_exists((string)$contact->id,$this->_companies)){
						$tid = $this->_companies[(string)$contact->id];
						$org = new Tactile_Organisation();
						$org->load($tid);
						
						$ti = new TaggedItem($org);
						$ti->addTag((string)$tag->name);
					}else if(key_exists((string)$contact->id,$this->_people)){
						$tid = $this->_people[(string)$contact->id];
						$person = new Tactile_Person();
						$person->load($tid);
						
						$ti = new TaggedItem($person);
						$ti->addTag((string)$tag->name);
					}
				}
				
			}
		}
		return true;
	}
	
	public function setUsers($users){
		$this->_users = $users;
	}
	
	protected function getOwner($id){
		if(empty($id)){
			return "";
		}
		if(key_exists($id,$this->_users)){
			return $this->_users[$id];
		} 
		return "";
	}
	
	
	public function getCompany($id){
		if(empty($id)){
			return "";
		}
		if(key_exists($id,$this->_companies)){
			return $this->_companies[$id];
		} 
		return "";
	}
	
	protected function decodeCountry($country){
		if(key_exists($country,$this->_countries)){
			return $this->_countries[$country];
		}
		return	$code = EGS::getCountryCode();
	}
	
	protected function getDealType($id){

		if(!empty($id) && key_exists($id,$this->_dealTypes)){
			return $this->_dealTypes[$id];
		} 
		return "";
	}

	
	protected function getTaskType($id){

		if(!empty($id) && key_exists($id,$this->_taskTypes)){
			return $this->_taskTypes[$id];
		} 
		return "";
	}	
	
	public function setDealStatus($status){
		$this->_dealStatusMap = $status;
	}
	
	public function getOrgs(){
		return $this->_companies;
		
	}
	
	public function getPeople(){
		return $this->_people;
	}
	
	public function getOps(){
		return $this->_deals;
	}
	
	public function getActivities(){
		return $this->_tasks;
	}
	
	public function getService() {
		return $this->_service;
	}
}
