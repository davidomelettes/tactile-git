<?php
class LedgerTransaction extends DataObject{

	public static function makeFromInvoice(Invoice $invoice) {
	
		switch(get_class($invoice)) {
			case 'PInvoice':
				$transaction = new PLTransaction();
				break;
			case 'SInvoice':
				$transaction = new SLTransaction();
				break;
			default:
				throw new Exception('Invalid Invoice type');
		}
		
		//copy across the fields that are needed
		foreach($invoice->getFields() as $fieldname=>$field) {
			if($transaction->isField($fieldname)) {
				$transaction->$fieldname = $invoice->$fieldname;
			}
		}
		$db=DB::Instance();
		$transaction->id = $db->GenID('PLTransactions_id_seq');
		$transaction->transaction_type = 'I';
		$transaction->our_reference = $invoice->invoice_number;
		$transaction->status = 'O';
		
		$prefixes = array('','base_','twin_');
		//the outstanding (os) values are the gross values to begin with
		foreach($prefixes as $prefix) {
			$transaction->{$prefix.'os_value'} = $transaction->{$prefix.'gross_value'};
		}
		
		$transaction->transaction_date = $invoice->invoice_date;
		$transaction->due_date = $invoice->due_date;
		return $transaction;
	}
	
	public static function makeFromCash(CBTransaction $cb_trans) {
		switch($cb_trans->source) {
			case 'P':
				$transaction = new PLTransaction();
				$transaction->transaction_type = 'P';
				$mult = 1;
				break;
			case 'S':
				$transaction = new SLTransaction();
				$transaction->transaction_type = 'R';
				$mult = -1;
				break;
			default:
				throw new Exception('Invalid Source');
		}
		
		foreach($cb_trans->getFields() as $fieldname=>$field) {
			if($transaction->isField($fieldname)) {
				$transaction->$fieldname = $cb_trans->$fieldname;
			}
		}
		$db = DB::Instance();
		$transaction->id = $db->GenID('PLTransactions_id_seq');
		$transaction->status='O';
		$transaction->our_reference = $cb_trans->reference;
		$desc = $cb_trans->description;
		$transaction->ext_reference = (empty($desc)?$cb_trans->cb_relation:$desc);
		
		$customer = SLCustomer::loadFromCBRelationID($cb_trans->cb_relation_id);
		$transaction->payment_term_id = $customer->term_id;
		$transaction->due_date = $transaction->transaction_date;
		
		$transaction->twin_currency = $cb_trans->twincurrency_id;
		$transaction->twin_rate = $cb_trans->twinrate;
		
		
		$prefixes = array('','base_','twin_');
		//the outstanding (os) values are the gross values to begin with
		foreach($prefixes as $prefix) {
			$transaction->{$prefix.'os_value'} = $transaction->{$prefix.'gross_value'};
		}
		$values = array('net_','tax_','gross_','os_');
		foreach($prefixes as $prefix) {
			foreach($values as $value) {
				$transaction->{$prefix.$value.'value'} *= $mult;
			}
		}
		
		$transaction->getField('slmaster_id')->value = $customer->id;
		return $transaction;
	}
	
	public function saveForPayment() {
		$result = parent::save();
		return $result;
	}
	
	public function save(Invoice $invoice) {
		$result = parent::save();
		if($result===false) {
			return false;
		}
		$gl_transaction = GLTransaction::makeFromLedgerTransaction($this,$invoice);
		if($gl_transaction!==false) {
			return $gl_transaction->save();
		}
		return false;
	}
	
}
?>