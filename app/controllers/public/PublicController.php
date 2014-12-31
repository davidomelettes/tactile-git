<?php

class PublicController extends Controller {
	
	function __construct($module, $view) {
		parent::__construct($module, $view);
	}
	
	public function index() {
		sendTo('/');
		return;
	}
	
	public function icalendar() {
		// When was the calendar last updated?
		$last_update = Tactile_Activity::getMostRecentlyModifiedDate(EGS::getUsername());
		if ($last_update) {
			$last_modified_ts = strtotime($last_update);
		} else {
			$last_modified_ts = time();
		}
		
		if ($last_modified_ts === FALSE) {
			$last_modified = date('r');
		} else {
			$last_modified = date('r', $last_modified_ts);
		}
		
		// Check for since-modified request
		if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			$modified_since_request_ts = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
			if (FALSE !== $modified_since_request_ts && FALSE !== $last_modified_ts) {
				if ($last_modified_ts <= $modified_since_request_ts) {
					// Calendar hasn't been updated since requested time
					$this->view->setHeader("Last-Modified: $last_modified");
					$this->view->setHeader('Status: 304 Not Modified');
					$this->view->setContentType('text/calendar');
					$this->view->set('layout', 'empty');
					return;
				}
			}
		}
		
		// Include our iCalendar classes
		Autoloader::Instance()->addPath(FILE_ROOT . 'omelette/lib/icalendar/');
		
		$activities = new Tactile_ActivityCollection();
		$sh = new SearchHandler($activities,false);
		$sh->addConstraint(new Constraint('act.assigned_to','=',EGS::getUsername()));
		$sh->setOrderBy('act.date','ASC');
		Controller::index($activities,$sh);
		$this->setTemplateName('icalendar');
		
		$cal = new VCalendar();
		
		foreach ($activities as $activity) {
			/* @var $activity Tactile_Activity */
			$item = $activity->toVCalendarItem();
			
			$cal->addItem($item);
		}
		
		$user = DataObject::Construct('User');
		$user->load(EGS::getUsername());
		$owner_name = $user->person;
		
		$title = $owner_name . "'" . (preg_match('/s$/', $owner_name) ? '' : 's') . " Tactile Activities";
		
		$this->view->set('icalendar', $cal->toString($title));
		
		$this->view->setHeader("Last-Modified: $last_modified");
		$this->view->setContentType('text/calendar');
		
		$this->view->set('layout', 'blank');
	}
	
	protected function _loadItemTimeline() {
		$timeline = new Timeline();
		$cc = new ConstraintChain();
		
		$act = new Tactile_Activity();
		$opp = new Tactile_Opportunity();
		$per = new Tactile_Person();
		$org = new Tactile_Organisation();
		if (!empty($this->_data['activity_id']) && FALSE !== $act->load($this->_data['activity_id'])) {
			$cc->add(new Constraint('t.activity_id', '=', $act->id));
			$this->view->set('rss_title', $act->name . ' - Tactile CRM');
		} elseif (!empty($this->_data['opportunity_id']) && FALSE !== $opp->load($this->_data['opportunity_id'])) {
			$cc->add(new Constraint('t.opportunity_id', '=', $opp->id));
			$this->view->set('rss_title', $opp->name . ' - Tactile CRM');
		} elseif (!empty($this->_data['person_id']) && FALSE !== $per->load($this->_data['person_id'])) {
			$cc->add(new Constraint('t.person_id', '=', $per->id));
			$this->view->set('rss_title', $per->name . ' - Tactile CRM');
		} elseif (!empty($this->_data['organisation_id']) && FALSE !== $org->load($this->_data['organisation_id'])) {
			$cc->add(new Constraint('t.organisation_id', '=', $org->id));
			$this->view->set('rss_title', $org->name . ' - Tactile CRM');
		} else {
			return false;
		}
		
		$timeline->addType('note');
		$timeline->addType('email');
		$timeline->addType('flag');
		$timeline->addType('s3file');
		$timeline->addType('opportunity');
		$timeline->addType('new_activity');
		$timeline->addType('completed_activity');
		$timeline->addType('overdue_activity');
		
		$timeline->load($cc);
		return $timeline;
	}
	
	protected function _loadNotesEmails() {
		$timeline = new Timeline();
		$timeline->per_page = 100;
		$cc = new ConstraintChain();
		$cc->add(new Constraint('"when"', '>', date('Y-m-d', strtotime('-30 days'))));
		
		$timeline->addType('note');
		$timeline->addType('email');
		
		$timeline->load($cc);
		return $timeline;
	}
	
	protected function _loadNotesEmailsActivities() {
		$timeline = new Timeline();
		$timeline->per_page = 100;
		$cc = new ConstraintChain();
		$cc->add(new Constraint('"when"', '>', date('Y-m-d', strtotime('-30 days'))));
		
		$timeline->addType('note');
		$timeline->addType('email');
		$timeline->addType('completed_activity');
		
		$timeline->load($cc);
		return $timeline;
	}
	
	protected function _loadCustomTimeline() {
		$timeline = new Timeline();
		$timeline->per_page = 100;
		$cc = new ConstraintChain();
		$cc->add(new Constraint('"when"', '>', date('Y-m-d', strtotime('-30 days'))));
		
		$cc_mine = new ConstraintChain();
		$cc_mine->add(new Constraint('owner', '=', EGS::getUsername()));
		$cc_mine->add(new Constraint('assigned_to', '=', EGS::getUsername()), 'OR');
		
		$timeline_prefs = TimelinePreference::getAll(EGS::getUsername());
		foreach ($timeline_prefs as $item => $types) {
			foreach ($types as $type => $value) {
				switch ($value) {
					// Don't bother doing anything unless the value is 'all' or 'mine'
					case 'all':
					case 'mine':
						switch ($item) {
							case 'activities':
								switch ($type) {
									case 'new':
										$timeline->addType('new_activity', 'all' == $value);
										break;
									case 'completed':
										$timeline->addType('completed_activity', 'all' == $value);
										break;
									case 'overdue':
										$timeline->addType('overdue_activity', 'all' == $value);
										break;
								}
								break;
								
							case 'opportunities':
								$timeline->addType('opportunity', 'all' == $value);
								break;
								
							case 'notes':
								$timeline->addType('note', 'all' == $value);
								break;
								
							case 'emails':
								$timeline->addType('email', 'all' == $value);
								break;
								
							case 'files':
								$timeline->addType('s3file', 'all' == $value);
								break;
						}
						break;
				}
			}
		}
		
		$timeline->load($cc);
		return $timeline;
	}
	
	public function timeline() {
		header('Content-Type: application/rss+xml');
		$this->view->set('layout', 'blank');
		$this->setTemplateName('timeline_rss');
		
		if (!empty($this->_data['activity_id']) || !empty($this->_data['opportunity_id']) || !empty($this->_data['person_id']) || !empty($this->_data['organisation_id'])) {
			$timeline = $this->_loadItemTimeline();
		} else {
			$restriction = Omelette_Magic::getValue('dashboard_timeline_restriction', EGS::getUsername(), 'notes_emails_acts');
			$this->view->set('restriction', $restriction);
			switch ($restriction) {
				case 'custom':
					$timeline = $this->_loadCustomTimeline();
					break;
				case 'notes_emails_acts':
					$timeline = $this->_loadNotesEmailsActivities();
					break;
				case 'notes_emails':
				default:
					$timeline = $this->_loadNotesEmails();
					break;
			}
		}
		
		$this->view->set('activity_timeline', $timeline);
	}
	
	public function form_html() {
		$this->form();
		$this->view->set('layout', 'webform');
		$this->view->set('template', 'webform');
	}
	
	public function form() {
		$this->setTemplateName('form');
		$this->view->set('layout', 'blank');
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		$db = DB::Instance();
		
		$recaptcha_private_key = RECAPTCHA_PRIVATE_KEY;
		$recaptcha_public_key = RECAPTCHA_PUBLIC_KEY;
		$this->view->set('recaptcha_public_key', $recaptcha_public_key);
		
		$tag = "Web Form";
		
		// Enabled for this account?
		$enabled = Tactile_AccountMagic::getAsBoolean('webform_enabled', 't', 'f'); 
		if (!$enabled) {
			sendTo();
			return;
		}
		$this->view->set('webform_enabled', $enabled);
		
		// Load form settings
		$form = array(
			'captcha_prompt'=> Tactile_AccountMagic::getValue('webform_captcha_prompt', 'Please enter the words above'),
			'firstname'		=> Tactile_AccountMagic::getValue('webform_label_firstname', 'First Name'),
			'surname'		=> Tactile_AccountMagic::getValue('webform_label_surname', 'Last Name'),
			'organisation'	=> Tactile_AccountMagic::getValue('webform_label_organisation', 'Organisation'),
			'phone'			=> Tactile_AccountMagic::getValue('webform_label_phone', 'Phone Number'),
			'email'			=> Tactile_AccountMagic::getValue('webform_label_email', 'Email Address'),
			'query'			=> Tactile_AccountMagic::getValue('webform_label_query', 'Your Message:'),
			'enddate'		=> Tactile_AccountMagic::getValue('webform_opp_enddate_days', 3),
			'options_label'	=> Tactile_AccountMagic::getValue('webform_label_options', ""),
			'options'		=> Tactile_AccountMagic::getValue('webform_opp_options', "")
		);
		$form['options'] = array_map('trim', explode(',', $form['options']));
		$this->view->set('form', $form);
		$this->view->set('has_options', (!empty($form['options_label']) && !empty($form['options'])));
		
		$webform_use_captcha = Tactile_AccountMagic::getAsBoolean('webform_use_captcha', 't', 'f');
		$this->view->set('webform_use_captcha', $webform_use_captcha);
		$capture_person = Tactile_AccountMagic::getValue('webform_capture_person');
		$this->view->set('capture_person', $capture_person);
		$capture_organisation = Tactile_AccountMagic::getValue('webform_capture_organisation', 'required');
		$this->view->set('capture_organisation', $capture_organisation);
		$capture_contact = Tactile_AccountMagic::getValue('webform_capture_contact', 'required');
		$this->view->set('capture_contact', $capture_contact);
		$create_opportunity = Tactile_AccountMagic::getAsBoolean('webform_create_opportunity', 't', 't');
		$this->view->set('create_opportunity', $create_opportunity);
		$query = "SELECT id FROM opportunitystatus WHERE open AND usercompanyid = "
			. $db->qstr(EGS::getCompanyId()) . " ORDER BY position ASC LIMIT 1";
		$status_id = $db->getOne($query);
		if ($status_id !== FALSE) {
			$this->view->set('status_id', $status_id);
		}
		$create_activity = Tactile_AccountMagic::getAsBoolean('webform_create_activity', 't', 'f');
		$this->view->set('create_activity', $create_activity);
		
		// Was this a form submission?
		if ($this->is_post) {
			// Check id
			if (empty($this->_data['id']) || $this->_data['id'] !== $account->id) {
				sendTo();
				return;
			}
			$db->startTrans();
			$errors = array();
			
			// Set user for ownership
			$old_username = EGS::getUsername();
			EGS::setUsername(Tactile_AccountMagic::getValue('webform_owner', Omelette::getPublicIdentity()));
			
			$saver = new ModelSaver();
			
			// Save Organisation
			if (!empty($capture_organisation)) {
				$org_errors = array();
				$org_data = (!empty($this->_data['Organisation']) && is_array($this->_data['Organisation'])) ? $this->_data['Organisation'] : array();
				if (!empty($org_data['name'])) {
					$org = $saver->save($org_data, 'Tactile_Organisation', $org_errors);
					if (FALSE !== $org) {
						// Save role
						$sharing = array('read'=>'everyone', 'write'=>'everyone');
						$normalized_roles = Omelette_OrganisationRoles::normalize($sharing);
						Omelette_OrganisationRoles::AssignWriteAccess(array($org->id), $sharing['write']);
						
						// Add tag
						$ti = new TaggedItem($org);
						$ti->addTag($tag);
					}
				} else {
					$org = FALSE;
				}
			}
			
			// Save Person
			if (!empty($capture_person)) {
				$person_errors = array();
				$person_data = (!empty($this->_data['Person']) && is_array($this->_data['Person'])) ? $this->_data['Person'] : array();
				if (!empty($person_data['firstname']) || !empty($person_data['surname'])) {
					if (isset($org) && $org !== FALSE) {
						$person_data['organisation_id'] = $org->id;
					}
					$person = $saver->save($person_data, 'Tactile_Person', $person_errors);
					if (FALSE !== $person) {
						$ti = new TaggedItem($person);
						$ti->addTag($tag);
					}
				} else {
					$person = FALSE;
				}
			}
			
			// Save contact methods
			if (!empty($capture_contact)) {
				$phone_errors = array();
				$email_errors = array();
				$phone_data = (!empty($this->_data['phone']) && is_array($this->_data['phone'])) ? $this->_data['phone'] : array();
				$email_data = (!empty($this->_data['email']) && is_array($this->_data['email'])) ? $this->_data['email'] : array();
				if (isset($person) && FALSE !== $person) {
					// Attach to Person
					if (!empty($phone_data)) {
						$phone_data['type'] = 'T';
						$phone_data['name'] = 'Main';
						$phone_data['person_id'] = $person->id;
						$phone = DataObject::Factory($phone_data, $phone_errors, 'Tactile_Personcontactmethod');
					}
					if (!empty($email_data)) {
						$email_data['type'] = 'E';
						$email_data['name'] = 'Main';
						$email_data['person_id'] = $person->id;
						$email = DataObject::Factory($email_data, $email_errors, 'Tactile_Personcontactmethod');
					}
				} elseif (isset($org) && FALSE !== $org) {
					// Attach to Organisation
					if (!empty($phone_data)) {
						$phone_data['type'] = 'T';
						$phone_data['name'] = 'Main';
						$phone_data['organisation_id'] = $org->id;
						$phone = DataObject::Factory($phone_data, $phone_errors, 'Tactile_Organisationcontactmethod');
					}
					if (!empty($email_data)) {
						$email_data['type'] = 'E';
						$email_data['name'] = 'Main';
						$email_data['organisation_id'] = $org->id;
						$email = DataObject::Factory($email_data, $email_errors, 'Tactile_Organisationcontactmethod');
					}
				}
				if (isset($phone) && FALSE !== $phone) {
					$phone->save();
				}
				if (isset($email) && FALSE !== $email) {
					$email->save();
				}
			}
			
			// Save Opportunity
			if ($create_opportunity) {
				$opp_errors = array();
				$opp_data = (!empty($this->_data['Opportunity']) && is_array($this->_data['Opportunity'])) ? $this->_data['Opportunity'] : array();
				$opp_data['status_id'] = $status_id;
				$opp_data['name'] = !empty($this->_data['option']) ? $this->_data['option'] : 'Web Form Contact';
				if (isset($org) && $org !== FALSE) {
					$opp_data['organisation_id'] = $org->id;
				}
				if (isset($person) && $person !== FALSE) {
					$opp_data['person_id'] = $person->id;
				}
				$days = !empty($this->_data['enddate']) ? (int) $this->_data['enddate'] : 3; 
				$opp_data['enddate'] = date('Y-m-d', strtotime("+$days days"));
				$opp = $saver->save($opp_data, 'Tactile_Opportunity', $opp_errors);
				if (FALSE !== $opp) {
					$ti = new TaggedItem($opp);
					$ti->addTag($tag);
				}
			}
			
			// Save Activity
			if ($create_activity) {
				$act_errors = array();
				$act_data = (!empty($this->_data['Activity']) && is_array($this->_data['Activity'])) ? $this->_data['Activity'] : array();
				$act_data['name'] = 'Respond to Web Form contact';
				$act_data['later'] = true;
				$act_data['assigned_to'] = Tactile_AccountMagic::getValue('webform_act_assigned_to');
				if (isset($org) && $org !== FALSE) {
					$act_data['organisation_id'] = $org->id;
				}
				if (isset($person) && $person !== FALSE) {
					$act_data['person_id'] = $person->id;
				}
				if (isset($opp) && $opp !== FALSE) {
					$act_data['opportunity_id'] = $opp->id;
				}
				$act = $saver->save($act_data, 'Tactile_Activity', $act_errors);
				if (FALSE !== $act) {
					$ti = new TaggedItem($act);
					$ti->addTag($tag);
				}
			}
			
			// Check CAPTCHA
			if ($webform_use_captcha) {
				$recaptcha_params = array (
					'privatekey'	=> $recaptcha_private_key,
					'remoteip'		=> $_SERVER['REMOTE_ADDR'],
					'challenge'		=> $this->_data['recaptcha_challenge_field'],
					'response'		=> $this->_data['recaptcha_response_field']
				);
				$http_options = array(
					'method'		=> 'POST',
					'timeout'		=> '5',
					'content'		=> http_build_query($recaptcha_params)
				);
				$http_context = stream_context_create(array('http' => $http_options));
				$response = @file_get_contents('http://api-verify.recaptcha.net/verify', false, $http_context);
				if (!empty($response)) {
					$lines = preg_split('/\n/', $response);
					if (empty($lines[0]) || $lines[0] !== 'true') {
						$errors[] = 'Anti-spam entry incorrect';
					}
				} else {
					$errors[] = 'Error verifying CAPTCHA';
				}
			}
			
			if ((isset($org) && FALSE !== $org) ||
				(isset($person) && FALSE !== $person) ||
				(isset($opp) && FALSE !== $opp) ||
				(isset($act) && FALSE !== $act)) {
				// We managed to save something
				if ($capture_organisation == 'required' && empty($org)) {
					$errors[] = $form['organisation'] . ' is required';
				}
				if ($capture_person == 'required' && empty($person)) {
					$errors[] = $form['firstname'] . ' and ' . $form['surname'] . ' are required';
				}
				if ($capture_contact == 'required' && empty($email)) {
					$errors[] = $form['email'] . ' is required';
				}
				if ($capture_contact == 'required' && empty($phone)) {
					$errors[] = $form['phone'] . ' is required';
				}
				if (empty($this->_data['query'])) {
					$errors[] = 'Please enter a message';
				}
				
				if (empty($errors)) {
					// Save Flag
					$flag_data = array(
						'organisation_id'	=> (isset($org) && $org !== FALSE) ? $org->id : null,
						'person_id'			=> (isset($person) && $person !== FALSE) ? $person->id : null,
						'opportunity_id'	=> (isset($opp) && $opp !== FALSE) ? $opp->id : null,
						'activity_id'		=> (isset($act) && $act !== FALSE) ? $act->id : null,
						'title'				=> 'Created via Web Form',
						'owner'				=> EGS::getUsername()
					);
					$flag = $saver->save($flag_data, 'Flag', $flag_errors);
					if (!empty($flag_errors)) {
						$errors[] = 'There was a problem with your submission';
					}
					
					// Save Note
					$note_data = array(
						'organisation_id'	=> (isset($org) && $org !== FALSE) ? $org->id : null,
						'person_id'			=> (isset($person) && $person !== FALSE) ? $person->id : null,
						'opportunity_id'	=> (isset($opp) && $opp !== FALSE) ? $opp->id : null,
						'activity_id'		=> (isset($act) && $act !== FALSE) ? $act->id : null,
						'title'				=> 'Web Form Details',
						'note'				=> !empty($this->_data['query']) ? $this->_data['query'] : '',
						'owner'				=> EGS::getUsername()
					);
					$note = $saver->save($note_data, 'Note', $note_errors);
					if (!empty($note_errors)) {
						$errors[] = 'There was a problem with your submission';
					}
				}
					
				if (empty($errors)) {
					// We are totally error free! Complete the transaction!
					$transaction_success = $db->CompleteTrans();
					if ($transaction_success) {
						// Send notification?
						$email = Tactile_AccountMagic::getValue('webform_email_to');
						if (!empty($email)) {
							$subject = 'New Tactile CRM Web Form Submission';
							
							$contact = false;
							$message = !empty($this->_data['query']) ? $this->_data['query'] : false;
							
							if (!empty($org)) {
								$controller = 'organisations';
								$id = $org->id;
								$subject .= ': ' . $org->name;
								$contact = $org->name;
							} elseif (!empty($person)) {
								$controller = 'people';
								$id = $person->id;
								$subject .= ': ' . $person->name;
								$contact = $person->name;
							} elseif (!empty($opp)) {
								$controller = 'opportunities';
								$id = $opp->id;
							} elseif (!empty($act)) {
								$controller = 'activities';
								$id = $act->id;
							} else {
								throw new Exception("Don't know where to send user!");
							}
							$link = "http://" . Omelette::getUserSpace() . '.tactilecrm.com/' . $controller . '/view/' . $id;
							
							$mail = new Omelette_Mail('webform_notification');
							$mail->getMail()->addTo($email);
							$mail->getMail()->setFrom(TACTILE_EMAIL_FROM, TACTILE_EMAIL_NAME);
							$mail->getMail()->setSubject($subject);
							$mail->getView()->set('link', $link);
							$mail->getView()->set('contact', $contact);
							$mail->getView()->set('message', $message);
							try {
								$mail->send();
							} catch (Zend_Mail_Transport_Exception $e) {
								// Failed to send email
							}
						}
					} else {
						$this->logger->info('Web form submission reached success stage, but transaction failed to complete: ' . $db->ErrorMsg());
						$this->logger->info('Form submission was: ' . print_r($this->_data, 1));
					}
				} else {
					// Something saved, but not enough
					$db->FailTrans();
					$db->CompleteTrans();
				}
				
			} else {
				// Nothing was saved!
				$db->FailTrans();
				$db->CompleteTrans();
			}
			
			// Don't want to display all the errors
			$flash = Flash::Instance();
			if ($flash->hasErrors()) {
				$flash->clear();
			} else {
				// Success!
				$flash->clear();
			}
			if (empty($errors)) {
				$msg = Tactile_AccountMagic::getValue('webform_success_msg', "Thank you, we'll contact you shortly");
				$flash->addMessage($msg);
			} else {
				$flash->addErrors($errors);
				// Remember details
				$this->view->set('data', $this->_data);
			}
			
			// Return previous user
			EGS::setUsername($old_username);
			
		} else {
			// Display the form
		}
	}
	
	public function fourohfour() {
		$this->view->set('layout', 'blank');
		$this->view->setHeader('Status: 404 Not Found');
	}
	
	public function fivehundred() {
	}
	
}
