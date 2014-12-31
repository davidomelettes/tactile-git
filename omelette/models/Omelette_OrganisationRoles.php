<?php
/**
 * Extends the model to make the keyword-roles ('everyone' and 'private' do what they're supposed to
 */
class Omelette_OrganisationRoles extends OrganisationRoles {
	
	/**
	 * Assigns read access to the companies provides to the roles provided. Also accepts 'everyone' and 'private' in places of an array of role_ids
	 * @param Array $organisation_ids
	 * @param mixed
	 */
	public static function AssignReadAccess(Array $organisation_ids,$role_ids) {
		return self::AssignAccess('read',$organisation_ids,$role_ids);	
	}
	/**
	 * Assigns write access to the companies provides to the roles provided. Also accepts 'everyone' and 'private' in places of an array of role_ids
	 * @param Array $organisation_ids
	 * @param mixed
	 */
	public static function AssignWriteAccess(Array $organisation_ids,$role_ids) {
		return self::AssignAccess('write',$organisation_ids,$role_ids);	
	}
	
	public static function AssignAccess($type,Array $organisation_ids, $role_ids) {
		switch($role_ids) {
			case 'everyone':
				$role_ids = array(Omelette::getUserSpaceRole()->id);
				break;
			case 'private':
				$role_ids = array('roleid'=>Omelette_User::getUserRole(EGS::getUsername())->id);
				break;
		}
		return parent::AssignAccess($type,$organisation_ids,$role_ids);
	}
	
	public static function normalize($sharing) {
		$rows = array();
		$write = $sharing['write'];
		$read = $sharing['read'];
		foreach($sharing as $type=>$ids) {
			switch($ids) {
				case 'everyone':
					$$type = array(Omelette::getUserSpaceRole()->id);
					break;
				case 'private':
					$$type = array('roleid'=>Omelette_User::getUserRole(EGS::getUsername())->id);
					break;
			}
		}
		$read = array_diff($read,$write);
		return array('read'=>$read,'write'=>$write);
	}
	
}
?>