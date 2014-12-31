<?php
class SLCustomer extends DataObject {

	function __construct() {
		parent::__construct('slmaster');
		$this->idField='id';
		$this->identifierField = 'name';
		
 		$this->belongsTo('Company', 'company_id', 'company');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('PaymentTerm', 'term_id', 'payment_term');
 		$this->belongsTo('TaxStatus', 'tax_status_id', 'tax_status'); 

	}

	public function getBillingAddress() {
		$address = new Companyaddress();
		$cc = new ConstraintChain();
		$cc->add(new Constraint('company_id','=',$this->company_id));
		$cc->add(new Constraint('billing','=','true'));
		$address->loadBy($cc);
		return $address;
	}
	
	public function phone() {
		
	}
	
	public function email() {
	
	}

	public function fax() {
	
	}
	
	public function contact() {
	
	}
	
	public function getOutstandingTransactions($extract=true) {
		$transactions = new SLTransactionCollection();
		$sh = new SearchHandler($transactions,false);
		if($extract) {
			$sh->extract();
		}
		$sh->addConstraint(new Constraint('status','=','O'));
		if(isset($this->_data['customer_id'])) {
			$sh->addConstraint(new Constraint('slmaster_id','=',$this->_data['customer_id']));
		}
		
		$transactions->load($sh);
		
		return $transactions;
	}
	
	public function outstanding_balance() {
		$db = DB::Instance();
		$query = 'select COALESCE(sum(os_value),0) FROM sltransactions WHERE status=\'O\' AND slmaster_id='.$db->qstr($this->id);
		$amount = $db->GetOne($query);
		if($amounts===false) {
			return false;
		}
		return $amount;
	}
	
	public static function loadFromCBRelationID($id) {
		$db = DB::Instance();
		$query = 'SELECT slm.* FROM slmaster slm RIGHT JOIN cb_relations cbr ON (slm.company_id=cbr.company_id) WHERE cbr.id='.$db->qstr($id);
		$row = $db->GetRow($query);
		
		$customer = new SLCustomer();
		$customer->_data = $row;
		$customer->load($row['id']);
		
		return $customer;
	}

}
?>
