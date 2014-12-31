<?php
class OrganisationRoles extends DataObject {

	function __construct() {
		parent::__construct('organisation_roles');
	}

	/**
	 * Removes all the entries in companyroles for the given company_id
	 * @param int $company_id
	 * @return void
	 */
	public static function deleteForCompany($organisation_id) {
		$db = DB::Instance();
		$query = 'DELETE FROM organisation_roles WHERE organisation_id = '.$db->qstr($organisation_id);
		$db->Execute($query);
	}
	
	/**
	 * Assigns $type access for given role-ids to the given company-ids
	 * @todo make this faster - copy?
	 * @param String $type One from 'read' or 'write'
	 * @param Array $organisation_ids
	 * @param Array $role_ids
	 * @return boolean
	 */
	public static function AssignAccess($type,Array $organisation_ids, Array $role_ids) {
		$errors=array();
		foreach($organisation_ids as $organisation_id) {
			foreach($role_ids as $role_id) {
				$row = array('organisation_id'=>$organisation_id,'roleid'=>$role_id,'read'=>true);
				if($type=='write') {
					$row['write']=true;
				}
				$org_role = DataObject::Factory($row,$errors,'OrganisationRoles');
				if($org_role===false || $org_role->save()===false) {
					return false;
				}
			}
		}
		return true;
	}
}
?>