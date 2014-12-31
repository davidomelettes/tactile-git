<?php
require_once FILE_ROOT.'egs/models/CompanyLookups.php';
/**
 * Responsible for the displaying, creating and editing of the various user-configurable values used throughout Tactile
 * 
 * @author gj
 */
class Tactile_SetupController extends Controller {
	
	/**
	 * The multi-d array, breaking down the options by what they're attached to
	 *
	 * @var Array
	 */
	private $groups = array(
		'companies' => array(
			'status'		=> 'CompanyStatus',
			'source'		=> 'CompanySource',
			'classification'=> 'CompanyClassification',
			'rating'		=> 'CompanyRating',
			'industry'		=> 'CompanyIndustry',
			'type'			=> 'CompanyType'
		),
		'opportunities' => array(
			'status'		=> 'Opportunitystatus',
			'source'		=> 'Opportunitysource',
			'type'			=> 'Opportunitytype'
		),
		'activities' => array(
			'type'			=> 'Activitytype'
		)
	);
	
	/**
	 * Displays a 'tree' of the various options.
	 * Initially, all options are shown, by what they're attached to. Clicking one will 'open' it, and the list will show
	 * all current values with 'edit' links next to them, as well as a line for 'new' (or just that line if there aren't any) with a save button.
	 *
	 */
	function index() {
		$this->view->set('groups',$this->groups);
		if (!empty($this->_data['group']) && !empty($this->_data['option'])) {
			$classname = $this->groups[$this->_data['group']][$this->_data['option']].'Collection';
			$collection = new $classname;
			$sh = new SearchHandler($collection, false);
			$sh->extract();
			$sh->perpage = 0;
			$collection->load($sh);
			
			$this->view->set('values', $collection);
			$this->view->set('selected_group', $this->_data['group']);
			$this->view->set('selected_option', $this->_data['option']);
		}	
	}
	
	/**
	 * Saves a config-option
	 * uses the 'group' and 'option' from the URL to determine what class we're saving.
	 * Redirects back to the 'edit' view when editing, rather than the 'closed' tree
	 *
	 */
	function save() {
		if (!empty($this->_data['group']) && !empty($this->_data['option'])) {
			$classname = $this->groups[$this->_data['group']][$this->_data['option']];
			
			$errors = array();
			if (!empty($this->_data[$this->_data['group']][$this->_data['option']])) {
				foreach ($this->_data[$this->_data['group']][$this->_data['option']] as $index => $data) {
					if ($this->_data['group'] == 'opportunities' && $this->_data['option'] == 'status') {
						if (!isset($data['open'])) {
							$data['open'] = 'off';
						}
						if (!isset($data['won'])) {
							$data['won'] = 'off';
						}
					}
					$model = DataObject::Factory($data, $errors, $classname);
					if ($model !== false && $model->save() !== false) {
						Flash::Instance()->addMessage(ucfirst($this->_data['option']) . ' group saved sucessfully');
					} else {
						Flash::Instance()->addErrors($errors);
					}
					sendTo('setup', null, null, array('group' => $this->_data['group'], 'option' => $this->_data['option']));
				}
			}
		} else {
			sendTo('setup');
		}
	}
	
	/**
	 * Deletes a config-option
	 *
	 */
	function delete () {
		if (!empty($this->_data['group']) && !empty($this->_data['option'])) {
			$classname = $this->groups[$this->_data['group']][$this->_data['option']];
			$errors = array();
			$model = DataObject::Factory($this->_data, $errors, $classname);
			
			if ($model !== FALSE && $this->_data['group'] == 'opportunities' && in_array($this->_data['option'], array('status', 'type'))) {
				$option = $this->_data['option'];
				$opps = new Tactile_OpportunityCollection();
				$sh = new SearchHandler($opps, false);
				$sh->extractPaging();
				$sh->addConstraint(new Constraint('opp.'.$option.'_id', '=', $model->id));
				$opps->load($sh);
				
				if ($opps->num_records > 0) {
					$this->view->set('option_options', $model->getAll());
					$this->view->set('opps_count', $opps->num_records);
					$this->view->set('option_type', $option);
					$this->view->set('option_name', $model->name);
					$this->view->set('option_id', $model->id);
					return;
				}
			} 
			
			if ($model !== false && $model->delete($this->_data['id']) !== false) {
				$status = 'success';
				Flash::Instance()->addMessage(ucfirst($this->_data['option']) . ' deleted successfully');
			} else {
				$status = 'failure';
				Flash::Instance()->addErrors($errors);
			}
			$this->view->set('id', $this->_data['id']);
		}
		sendTo('setup', null, null, array('group'=>$this->_data['group'], 'option'=>$this->_data['option']));
	}
	
	function process_delete() {
		if (!empty($this->_data['group']) && !empty($this->_data['option']) && !empty($this->_data['new_option'])) {
			$classname = $this->groups[$this->_data['group']][$this->_data['option']];
			$model = new $classname;
			$new_model = new $classname;
			if (FALSE !== $model->load($this->_data['id']) && FALSE !== $new_model->load($this->_data['new_option'])) {
				// Move any opps on old option to new option
				$db = DB::Instance();
				$db->startTrans();
				$sql = "UPDATE opportunities SET " . $this->_data['option'] ."_id = " . $db->qstr($new_model->id) . " WHERE usercompanyid = " . $db->qstr(EGS::getCompanyId()) . " AND " . $this->_data['option'] ."_id = " . $db->qstr($model->id);
				$db->execute($sql);
				
				if ($model->delete($this->_data['id']) !== false) {
					Flash::Instance()->addMessage(ucfirst($this->_data['option']) . ' deleted successfully');
				} else {
					$db->failTrans();
					Flash::Instance()->addMessage('There was a problem deleting that ' . $this->_data['group']);
				}
				$db->completeTrans();
			} else {
				Flash::Instance()->addMessage($this->_data['group'] . ' not found');
			}
		} else {
			Flash::Instance()->addMessage('There was a problem deleting that option');
		}
		sendTo('setup', null, null, array('group'=>$this->_data['group'], 'option'=>$this->_data['option']));
	}
	
	function email() {
		$this->setTemplateName('email');
		$this->view->set('pref_view', 'shared_addresses');
		
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		$this->view->set('account', $account);
		
		$enabled = $account->is_free() ? true : Tactile_AccountMagic::getAsBoolean('tactilemail_enabled', 't', 't');
		$this->view->set('tactilemail_enabled', $enabled);
		$this->view->set('tactilemail_user_addresses', Tactile_AccountMagic::getAsBoolean('tactilemail_user_addresses', 't', 't'));
		
		$templates = new EmailTemplateCollection();
		$sh = new SearchHandler($templates, false);
		$sh->extract();
		$templates->load($sh);
		$this->view->set('templates', $templates);
		
		$account_role = Omelette::getUserSpaceRole();
		$emails = new TactileEmailAddressCollection();
		$sh = new SearchHandler($emails, false);
		$sh->extract();
		$sh->addConstraint(new Constraint('role_id', '=', $account_role->id));
		$emails->load($sh);
		$this->view->set('emails', $emails);
	}
	
	function email_templates() {
		$this->email();
		$this->view->set('pref_view', 'email_templates');
	}
	
	function email_shared_addresses() {
		$this->email();
		$this->view->set('pref_view', 'shared_addresses');
	}
	
	function email_save() {
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		
		if ($account->is_free()){
			$enabled = true;
		} else {
			$enabled = isset($this->_data['tactilemail_enabled']) ? true : false;
		}
		$user_addresses = isset($this->_data['tactilemail_user_addresses']) ? true : false;
		
		Tactile_AccountMagic::saveChoice('tactilemail_enabled', $enabled);
		Tactile_AccountMagic::saveChoice('tactilemail_user_addresses', $user_addresses);
		
		Flash::Instance()->addMessage('TactileMail preferences saved');
		sendTo('setup', 'email');
	}
	
}
