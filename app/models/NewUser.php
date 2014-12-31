<?php
/**
 *
 * @author gj
 */
class NewUser extends DataObject {
	
	public function __construct() {
		parent::__construct('users');
		$this->getField('password')->blockValidator('PasswordValidator');
	}
	
	/**
	 * Create the initial user (owner) for a new Sys-Company
	 * 
	 * @param TactileAccount $account
	 * @param NewPerson $person
	 * @return NewUser
	 */	
	public static function create(TactileAccount $account, NewPerson $person) {
		$google_domain = $account->google_apps_domain;
		$user_data = array(
			'username'=>$account->username.'//'.$account->site_address,
			'password'=>$account->password,
			'person_id'=>$person->id,
			'is_admin'=>true,
			'enabled'=>true,
			'terms_agreed'=>date('d/m/Y'),
			'dropboxkey'=>Omelette_User::generateDropBoxKey(),
			'api_token'=> Omelette_User::generateApiToken(),
			'webkey' => Omelette_User::generateWebKey(),
			'google_apps_email'=>(!empty($google_domain) ? $account->email : ''),
			'openid'=>$account->openid
		);
		$errors = array();
		$user = DataObject::Factory($user_data,$errors,'NewUser');
		if($user === false || $user->save() === false) {
			return false;
		}
		return $user;
	}
	
	/**
	 * Put the user into one or more roles, takes any number of roles
	 * 
	 * @param Role $role
	 * @return void
	 */
	public function giveRoles() {
		$args = func_get_args();
		foreach($args as $role) {
			$user_role = new UserRole();
			$user_role->username = $this->username;
			$user_role->roleid = $role->id;
			$user_role->save();
		}
	}
	
}
?>