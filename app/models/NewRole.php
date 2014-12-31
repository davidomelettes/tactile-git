<?php
/**
 * A basic enough representation of the roles table for account-creation
 * @author gj
 */
class NewRole extends DataObject {
	
	public function __construct() {
		parent::__construct('roles');
	}
	
	/**
	 * Creates the 'everyone' role for the account, as well as the 'user-role' for the initial user
	 *
	 * @param TactileAccount $account
	 * @param NewCompany $company
	 * @param NewUser $user
	 * 
	 * @return void
	 */
	public static function createInitialRoles(TactileAccount $account, NewOrganisation $organisation, NewUser $user) {
		$space = '//'.$account->site_address;
		
		//'everyone'
		$everyone = new Role();
		$everyone->name = $space;
		$everyone->save();
		$organisation->giveReadAccess($everyone);
		
		//'user'
		$role = new Role();
		$role->name = $user->username;
		$role->save();
		$organisation->giveEditAccess($role);
		
		$user->giveRoles($everyone,$role);
	}
	
}
