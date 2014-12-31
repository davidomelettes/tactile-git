<?php
class SInvoice extends Invoice {
	protected $defaultDisplayFields = array('invoice_number','invoice_date','transaction_type','status','base_gross_value');
	function __construct() {
		parent::__construct('si_header');
		$this->idField='id';
		
		$this->view='';
		
 		$this->validateUniquenessOf('invoice_number');
 		$this->belongsTo('SLCustomer', 'slmaster_id', 'slmaster');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('Currency', 'twin_currency', 'twin');
 		$this->belongsTo('PaymentTerm', 'payment_term_id', 'payment'); 
		$this->setEnum('transaction_type',array('I'=>'Invoice','C'=>'Credit Note'));
		$this->setEnum('status',array('N'=>'New','O'=>'Open','P'=>'Printed','C'=>'Completed'));
		$this->getField('base_gross_value')->setFormatter(new PriceFormatter());
		$this->hasMany('SInvoiceLine','lines','invoice_id');
		
	}
	

	public static function Factory($header_data,$lines_data,&$errors) {
		$header = Invoice::makeHeader($header_data,new SInvoice(),$errors);
		if($header!==false) {
			$lines=array();
			
			Invoice::attachCurrencyInfo($header,$lines_data);
			//build the lines
			foreach($lines_data as $line_data) {
				$line = Invoice::makeLine($line_data,new SInvoiceLine(),$errors);
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
	
	
	
	
	/**
	 *  Post an invoice to the Sales ledger
	 *  - saving to SLTransaction automatically saves to General Ledger
	 */
	public function post() {
		if($this->hasBeenPosted()) {
			throw new Exception('Attempting to repost an Invoice');
		}
		$db=DB::Instance();
		$db->StartTrans();
		$sl_transaction = SLTransaction::makeFromInvoice($this);
		if($sl_transaction!==false) {
			$sl_transaction->save($this);
			$this->status='O';
			$this->save();
		}
		else {
			$db->FailTrans();
		}
		return $db->CompleteTrans();
	}
	
	
	
	/**
	 *  Build the invoice header, as if from a submitted form
	 *
	 */
	public static function makeHeader($data,&$errors) {
		$invoice = new SInvoice();
		$fields = $invoice->getFields();
		foreach($data as $key=>$val) {
			$invoice->$key = $val;
		}
		
		$db=DB::Instance();
		$invoice->id = $db->GenID('si_header_id_seq');
				
		$invoice_date = fix_date($data['invoice_date']);
		$invoice->invoice_date=$invoice_date;

		$terms = new PaymentTerm();
		$terms->load($data['payment_term_id']);
		
		$invoice->due_date = calc_due_date($invoice->invoice_date,$terms->basis,$terms->days,$terms->months);
			
		$generator = new InvoiceNumberHandler();
		$invoice->invoice_number = $generator->handle($invoice);
		
		$invoice->status = 'N';
		
		return $invoice;
	}
	

}
?>
