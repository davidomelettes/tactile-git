<?php
class PInvoiceLine extends InvoiceLine {
	function __construct() {
		parent::__construct('pi_lines');
		$this->idField='id';
		
		
 		$this->belongsTo('PInvoice', 'invoice_id', 'invoice');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('Currency', 'twin_currency', 'twin');
 		$this->belongsTo('GLAccount', 'glaccount_id', 'glaccount');
 		$this->belongsTo('GLCentre', 'glcentre_id', 'glcentre'); 

	}
	public function sortOutValues($data) {
		
	}	
	
}
?>
