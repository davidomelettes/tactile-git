<?php

class WebformController extends Controller {
	
	public function index() {
		$this->view->set('webform_enabled', Tactile_AccountMagic::getAsBoolean('webform_enabled', 't', 'f'));
		$this->view->set('webform_email_to', Tactile_AccountMagic::getAsBoolean('webform_email_to'));
		$this->view->set('webform_use_captcha', Tactile_AccountMagic::getAsBoolean('webform_use_captcha', 't', 'f'));
		$this->view->set('capture_person', Tactile_AccountMagic::getValue('webform_capture_person'));
		$this->view->set('capture_organisation', Tactile_AccountMagic::getValue('webform_capture_organisation', 'required'));
		$this->view->set('capture_contact', Tactile_AccountMagic::getValue('webform_capture_contact', 'required'));
		$this->view->set('create_opportunity', Tactile_AccountMagic::getAsBoolean('webform_create_opportunity', 't', 't'));
		$this->view->set('create_activity', Tactile_AccountMagic::getAsBoolean('webform_create_activity', 't', 'f'));
		
		$form = array(
			'owner'			=> Tactile_AccountMagic::getValue('webform_owner', Omelette::getPublicIdentity()),
			'email_to'		=> Tactile_AccountMagic::getValue('webform_email_to', ''),
			'success_msg'	=> Tactile_AccountMagic::getValue('webform_success_msg', "Thank you, we'll contact you shortly"),
			'captcha_prompt'=> Tactile_AccountMagic::getValue('webform_captcha_prompt', "Please enter the words above"),
			'redirect_url'	=> Tactile_AccountMagic::getValue('webform_redirect_url', ''),
			'firstname'		=> Tactile_AccountMagic::getValue('webform_label_firstname', 'First Name'),
			'surname'		=> Tactile_AccountMagic::getValue('webform_label_surname', 'Last Name'),
			'organisation'	=> Tactile_AccountMagic::getValue('webform_label_organisation', 'Organisation'),
			'phone'			=> Tactile_AccountMagic::getValue('webform_label_phone', 'Phone Number'),
			'email'			=> Tactile_AccountMagic::getValue('webform_label_email', 'Email Address'),
			'options_label'	=> Tactile_AccountMagic::getValue('webform_label_options', "I'm Interested In"),
			'options'		=> Tactile_AccountMagic::getValue('webform_opp_options', "Option 1, Option 2, Option 3"),
			'query'			=> Tactile_AccountMagic::getValue('webform_label_query', 'Your Message:'),
			'enddate'		=> Tactile_AccountMagic::getValue('webform_opp_enddate_days', 3),
			'assign'		=> Tactile_AccountMagic::getValue('webform_act_assigned_to', Omelette::getPublicIdentity())
		);
		$this->view->set('form', $form);
		
		$user = new Omelette_User();
		$this->view->set('users', $user->getAll());
	}
	
	public function save() {
		$errors = array();
		
		Tactile_AccountMagic::saveChoice('webform_enabled', !empty($this->_data['webform_enabled']) ? 't' : 'f');
		Tactile_AccountMagic::saveChoice('webform_owner', !empty($this->_data['webform_owner']) ? $this->_data['webform_owner'] : Omelette::getPublicIdentity());
		Tactile_AccountMagic::saveChoice('webform_email_to', !empty($this->_data['webform_email_to']) ? $this->_data['webform_email_to'] : '');
		if (empty($this->_data['webform_success_msg'])) {
			$errors[] = 'You must enter a success message';
			$this->_data['webform_success_msg'] = "Thank you, we'll contact you shortly";
		}
		Tactile_AccountMagic::saveChoice('webform_success_msg', !empty($this->_data['webform_success_msg']) ? $this->_data['webform_success_msg'] : '');
		Tactile_AccountMagic::saveChoice('webform_redirect_url', !empty($this->_data['webform_redirect_url']) ? $this->_data['webform_redirect_url'] : '');
		
		Tactile_AccountMagic::saveChoice('webform_use_captcha', !empty($this->_data['webform_use_captcha']) ? 't' : 'f');
		Tactile_AccountMagic::saveChoice('webform_captcha_prompt', !empty($this->_data['webform_captcha_prompt']) ? $this->_data['webform_captcha_prompt'] : '');
		
		Tactile_AccountMagic::saveChoice('webform_label_query', !empty($this->_data['query']) ? $this->_data['query'] : '');
		
		Tactile_AccountMagic::saveChoice('webform_capture_person', !empty($this->_data['capture_person']) ? $this->_data['capture_person'] : '');
		Tactile_AccountMagic::saveChoice('webform_label_firstname', !empty($this->_data['firstname']) ? $this->_data['firstname'] : '');
		Tactile_AccountMagic::saveChoice('webform_label_surname', !empty($this->_data['surname']) ? $this->_data['surname'] : '');
		
		Tactile_AccountMagic::saveChoice('webform_capture_organisation', !empty($this->_data['capture_organisation']) ? $this->_data['capture_organisation'] : '');
		Tactile_AccountMagic::saveChoice('webform_label_organisation', !empty($this->_data['organisation']) ? $this->_data['organisation'] : '');
		
		if (!empty($this->_data['capture_contact']) &&
			($this->_data['capture_organisation'] != 'required' && $this->_data['capture_person'] != 'required')) {
			$errors[] = 'You must require an Organisation or Person to collect contact details';
			$this->_data['capture_contact'] = '';
		}
		Tactile_AccountMagic::saveChoice('webform_capture_contact', !empty($this->_data['capture_contact']) ? $this->_data['capture_contact'] : '');
		Tactile_AccountMagic::saveChoice('webform_label_phone', !empty($this->_data['phone']) ? $this->_data['phone'] : '');
		Tactile_AccountMagic::saveChoice('webform_label_email', !empty($this->_data['email']) ? $this->_data['email'] : '');
		
		Tactile_AccountMagic::saveChoice('webform_create_opportunity', !empty($this->_data['create_opportunity']) ? 't' : 'f');
		Tactile_AccountMagic::saveChoice('webform_label_options', !empty($this->_data['options_label']) ? $this->_data['options_label'] : '');
		Tactile_AccountMagic::saveChoice('webform_opp_options', !empty($this->_data['options']) ? $this->_data['options'] : '');
		
		if (empty($this->_data['capture_organisation']) && empty($this->_data['capture_person']) &&
			empty($this->_data['create_opportunity']) && empty($this->_data['create_activity'])) {
			$errors[] = "You must at least capture an Organisation, Person, Opportunity, or Activity";
			$this->_data['create_activity'] = 'on';
		}
		Tactile_AccountMagic::saveChoice('webform_create_activity', !empty($this->_data['create_activity']) ? 't' : 'f');
		Tactile_AccountMagic::saveChoice('webform_act_assigned_to', !empty($this->_data['assign']) ? $this->_data['assign'] : Omelette::getPublicIdentity());
		
		if (!empty($errors)) {
			Flash::Instance()->addErrors($errors);
		} else {
			Flash::Instance()->addMessage('Web Form options saved successfully');
		}
		sendTo('webform');
	}
	
}
