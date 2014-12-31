<?php

class Tactile_PermissionsController extends Controller {
	
	public function index() {
		$this->setTemplateName('index');
		$this->view->set('pref_view', 'feature_control');
		
		$this->view->set('permission_import_enabled', Tactile_AccountMagic::getAsBoolean('permission_import_enabled', 't', 't'));
		$this->view->set('permission_export_enabled', Tactile_AccountMagic::getAsBoolean('permission_export_enabled', 't', 't'));
		
		// Default permissions
		$users = new Omelette_UserCollection();
		$sh = new SearchHandler($users, false);
		$sh->extract();
		//$sh->addConstraint(new Constraint('enabled', '=', 'true'));
		$sh->setOrderby(array('is_admin', 'username'));
		$users->load($sh);
		$this->view->set('users', $users);
	}
	
	public function default_permissions() {
		$this->index();
		$this->view->set('pref_view', 'default_permissions');
	}
	
	public function save() {
		Tactile_AccountMagic::saveChoice('permission_import_enabled', !empty($this->_data['permission_import_enabled']));
		Tactile_AccountMagic::saveChoice('permission_export_enabled', !empty($this->_data['permission_export_enabled']));
		
		Flash::Instance()->addMessage('Permissions saved');
		
		sendTo('permissions');
	}
	
	public function save_defaults() {
		$username = !empty($this->_data['username']) ? $this->_data['username'] . '//' . Omelette::getUserspace() : '';
		$user = new Omelette_User();
		if (empty($username) || FALSE === $user->load($username)) {
			Flash::Instance()->addError('Failed to load user');
			sendTo('permissions/default_permissions');
			return;
		}
		Omelette_Magic::saveChoice('permissions_fixed', !empty($this->_data['fixed']), $username);
		
		$sharing = !empty($this->_data['Sharing']) ? $this->_data['Sharing'] : array('read' => 'everyone', 'write' => 'everyone');
		$levels = array('read', 'write');
		foreach ($levels as $level) {
			if (!isset($sharing[$level])) {
				continue;
			}
			switch ($sharing[$level]) {
				case 'everyone':
				case 'private':
					Omelette_Magic::saveChoice($level.'_permissions', $sharing[$level], $username);
					break;
				default: {
					if (is_array($sharing[$level])) {
						$roles = array();
						foreach ($sharing[$level] as $role_id) {
							$role = new Omelette_Role();
							if (FALSE !== $role->load($role_id)) {
								$roles[] = $role_id;
							}
						}
						Omelette_Magic::saveChoice($level.'_permissions', implode(',', $roles), $username);
					}
					break;
				}
			} 
		}
		
		Flash::Instance()->addMessage('Permissions saved');
		sendTo('permissions/default_permissions');
	}
	
}
