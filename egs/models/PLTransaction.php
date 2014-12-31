<?php
class PLTransaction extends LedgerTransaction {

	function __construct() {
		parent::__construct('PLTransactions');
		$this->idField='id';
		
		
 		$this->belongsTo('Cumaster', 'currency_id', 'currency');
 		$this->belongsTo('Cumaster', 'twin_currency', 'twin');
 		$this->belongsTo('Syterm', 'payment_term_id', 'payment'); 

	}
	
	
	

}
?>
