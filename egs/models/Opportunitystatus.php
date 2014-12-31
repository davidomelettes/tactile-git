<?php
class Opportunitystatus extends DataObject {

	function __construct() {
		parent::__construct('opportunitystatus');
		$this->idField='id';

		$this->orderby = 'position';
 		$this->validateUniquenessOf('id');
		$this->hasMany('Opportunity','opportunities','status_id');
	}
	
	public function getTotalCost(ConstraintChain $cc=null) {
		$db = DB::Instance();
		$query = 'SELECT COALESCE(sum(cost),0) FROM opportunities WHERE status_id='.$db->qstr($this->id).' AND usercompanyid='.$db->qstr(EGS_COMPANY_ID);
		if($cc!=null) {
			$where = $cc->__toString();
			if(!empty($where)) {
				$query.=' AND '.$where;
			}
		}
		
		$total = $db->GetOne($query);
		if($total===false) {
			die($db->ErrorMsg());
		}
		return $total;
	}
	
	public static function StatusIsOpen($id) {
		if(is_null($id)) {
			return false;
		}
		$db = DB::Instance();
		$query = 'SELECT open FROM opportunitystatus 
			WHERE id='.$db->qstr($id).' AND usercompanyid='.$db->qstr(EGS::getCompanyId());
		$open = $db->GetOne($query);
		return $open == 't';
	}

}
?>
