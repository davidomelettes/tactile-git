<?php
class SLTransaction extends LedgerTransaction {
	protected $defaultDisplayFields = array('our_reference','transaction_date','ext_reference','due_date','gross_value','currency_id','transaction_type','status');
	function __construct() {
		parent::__construct('sltransactions');
		$this->idField='id';
		
		
 		$this->belongsTo('Cumaster', 'currency_id', 'currency');
 		$this->belongsTo('Cumaster', 'twin_currency', 'twin');
 		$this->belongsTo('Syterm', 'payment_term_id', 'payment'); 

	}
}
?>
