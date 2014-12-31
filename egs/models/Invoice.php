<?php
abstract class Invoice extends DataObject {
	
	private $unsaved_lines=array();
	/**
	 *  Build the invoice header, as if from a submitted form
	 *
	 */
	public static function makeHeader($data,Invoice $invoice,&$errors) {
		$fields = $invoice->getFields();
		foreach($data as $key=>$val) {
			$invoice->$key = $val;
		}
		
		$db=DB::Instance();
		$seq = $invoice->getTableName().'_id_seq';
		$invoice->id = $db->GenID($seq);
				
		$invoice_date = fix_date($data['invoice_date']);
		$invoice->invoice_date=$invoice_date;

		$terms = new PaymentTerm();
		$terms->load($data['payment_term_id']);
		
		
		$invoice->due_date = calc_due_date($invoice->invoice_date,$terms->basis,$terms->days,$terms->months);
			
		$generator = new InvoiceNumberHandler();
		$invoice->invoice_number = $generator->handle($invoice);
		
		$invoice->status = 'N';
		$invoice->usercompanyid=EGS_COMPANY_ID;
		return $invoice;
	}
	/**
	 * Build the line
	 *
	 */
	public static function makeLine($data,$line,&$errors) {
		foreach($data as $key=>$value) {
			$line->$key = $value;
		}
		$line->line_number = $data['line_num'];
		
		
		//how the net and tax are determined depends on the type of invoice:
		$line->sortOutValues($data);
		
		//gross value is net + tax
		$line->gross_value = round(bcadd($line->net_value,$line->tax_value),2);
		
		//then convert to the base currency
		$line->base_net_value = round(bcdiv($line->net_value,$line->rate),2);
		$line->base_tax_value = round(bcdiv($line->tax_value,$line->rate),2);
		$line->base_gross_value = round(bcadd($line->base_tax_value,$line-> base_net_value),2);
		
		
		//and to the twin-currency
		$line->twin_net_value = round(bcmul($line->base_net_value,$line->twin_rate),2);
		$line->twin_tax_value = round(bcmul($line->base_tax_value,$line->twin_rate),2);
		$line->twin_gross_value = round(bcadd($line->twin_tax_value,$line-> twin_net_value),2);
		
		$line->usercompanyid = EGS_COMPANY_ID;
		return $line;
	}
	public static function attachCurrencyInfo($header,&$lines_data) {
		//determine the base currency
		$currency = new Currency();
		$currency->load($header->currency_id);
		$header->rate =$currency->rate;
			
			
		//determine the twin currency
		global $companyparams;
		$twin_currency = new Currency();
		$twin_currency->load($companyparams->getParam('currency'));
		$header->twin_rate = $twin_currency->rate;
		$header->twin_currency = $twin_currency->id;
		
		//build the invoice lines
		$i=0;
		foreach($lines_data as $index=>$line) {
			$i++;
			//the invoice id of the line is the header's id
			$lines_data[$index]['pinvoice_id'] = $header->id;
			//the line number is just an increment
			$lines_data[$index]['line_num']=$i;
			
			//the currencies of the lines are the same as the header
			$lines_data[$index]['currency_id'] = $currency->id;
			$lines_data[$index]['rate'] = $currency->rate;

			$lines_data[$index]['twin_currency'] = $twin_currency->id;
			$lines_data[$index]['twin_rate'] = $twin_currency->rate;
			
			//TODO:
			$lines_data[$index]['line_discount']=0;
		}
		
	}
	
	public function addLine($line) {
		$sums = array('net_value', 'tax_value', 'gross_value');
		$prefixes = array('','twin_','base_');
		foreach($prefixes as $prefix) {
			foreach($sums as $sum) {
				$this->{$prefix.$sum}=bcadd($this->{$prefix.$sum},$line->{$prefix.$sum});
			}
		}
		$line->invoice_id=$this->id;
		$this->unsaved_lines[]=$line;
	}
	
	/**
	 *  Saving the Invoice involves saving the lines
	 *
	 */
	public function save($debug=false) {
		$db=DB::Instance();
		$db->startTrans();
		$result = parent::save($debug);
		if($result!==false) {
			foreach($this->unsaved_lines as $line) {
				$result=$line->save();
				if($result===false) {
					break;
				}
			}
		}
		return $db->CompleteTrans();
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
		$pl_transaction = LedgerTransaction::makeFromInvoice($this);
		if($pl_transaction!==false) {
			$pl_transaction->save($this);
			$this->status='O';
			$this->save();
		}
		else {
			$db->FailTrans();
		}
		return $db->CompleteTrans();
	}
	
	public function hasBeenPosted() {
		return ($this->status!='N'&&$this->status!='New');
	}
}
?>