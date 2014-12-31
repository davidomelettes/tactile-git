<?php
class Systemcompany extends DataObject {

	protected $defaultDisplayFields = array('organisation'=>'Organisation','enabled'=>'Enabled');

	function __construct() {
		parent::__construct('system_companies');
		$this->idField='id';
		
		$this->validateUniquenessOf('organisation_id');
 		$this->belongsTo('Organisation', 'organisation_id', 'organisation_id'); 

	}

	/**
	 * Saving a new Systemcompany causes:
	 * - a default admin role to be added. 
	 * - the admin role is given access to all the modules of the company
	 * - the creating-user is given access to the systemcompany
	 * -  the creating-user is put in the admin role
	 *
	 * @return boolean
	 */
	public function save($debug=false) {
		$db=DB::Instance();
		$db->StartTrans();
		$result = parent::save($debug);
		if($result===false) {
			$db->FailTrans();
			return ($db->CompleteTrans());
		}
		//if it succeeded, then see if we need to make a default role and give the creating user access
		$query = 'SELECT count(*) FROM roles WHERE usercompanyid='.$db->qstr($this->organisation_id);
		$num_roles = $db->GetOne($query);
		if($num_roles>0) {
			return ($db->CompleteTrans());	//not a fail, just no need to continue
		}
		$errors=array();
		
		$role_data = array();
		$role_data['description']='A default role for Admin users, has access to all modules';
		$role_data['name']='Admin';
		$role_data['usercompanyid'] = $this->organisation_id;
		
		$admin_role = DataObject::Factory($role_data,$errors,'Role');
		if($admin_role===false||$admin_role->save()===false) {
			$db->FailTrans();
			return false;
		}
		
		$query = 'INSERT INTO haspermission (roleid,permissionsid) (SELECT '.$admin_role->id.', permissionid FROM companypermissions WHERE usercompanyid='.$db->qstr($this->organisation_id).')';
		$db->Execute($query);
		
		$has_role_data=array(
			'roleid'=>$admin_role->id,
			'username'=>EGS_USERNAME
		);
		$has_role = DataObject::Factory($has_role_data,$errors,'HasRole');
		if($has_role===false||$has_role->save()===false) {
			$db->FailTrans();
			return false;
		}
		
		$uca_data = array(
			'username'=>EGS_USERNAME,
			'organisation_id'=>$this->organisation_id,
			'enabled'=>true
		);
		$uca = DataObject::Factory($uca_data,$errors,'Usercompanyaccess');
		if($uca===false||$uca->save()===false) {
			$db->FailTrans();
			return false;
		}
		return ( $db->CompleteTrans() );
	}
	
	public static function countNonUsers() {
		$db = DB::Instance();
		$query = 'SELECT id FROM person p LEFT JOIN users u ON (p.id=u.person_id) WHERE p.usercompanyid='.EGS_COMPANY_ID.' LIMIT 1';
		$count = $db->GetOne($query);
		return $count;
	}

}
?>
