<?php
class PLSupplier extends DataObject {

	function __construct() {
		parent::__construct('plmaster');
		$this->idField='id';
		
		
 		$this->belongsTo('Company', 'company_id', 'company');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('PaymentTerm', 'term_id', 'payment_term');
 		$this->belongsTo('TaxStatus', 'tax_status_id', 'tax_status'); 

	}


}
?>
