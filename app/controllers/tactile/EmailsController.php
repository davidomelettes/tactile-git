<?php

/**
 * Responsible for handling the user's emails.
 *
 * @author mjg
 */
class EmailsController extends Controller {
	
	function __construct($module=null,$view=null) {
		parent::__construct($module,$view);
		$this->uses('Person');
		$this->uses('Organisation');
		$this->uses('Opportunity');
		$this->uses('Email');
	}
	
	private function useRestriction($name) {
		$user = CurrentlyLoggedInUser::Instance()->getModel();
		$this->view->set('dropboxkey', $user->dropboxkey);
		
		$emails = new EmailCollection();
		$sh = new SearchHandler($emails,false);
		$sh->extractOrdering();
		$sh->extractPaging();
		$this->view->set('restriction',$name);
		switch($name) {
			case 'all': {
				break;
			}
			case 'unassigned': {
				$sh->addConstraint(new Constraint('e.person_id','IS','NULL'));
				$sh->addConstraint(new Constraint('e.organisation_id','IS','NULL'));
				$sh->addConstraint(new Constraint('e.opportunity_id','IS','NULL'));
				break;
			}
			case 'incoming': {
				$sh->addConstraint(new Constraint('e.direction','=','incoming'));
				break;
			}
			case 'outgoing': {
				$sh->addConstraint(new Constraint('e.direction','=','outgoing'));
				break;
			}
		}
		$sh->addConstraint(new Constraint('e.owner','=',$user->getRawUsername()));
		$sh->setOrderBy('e.created', 'DESC');
		
		$this->setTemplateName('index');
		
		// Email Addresses
		$email_addresses = new TactileEmailAddressCollection();
		$sh2 = new SearchHandler($email_addresses, false);
		$sh2->extractOrdering();
		$sh2->extractPaging();
		//$sh2->addConstraint(new Constraint('send','=','true'));
		$cc_roles = new ConstraintChain();
		$cc_roles->add(new Constraint('role_id', '=', CurrentlyLoggedInUser::getUserRole()->id));
		$cc_roles->add(new Constraint('role_id', '=', Omelette::getUserSpaceRole()->id), 'OR');
		$sh2->addConstraintChain($cc_roles);
		$email_addresses->load($sh2);
		$this->view->set('addresses_send', new EmailAddressVerifiedFilter($email_addresses));
		$this->view->set('addresses_unverified', new EmailAddressUnverifiedFilter($email_addresses));
		
		parent::index($emails,$sh);
	}
	
	function index() {
			if ($this->view->is_json) {
				$email_list_type = 'all';
			} else {
				$email_list_type = Omelette_Magic::getValue('emails_index_restriction', EGS::getUsername(), 'all');
			}

			switch($email_list_type) {
				case 'unassigned':
				case 'incoming':
				case 'outgoing':
					break;
				default:
					$email_list_type = 'all';
					break;
			}
			$this->$email_list_type();
	}
	
	function all() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('emails_index_restriction', 'all', EGS::getUsername());
		}
		$this->useRestriction('all');
	}
	
	function unassigned() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('emails_index_restriction', 'unassigned', EGS::getUsername());
		}
		$this->useRestriction('unassigned');
	}
	
	function incoming() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('emails_index_restriction', 'incoming', EGS::getUsername());
		}
		$this->useRestriction('incoming');
	}
	
	function outgoing() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('emails_index_restriction', 'outgoing', EGS::getUsername());
		}
		$this->useRestriction('outgoing');
	}
	
	function assign() {
		$email = new Email();
		if (!isset($this->_data['id']) || $email->load($this->_data['id']) === false) {
			Flash::Instance()->addError('Invalid id specified');
			sendTo('emails');
			return;
		}
		
		$user = CurrentlyLoggedInUser::Instance()->getModel();
		$this->view->set('dropboxkey', $user->dropboxkey);
		
		if ($email->owner != $user->getRawUsername()) {
			Flash::Instance()->addError('Selected email does not belong to you');
			sendTo('emails');
			return;
		}
		$this->view->set('email', $email);
		
		// To me or from me? Should always be one or the other
		$db = DB::Instance();
		$query = "SELECT person_id
			FROM person_contact_methods pcm
			LEFT JOIN people p ON p.id = pcm.person_id
			WHERE p.usercompanyid = " . $db->qstr(EGS::getCompanyId()) . "
			AND pcm.type='E' AND pcm.contact ILIKE " . $db->qstr($email->email_to); 
		$t = $db->getOne($query);
		$query = "SELECT person_id
			FROM person_contact_methods pcm
			LEFT JOIN people p ON p.id = pcm.person_id
			WHERE p.usercompanyid = " . $db->qstr(EGS::getCompanyId()) . "
			AND pcm.type='E' AND pcm.contact ILIKE " . $db->qstr($email->email_from);
		$f = $db->getOne($query);
		$email_direction = 'neither';
		if ($t === CurrentlyLoggedInUser::Instance()->getModel()->person_id) {
			$email_direction = 'incoming';
		}
		if ($f === CurrentlyLoggedInUser::Instance()->getModel()->person_id) {
			$email_direction = 'outgoing';
		}
		$this->view->set('email_direction', $email_direction);
		
		/*
		$suggested_persons = array();
		if($f !== false && $f !== CurrentlyLoggedInUser::Instance()->getModel()->person_id) {
			$details = $db->getRow("SELECT id, fullname, company, company_id FROM personoverview WHERE id = " . $db->qstr($f) . " LIMIT 1");
			$suggested_persons[] = $details;
		}
		if($t !== false && $t !== CurrentlyLoggedInUser::Instance()->getModel()->person_id) {
			$details = $db->getRow("SELECT id, fullname, company, company_id FROM personoverview WHERE id = " . $db->qstr($t) . " LIMIT 1");
			$suggested_persons[] = $details;
		}
		$this->view->set('suggested_persons',$suggested_persons);
		*/
		
		// Get the possible opp statuses for new opps
		$opp_statuses = new Opportunitystatus();
		$this->view->set('opp_statuses', $opp_statuses->getAll());
	}
	
	function delete() {
		$user = CurrentlyLoggedInUser::Instance()->getModel();
		$email = new Email();
		if(!isset($this->_data['id']) || $email->load($this->_data['id'])===false) {
			Flash::Instance()->addError('Invalid id specified');
			sendTo('emails');
			return;
		}
		if($email->owner != $user->getRawUsername()) {
			Flash::Instance()->addError('Selected email does not belong to you');
			sendTo('emails');
			return;
		}
		
		if($email->delete() === false) {
			Flash::Instance()->addError('An error occurred whilst deleting the email');
			sendTo('emails','view',null,array('id'=>$this->_data['Email']['id']));
			return;
		}
		Flash::Instance()->addMessage('Email deleted successfully');
		sendTo('emails');
	}
	
	function save() {
		$user = CurrentlyLoggedInUser::Instance()->getModel();
		$this->view->set('dropboxkey', $user->dropboxkey);
		
		$flash = Flash::Instance();
		
		$email = new Email();
		$person = DataObject::Construct('Person');
		$org = DataObject::Construct('Organisation');
		$opp = DataObject::Construct('Opportunity');
		
		$data = $this->_data['Email'];
		
		// Catch invalid ID error
		if (!isset($data['id']) || $email->load($data['id']) === false) {
			$flash->addError('Invalid id specified');
			sendTo('emails');
			return;
		}
		
		if ($email->owner != $user->getRawUsername()) {
			$flash->addError('Selected email does not belong to you');
			sendTo('emails');
			return;
		}
		
		// Catch errors with form input
		if (!empty($data['person_id']) && $person->load($data['person_id']) === false) {
			$flash->addError('Invalid Person id specified');
		}
		if (!empty($data['organisation_id']) && $org->load($data['organisation_id']) === false) {
			$flash->addError('Invalid Organisation id specified');
		}
		if (!empty($data['organisation_id']) && !empty($data['person_id']) && ($org->id !== $person->organisation_id)) {
			$flash->addError('Person specified is not a member of Organisation specified');
		}
		if (!empty($data['opportunity_id']) && $opp->load($data['opportunity_id']) === false) {
			$flash->addError('Invalid Opportunity id specified');
		}
		
		$db = DB::Instance();
		$db->StartTrans();
		$errors = array();
		if (empty($data['organisation_id']) && !empty($data['organisation']) && $data['organisation'] !== 'Type to find') {
			if (FALSE !== ($org = Tactile_Organisation::factoryFromString($data['organisation'], null, $org_errors))) {
				if ($org->save()) {
					$data['organisation_id'] = $org->id;
				} else {
					$errors[] = 'There was a problem saving the associated Organisation';
				}
			} else {
				$errors = array_merge($errors, $org_errors);
			}
		}
		if (empty($data['person_id']) && !empty($data['person']) && $data['person'] !== 'Type to find') {
			$person_data = array();
			if (!empty($data['organisation_id'])) {
				$person_data['organisation_id'] = $data['organisation_id'];
			}
			if (FALSE !== ($person = Tactile_Person::factoryFromString($data['person'], $person_data, $person_errors))) {
				if ($person->save()) {
					$data['person_id'] = $person->id;
				} else {
					$errors[] = 'There was a problem saving the associated Person';
				}
			} else {
				$errors = array_merge($errors, $person_errors);
			}
		}
		if (empty($data['opportunity_id']) && !empty($data['opportunity']) && $data['opportunity'] !== 'Type to find') {
			$opp_data = array();
			if (!empty($data['organisation_id'])) {
				$opp_data['organisation_id'] = $data['organisation_id'];
			}
			if (!empty($data['person_id'])) {
				$opp_data['person_id'] = $data['person_id'];
			}
			if (FALSE !== ($opp = Tactile_Opportunity::factoryFromString($data['opportunity'], $opp_data, $opp_errors))) {
				if ($opp->save()) {
					$data['opportunity_id'] = $opp->id;
				} else {
					$errors[] = 'There was a problem saving the associated Opportunity';
				}
			} else {
				$errors = array_merge($errors, $opp_errors);
			}
		}
		
		if (!empty($errors)) {
			$db->FailTrans();
			$db->CompleteTrans();
			$flash->addErrors($errors);
			sendTo('emails','view',null,array('id'=>$this->_data['Email']['id']));
			return;
		}
		
		$pid = $person->id;
		$email->person_id = (is_null($pid) ? 'NULL' : $pid);
		$orgid = $org->id;
		$email->organisation_id = (is_null($orgid) ? 'NULL' : $orgid);
		$oppid = $opp->id;
		$email->opportunity_id = (is_null($oppid) ? 'NULL' : $oppid);
		
		if (isset($this->_data['email_assign']) && $this->_data['email_assign'] === 'on') {
			$data = array(
				'type' => 'E',
				'person_id' => $email->person_id,
				'name' => 'Main'
			);
			
			$email_address = $email->getDirection() == 'incoming' ? $email->email_from : $email->email_to;
			
			$is_assigned = $db->getOne("
				SELECT person_id 
				FROM person_contact_methods pcm 
				LEFT JOIN people p ON p.id = pcm.person_id 
				WHERE p.usercompanyid = " . $db->qstr(EGS::getCompanyId()) . " 
					AND pcm.type='E' 
					AND p.id = " . $db->qstr($email->person_id) . " 
					AND pcm.contact ILIKE " . $db->qstr($email_address));
			if(!$is_assigned && $email_address!=="<not_found>") {
				$data['contact'] = $email_address;
				$errors = array();
				$pcm = DataObject::Factory($data,$errors,'Personcontactmethod');
				if($pcm->save() === false) {
					$db->FailTrans();
					$db->CompleteTrans();
					$flash->addError('An error occurred whilst adding the email address to the person');
					sendTo('emails','view',null,array('id'=>$this->_data['Email']['id']));
					return;
				}
				else {
					$flash->addMessage('Added email address ('.$email_address.') to person.');
				}
			}
		}
		
		if ($email->save() === false) {
			$db->FailTrans();
			$db->CompleteTrans();
			$flash->addError('An error occurred whilst saving the email');
			sendTo('emails','view',null,array('id'=>$this->_data['Email']['id']));
			return;
		} else {
			$db->CompleteTrans();
			$flash->addMessage('Email saved successfully.');
		}
		
		sendTo('emails');
	}
	
	
	public function verify_address() {
		$id = isset($this->_data['id']) ? $this->_data['id'] : '';
		$code = isset($this->_data['verify_code']) ? $this->_data['verify_code'] : '';
		
		$email = new TactileEmailAddress();
		if (FALSE === $email->load($id)) {
			Flash::Instance()->addError('Failed to load Email Address');
			sendTo('preferences/email');
			return;
		}
		if (!$email->canEdit()) {
			Flash::Instance()->addError('You are not allowed to verify that Email Address');
			sendTo('emails');
			return;
		}
		
		if (FALSE === $email->verify($code)) {
			Flash::Instance()->addError('Incorrect verification code. Please try again.');
		} else {
			Flash::Instance()->addMessage('Email Address Verified');
		}
		if ($email->isShared()) {
			sendTo('setup/email');
		} else {
			sendTo('preferences/email');
		}
	}
	
	public function options() {
		if (!$this->view->is_json) {
			sendTo('emails');
			return;
		}
		$emails = new TactileEmailAddressCollection();
		$sh = new SearchHandler($emails, false);
		$sh->extract();
		$sh->addConstraint(new Constraint('verified', '=', 'true'));
		$sh->addConstraint(new Constraint('send', '=', 'true'));
		// Only show for account role and our own role
		$role_cc = new ConstraintChain();
		$role_cc->add(new Constraint('role_id', '=', Omelette::getUserSpaceRole()->id));
		if (Tactile_AccountMagic::getAsBoolean('tactilemail_user_addresses', 't', 't')) {
			$role_cc->add(new Constraint('role_id', '=', CurrentlyLoggedInUser::Instance()->getUserRole()->id), 'OR');
		}
		$sh->addConstraintChain($role_cc);
		$emails->load($sh);
		$this->view->set('emails', $emails);
		
		// Suggest an email address to verify
		$user = CurrentlyLoggedInUser::Instance();
		$this->view->assign('user_email', $user->getModel()->getEmail());
		$this->view->assign('user_name', User::getPersonName($user->getRawUsername()));
		
		$templates = new EmailTemplateCollection();
		$sh = new SearchHandler($templates, false);
		$sh->extract();
		$sh->addConstraint(new Constraint('enabled', '=', 'true'));
		$templates->load($sh);
		$this->view->set('templates', $templates);
	}
	
	public function template() {
		if (!$this->view->is_json) {
			sendTo('emails');
			return;
		}
		$id = isset($this->_data['id']) ? $this->_data['id'] : '';
		$template = new EmailTemplate();
		if (FALSE !== $template->load($id)) {
			$this->view->set('template', $template);
		} else {
			Flash::Instance()->addError('Failed to load Template');
		}
	}
	
	public function send() {
		if (!$this->view->is_json) {
			sendTo('emails');
			return;
		}
		$email_data = isset($this->_data['TactileMail']) ? $this->_data['TactileMail'] : array();
		$errors = array();
		
		$saver = new ModelSaver();
		$email = $saver->save($email_data, 'TactileMail', $errors);
		if (FALSE !== $email) {
			$success = false;
			try {
				if (!empty($this->_data['opportunity_id'])) {
					$opp_id = ((int)$this->_data['opportunity_id']);
					$opp = new Tactile_Opportunity();
					$action = (FALSE !== $opp->load($opp_id)) ? 'opp+'.$opp_id : 'dropbox';
					$success = $email->send($action);
				} else {
					$success = $email->send();
				}
			} catch (Zend_Mail_Transport_Exception $e) {
				Flash::Instance()->addError('Transport error: Failed to send Email');
			}
			if ($success) {
				$this->view->set('email', $email);
			} else {
				Flash::Instance()->addError('Failed to send Email');
			}
		}
	}
	
	public function dropbox_vcard() {
		$user = CurrentlyLoggedInUser::Instance();
		$dropbox_address = $user->getDropboxAddress();
		
		$cm = new Tactile_Personcontactmethod();
		$cm->type = 'E';
		$cm->contact = $dropbox_address;
		
		$current_person = CurrentlyLoggedInUser::Instance()->getModel()->getPerson();
		$current_person->id = null;
		
		Autoloader::Instance()->addPath(FILE_ROOT . 'omelette/lib/icalendar/');
		$vcard = new VCard();
		$vcard->addPerson($current_person);
		$vcard->addContactMethod($cm);
		
		$this->view->set('layout', 'blank');
		header('Content-Type: text/x-vcard; charset=utf-8');
		header('Content-Disposition: attachment; filename="Tactile CRM Dropbox.vcf"');
		$this->view->set('vcard', $vcard);
	}
	
}
