<?php
class SameCompanyUsersConstraint extends Constraint {
	
	public function __construct() {
	}
	
	function __toString() {
		$db=DB::Instance();
		$string='';
		$subquery='select u.username from users u left join person p on (u.person_id=p.id),
				users u2 LEFT JOIN person p2 ON (u2.person_id=p2.id) where p.organisation_id=p2.organisation_id and u2.username='.$db->qstr(EGS_USERNAME);
		$string.='username IN ('.$subquery.')';
		return $string;
	}	
	
}
?>