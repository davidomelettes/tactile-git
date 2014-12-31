<?php
class DelayedHighriseImporter extends DelayedTask {

	/**
	 * Set if we should add users and assign records to them
	 *
	 * @param boolean $val
	 */
	public function retainUsers($val){
		$this->data['users']=$val;
	}
	
	public function credentials($site,$username,$password){
		$this->data['hr_user']=$username;
		$this->data['hr_site']=$site;
		$this->data['hr_pass']=$password;
	}
	
	public function setUsers($users){
		$this->data['hr_users']=$users;
	}
	
	public function setTypes($types){
		$this->data['hr_types']=$types;
	}
	
	/**
	 * GO GO GO!
	 */
	public function execute(){
		$user_zone = CurrentlyLoggedInUser::Instance()->getTimezoneString();
		date_default_timezone_set($user_zone);
		
		$importer = new HighriseImporter($this->data, $this->logger);
		$importer->setUsers($this->data['hr_users']);
		$importer->setDealStatus($this->data['hr_types']);
		try {
			$success = $importer->import();
			if (!$success) {
				$error_msg = 'there was an unexpected problem (type 1) during the import. Please contact support via support@tactilecrm.com';
			}
		} catch (Service_Highrise_Exception $e) {
			$success = false;
			$error_msg = 'there was an unexpected problem (type 2) during the import. Please contact support via support@tactilecrm.com';
			$this->logger->debug($e->getMessage());
			$this->logger->debug($importer->getService()->getHttpClient()->getLastResponse()->getBody());
		} catch (Exception $e) {
			$success = false;
			$error_msg = 'there was an unexpected problem (type 3) during the import. Please contact support via support@tactilecrm.com';
			$this->logger->debug($e->getMessage());
		}
		
		$mail = new Omelette_Mail('highrise_import_status');
		if (!$success) {
			$mail->getView()->set('error_msg', $error_msg);
		}
		
		$organisation_ids = array_unique($importer->getOrgs());
		$sharing = Omelette_OrganisationRoles::normalize(array(
			'write' => 'everyone',
			'read' => 'everyone'
		));
		if (FALSE !== Omelette_OrganisationRoles::AssignReadAccess($organisation_ids,$sharing['read']) &&
			FALSE !== Omelette_OrganisationRoles::AssignWriteAccess($organisation_ids, $sharing['write'])) {
				//hmm
		}
		
		//then add tags to companies
		$tags = array_map('trim', explode(',', $this->data['tags']));
		$taggable = new TaggedItem(DataObject::Construct('Tactile_Organisation'));
		$taggable->addTagsInBulk($tags, $organisation_ids);
		
		//and tags to people
		$taggable_person = new TaggedItem(DataObject::Construct('Person'));
		$taggable_person->addTagsInBulk($tags, $importer->getPeople());
		
		$taggable_opportunity = new TaggedItem(DataObject::Construct('Opportunity'));
		$taggable_opportunity->addTagsInBulk($tags, $importer->getOps());
		
		$taggable_activity = new TaggedItem(DataObject::Construct('Tactile_Activity'));
		$taggable_activity->addTagsInBulk($tags, $importer->getActivities());
		
		//all done, so send email
		$user = DataObject::Construct('User');
		$user->load(EGS::getUsername());
		$email_address = $user->getEmail();
		
		//we can only send if there is an email address
		if($email_address !== false) {
			$mail->getMail()
				->addTo($email_address)
				->setSubject('Tactile CRM: Status Update')
				->setFrom(TACTILE_EMAIL_FROM, TACTILE_EMAIL_NAME);
			
			$mail->send();
			$this->logger->info('Email sent to ' . $email_address);
		}
		
		date_default_timezone_set('Europe/London');
		parent::cleanup();
	}
	
	/**
	 * Pass in an assoc-array of read=>[ids] and write=>[ids]
	 * @param Array $sharing
	 * @return void
	 */
	public function setSharing($sharing) {
		$this->data['sharing'] = $sharing;
	}

	/**
	 * Pass in a comma-separated list of tags to apply to the imported entries
	 *
	 * @param String $tags
	 */
	public function setTags($tags) {
		$this->data['tags'] = $tags;
	}
	
	
	
}
?>