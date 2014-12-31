<?php
class Tactile_User extends Omelette_User {
	
	public function getEntanetExtension() {
		$db = DB::Instance();
		$query = 'SELECT extension FROM entanet_extensions WHERE username = ' . $db->qstr($this->username . '//' . Omelette::getUserSpace());
		$ext = $db->GetOne($query);
		return $ext;
	}
	
	public function hasLoggedInBefore() {
		$value = $this->last_login;
		return !empty($value);
	}
	
	function getReadString() {
		return 'by Admins only';
	}
	
	function getWriteString() {
		return 'by Admins only';
	}
	
	function getPerson() {
		$person = new Tactile_Person();
		return $person->load($this->person_id);
	}
}
