<?php
class CBAccount extends DataObject {
	function __construct() {
		parent::__construct('cb_accounts');
		$this->idField='id';
		
		
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('GLAccount', 'glaccount_id', 'glaccount');
 		$this->belongsTo('GLCentre', 'glcentre_id', 'glcentre'); 

	}


	public function updateBalance(CBTransaction $cb_trans) {
		$amount = $cb_trans->account_value;
		$db=DB::Instance();
		$db->StartTrans();
		$this->balance = bcadd($this->balance,$amount);
		if($this->save()!==false) {
			return $db->CompleteTrans();
		}
		return false;		
	}
	
	public function glbalance() {
		$db=DB::Instance();
		$query = 'SELECT COALESCE(sum(value),0) FROM glelement WHERE glmaster_id='.$db->qstr($this->glaccount_id);
		return $db->GetOne($query);
	}

}
?>
