<?php
class GLTransaction extends DataObject {
	private $unsaved_elements=array();
	function __construct() {
		
		parent::__construct('gltransaction');
		$this->idField='id';
		$this->identifierField='comment';		
		$this->orderby='id';
		$this->orderdir='desc';
 		$this->validateUniquenessOf(array('docref','source','sourcecode'));
 		$this->belongsTo('GLPeriod', 'glperiods_id', 'glperiods');
 		$this->belongsTo('Currency', 'twincurrency_id', 'twincurrency'); 
		$this->setEnum('type',array('J'=>'Journals','A'=>'Accurals', 'S'=>'Standing'));   
		$this->assignAutoHandler('glperiods_id',new CurrentPeriodHandler());
		$this->getField('glperiods_id')->addValidator(new PeriodValidator());
		rsort($this->getField('glperiods_id')->_validators);
	}
	
	
	public static function makeFromJournalEntry($gl_data,$lines_data,&$errors=array()) {
		$db=DB::Instance();
		self::setTwinCurrency($gl_data);
		$gl_transaction = GLTransaction::Factory($gl_data,$errors,'GLTransaction');
		if($gl_transaction!==false) {
			//then sort out the lines.
			foreach($lines_data as $line) {
				$line = GLElement::makeJournalEntryLine($line,$gl_transaction->id,$gl_transaction->twinrate,$errors);
				if($line!==false) {
					$gl_transaction->addElement($line);
				}
				else {
					$db->FailTrans();
					return false;
				}
			}
			return $gl_transaction;
		}
		return false;
	}
	
	
	public static function makeFromCashbookTransaction(CBTransaction $cb_trans,$data,$type) {
		$errors=array();
		$db=DB::Instance();
		$db->StartTrans();
		$gl_data=array();
		$gl_data['docref'] = $cb_trans->id;
		//dates should be the same
		$gl_data['trandate'] = un_fix_date($cb_trans->transaction_date);
		//coming from CB, so 'C'
		$gl_data['source'] = $cb_trans->source;
		
		$gl_data['type']=$type;
		
		$gl_data['sourcecode'] = $cb_trans->reference;
		
		$desc = $cb_trans->description;
	
		$gl_data['comment'] = !empty($desc)?$desc:$cb_trans->reference;
		
		self::setTwinCurrency($gl_data);
		$gl_transaction = GLTransaction::Factory($gl_data,$errors,'GLTransaction');
		if($gl_transaction!==false) {
			$bank_line = GLElement::makeCBReceiptLine($gl_transaction->id,$cb_trans,$errors);
			if($bank_line!==false) {
				$gl_transaction->addElement($bank_line);
			}
			else {
				$db->FailTrans();
			}
			if(!empty($data['tax_rate_id'])) {
				$vat_line = GLElement::makeCBReceiptTax($gl_transaction->id,$cb_trans,$errors);
				if($vat_line!==false) {
					$gl_transaction->addElement($vat_line);
				}
				else {
					$db->FailTrans();
				}
			}
			$dist_line = GLElement::makeCBReceiptDistributionLine($gl_transaction->id,$data['glaccount_id'],$data['glcentre_id'],$cb_trans,$errors);
			if($dist_line!==false) {
				$gl_transaction->addElement($dist_line);
			}
			else {
				$db->FailTrans();
			}
		}
		if(!$db->hasFailedTrans()) {
			$db->CompleteTrans();
			return $gl_transaction;
		}
		return false;
	}
	
	/**
	 * Takes an array by reference, and sets 'twincurrency_id' and 'twinrate' to the appropriate values
	 *
	 */
	public static function setTwinCurrency(&$data) {
		global $companyparams;
		$twin_currency = new Currency();
		$twin_currency ->load($companyparams->getParam('twinrate'));
		$data['twincurrency_id']= $twin_currency->id;
		$data['twinrate'] = $twin_currency->rate;
	}
	
	public static function makeFromLedgerTransaction(LedgerTransaction $transaction, Invoice $invoice) {
		$errors = array();
		$db=DB::Instance();
		//sort out the header details
		$gl_data=array();
		//the gl docref is the invoice number
		$gl_data['docref'] = $invoice->invoice_number;
		//dates should be the same
		$gl_data['trandate'] = un_fix_date($invoice->invoice_date);
		//coming from SL, so 'S'
		$gl_data['source'] = substr(strtoupper(get_class($transaction)),0,1);
		//type depends on Invoice or Credit Note
		$gl_data['type']=(($invoice->transaction_type=='Invoice'?'I':'C'));
		//sourcode is the customer's id
		$gl_data['sourcecode'] = ($gl_data['source']=='S')?$invoice->slmaster_id:$invoice->plmaster_id;
		
		//the description is one from a number of bits of information
		//(description is compulsory for GL, but the options aren't for SLTransaction and SInvoice)
		$desc = $invoice->description;
		$ext_ref=$invoice->ext_reference;
		$sales_order_id = $invoice->sales_order_id;
		if(!empty($desc)) {
			$header_desc=$desc;
		}
		else if(!empty($ext_ref)) {
			$header_desc=$ext_ref;
		}
		else if(!empty($sales_order_id)) {
			$header_desc=$sales_order_id;
		}
		else {
			$header_desc=$invoice->invoice_number;
		}
		
		$gl_data['comment']=$header_desc;

		//another docref
		$gl_data['docref2'] = $invoice->sales_order_id;
		
		
		//grab the currencies that are needed
		self::setTwinCurrency($gl_data);
		
		
		//build the header
		$gl_transaction = GLTransaction::Factory($gl_data,$errors,'GLTransaction');
		if($gl_transaction!==false) {
			//then sort out the lines.
			//there needs to be a tax element
			$vat_element = GLElement::makeLedgerTax($invoice,$gl_transaction->id,$header_desc,$errors);
			if($vat_element!==false) {
				$gl_transaction->addElement($vat_element);
			}
			else {
				$db->FailTrans();
				return false;
			}
			
			//this is the control element (used to balance the tax and lines)
			$control = GLElement::makeLedgerControl($invoice,$gl_transaction->id,$header_desc);
			if($control!==false) {
				$gl_transaction->addElement($control);
			}
			else {
				$db->FailTrans();
				return false;
			}
			
			//then do the invoice lines
			$lines = $invoice->lines;
			foreach($lines as $line) {
				$element = GLElement::makeLedgerLine($line,$gl_transaction->id,$invoice->transaction_type,$errors);
				if($element!==false) {
					$gl_transaction->addElement($element);
				}
				else {
					$db->FailTrans();
					return false;
				}
			}
			return $gl_transaction;			
		}
		print_r($errors);
		$db->FailTrans();
		return false;
	}
	
	public function addElement(GLElement $element) {
		$this->unsaved_elements[]=$element;
	}
	
	/**
	 *  Save the Transaction and the lines
	 *  performs a check that the lines sum to zero
	 */
	public function save() {
		$db = DB::Instance();
		$db->StartTrans();
		$result = parent::save();
		$total = 0;
		if($result!==false) {
			foreach($this->unsaved_elements as $element) {
				$total=bcadd($total,$element->value);
				$result = $element->save();
				if($result===false) {
					$db->FailTrans();
					return false;
				}
			}
			if($total!=0) {
				throw new Exception('Sum of GLElements going into a transaction must equal zero. It equals: '.$total);
			}
			return $db->CompleteTrans();
		}
		$db->FailTrans();
		return false;
	}

}
?>
