<?php
/**
 *
 * @author gj
 */
class NewPerson extends DataObject {
	
	public function __construct() {
		parent::__construct('people');
	}
	
	public static function create(TactileAccount $account) {
		$person_data = array(
			'firstname'		=> $account->firstname,
			'surname'		=> $account->surname,
			'language_code'	=> 'EN'
		);
		$errors = array();
		$person = DataObject::Factory($person_data,$errors,'NewPerson');
		if($person===false || $person->save()===false) {
			return false;
		}
		$phone_data = array(
			'contact'=>$account->email,
			'person_id'=>$person->id,
			'type'=>'E',
			'main'=>true,
			'name'=>'Main'
		);
		$email = DataObject::Factory($phone_data,$errors,'NewEmail');
		if($email===false || $email->save()===false) {
			return false;
		}
		
		return $person;
	}	
	
	public function setOwner(NewUser $user) {
		$this->owner = $user->username;
		$this->alteredby = $user->username;
		return $this->save();
	}
}
?>