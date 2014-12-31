<?php
class PInvoice extends Invoice {

	function __construct() {
		parent::__construct('pi_header');
		$this->idField='id';
		
		
 		$this->belongsTo('PLSupplier', 'plmaster_id', 'plmaster');
 		$this->belongsTo('User', 'auth_by', 'auth');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('PaymentTerm', 'payment_term_id', 'payment'); 
		$this->setEnum('transaction_type',array('I'=>'Invoice','C'=>'Credit Note'));
		$this->hasMany('PInvoiceLine','lines','invoice_id');
	}

	public static function Factory($header_data,$lines_data,&$errors) {
		$header = Invoice::makeHeader($header_data,new PInvoice(),$errors);
		if($header!==false) {
			$lines=array();
			
			Invoice::attachCurrencyInfo($header,$lines_data);
			
			//build the lines
			foreach($lines_data as $line_data) {
				$line = Invoice::makeLine($line_data,new PInvoiceLine(),$errors);
				if($line!==false) {
					$header->addLine($line);
				}
				else {
					return false;
				}
			}
			return $header;
		}
		return false;
	}
}
?>
