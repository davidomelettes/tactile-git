<?php
class GLElement extends DataObject {
	private static $multipliers = array(
		'S'=>array(
			'I'=>-1,
			'C'=>1
		),
		'P'=>array(
			'I'=>1,
			'C'=>-1
		)	
	);
	function __construct() {
		parent::__construct('glelement');
		$this->idField='id';
		$this->orderby='value';
		
		
 		$this->belongsTo('GLTransaction', 'gltransaction_id', 'gltransaction');
 		$this->belongsTo('GLAccount', 'glmaster_id', 'glmaster');
 		$this->belongsTo('GLCentre', 'glcentre_id', 'glcentre'); 


	}


	public static function makeLedgerControl(Invoice $invoice,$transaction_id,$description='',&$errors=array()) {
		$control_data = array();
		$invoice_type = substr(strtoupper(get_class($invoice)),0,1);
		$trans_type = strtoupper($invoice->transaction_type[0]);
		$mult = self::$multipliers[$invoice_type][$trans_type];
		
		$control_data['gltransaction_id'] = $transaction_id;
		$gl_params = new GLParams();
		//there's a dummy GLAccount
		$control_data['glmaster_id'] = ($invoice_type=='S')?$gl_params->sales_ledger_control_account:$gl_params->purchase_ledger_control_account;
		//and a same dummy cost centre
		$control_data['glcentre_id'] = $gl_params->balance_sheet_cost_centre;
		
		//control for Invoices is +ve, -ve for CreditNotes
		$value = ($mult*-1) * $invoice->base_gross_value;
		$twinvalue = ($mult*-1) * $invoice->twin_gross_value;
		$control_data['value'] = $value;
		$control_data['twinvalue'] = $twinvalue;
		
		//and the same comment again
		$control_data['comment'] =  $description;
		
		//and build this element
		$element = GLElement::Factory($control_data,$errors,'GLElement');
		return $element;
	}
	
	
	public static function makeLedgerTax(Invoice $invoice, $transaction_id, $description='', &$errors=array()) {
		$vat_element = array();
		$invoice_type = substr(strtoupper(get_class($invoice)),0,1);
		$trans_type = strtoupper($invoice->transaction_type[0]);
		$mult = self::$multipliers[$invoice_type][$trans_type];
		
		$vat_element['gltransaction_id'] = $transaction_id;
		
		$gl_params = new GLParams();			
		//for tax, there is a dummy GLAccount for VAR, for SL we want vat_output
		$vat_element['glmaster_id']  = ($invoice_type=='S')?$gl_params->vat_output:$gl_params->vat_input;
		
		//there is a dummy cost_centre, which for SL and PL tax and control is the balance_sheet
		$vat_element['glcentre_id'] = $gl_params->balance_sheet_cost_centre;
		
		//the values come from the invoice
		$value = $mult * $invoice->base_tax_value;
		$twinvalue = $mult * $invoice->twin_tax_value;
		$vat_element['value'] = $value;
		$vat_element['twinvalue'] = $twinvalue;
		
		//and add the comment as for the header
		$vat_element['comment'] = $description;
		
		//build the element
		$vat_element = GLElement::Factory($vat_element,$errors,'GLElement');
		return $vat_element;
	}

//GLElement::makeDistibutionLine($gl_transaction->id,$data['glaccount_id'],$data['glcentre_id'],$cb_trans,$errors);
	public static function makeCBReceiptDistributionLine($transaction_id,$glaccount_id,$glcentre_id,$cb_trans,&$errors) {
		$element=array();
		$element['gltransaction_id']=$transaction_id;
		$element['value']=-$cb_trans->base_net_value;
		
		$element['glmaster_id'] = $glaccount_id;
		$element['glcentre_id'] = $glcentre_id;
		
		$element['twinvalue'] = -$cb_trans->twin_net_value;
		$element['comment'] = $cb_trans->reference;
		
		$element = GLElement::Factory($element,$errors,'GLElement');
		return $element;
	}
//GLElement::makeCBReceiptTax($gl_transaction->id,$cb_trans,$errors);
	public static function makeCBReceiptTax($transaction_id,$cb_trans,&$errors) {
		$element=array();
		$element['gltransaction_id']=$transaction_id;
		$gl_params = new GLParams();
		
		$element['glcentre_id'] =  $gl_params->balance_sheet_cost_centre;
		$element['glmaster_id'] = $gl_params->vat_output;
		
		$element['value'] = -$cb_trans->base_tax_value;
		$element['twinvalue'] = -$cb_trans->twin_tax_value;
		
		$element = GLElement::Factory($element,$errors,'GLElement');
		return $element;
	}

	public static function makeLedgerLine(InvoiceLine $line,$transaction_id,$type,&$errors=array()) {
		$element = array();
		$element['gltransaction_id'] = $transaction_id;
		
		$invoice_type = substr(strtoupper(get_class($line)),0,1);
		$trans_type = substr(strtoupper($type),0,1);
		$mult = self::$multipliers[$invoice_type][$trans_type];
		
		//the glaccount and cost centre details are held in the lines
		$element['glmaster_id'] = $line->glaccount_id;
		$element['glcentre_id'] = $line->glcentre_id;
		
		$value = $mult * $line->base_net_value;
		$twinvalue = $mult * $line->twin_net_value;
		$element['value'] = $value;
		$element['twinvalue'] = $twinvalue;
		
		//then provide some alternatives to get a comment
		$i_desc = $line->item_description;
		$desc = (!empty($i_desc)?$i_desc:$line->description);
		$desc = (!empty($desc)?$desc:'');
		$element['comment'] = $desc;
		
		//and build the element
		$element=GLElement::Factory($element,$errors,'GLElement');
		return $element;
	}	
	
	public static function makeJournalEntryLine(Array $element,$transaction_id,$twin_rate,&$errors) {
		$element['gltransaction_id'] = $transaction_id;
		if(!empty($element['debit'])) {
			$element['value'] = $element['debit'];
		}
		else if(!empty($element['credit'])) {
			$element['value'] = -$element['credit'];
		}
		else {
			throw new Exception('Can\'t enter a journal line without either a credit or a debit');
		}
		$element['twinvalue'] = $element['value'] * $twin_rate;
		$element = GLElement::Factory($element,$errors,'GLElement');
		return $element;		
	}
//	$bank_line = GLElement::makeBankAccountLine($gl_transaction->id,$cb_trans,$errors);	
	public static function makeCBReceiptLine($transaction_id,$cb_trans,&$errors) {
		$element = array();
		$element['gltransaction_id']=$transaction_id;
		
		$element['value'] = $cb_trans->base_gross_value;
		$element['twinvalue'] = $cb_trans->twin_gross_value;
		
		$account = new CBAccount();
		$account->load($cb_trans->cb_account_id);
		
		$element['glcentre_id']=$account->glcentre_id;
		$element['glmaster_id']=$account->glaccount_id;
		$element['comment'] = $cb_trans->reference;
		$element = GLElement::Factory($element,$errors,'GLElement');
		return $element;
		
	}

}
?>
