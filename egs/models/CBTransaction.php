<?php
class CBTransaction extends DataObject {
	protected $defaultDisplayFields = array('transaction_date','reference','gross_value','status','cb_account_id','cb_relation_id','source','type_id');
	function __construct() {
		parent::__construct('cb_transactions');
		$this->idField='id';
		
		
 		$this->belongsTo('CBAccount', 'cb_account_id', 'cb_account');
 		$this->belongsTo('CBRelation', 'cb_relation_id', 'cb_relation');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('Currency', 'twincurrency_id', 'twincurrency');
 		$this->belongsTo('Currency', 'basecurrency_id', 'basecurrency');
 		$this->belongsTo('Sypaytype', 'type_id', 'type'); 
		$this->belongsTo('TaxRate','tax_rate_id','tax_rate');

	}
	public static function receivePaymentThroughSL($data) {
		$glparams = new GLParams();
		$db = DB::Instance();
		$db->StartTrans();
		$data['glaccount_id']=$glparams->sales_ledger_control_account;
		$data['glcentre_id']=$glparams->balance_sheet_cost_centre;
		$data['source'] = 'S';
		$cb_trans = self::receivePayment($data);
		
		$sl_trans = LedgerTransaction::makeFromCash($cb_trans);
		if($sl_trans!==false) {
			$sl_trans->saveForPayment();		
		}
		$db->CompleteTrans();
	}

	public static function receivePayment($data) {
				
		$currency = new Currency();
		$currency->load($data['currency_id']);
		
		$data['rate'] = $currency->rate;
		
		global $companyparams;
		$twin_currency = new Currency();
		$twin_currency->load($companyparams->getParam('currency'));
		$data['twinrate'] = $twin_currency->rate;
		$data['twincurrency_id'] = $twin_currency->id;
		
		if(empty($data['tax_value'])) {
			$data['tax_value'] = 0;
		}
		$data['gross_value'] = bcadd($data['net_value'],$data['tax_value']);
		
		$base_currency = new Currency();
		$base_currency->load($companyparams->getParam('currency'));
		$data['basecurrency_id'] = $base_currency->id;
		
		
		$data['base_gross_value'] = round(bcdiv($data['gross_value'],$data['rate']),2);
		$data['twin_gross_value'] = round(bcdiv($data['base_gross_value'],$data['twinrate']),2);
		
		$data['base_tax_value'] = round(bcdiv($data['tax_value'],$data['rate']),2);
		$data['twin_tax_value'] = round(bcdiv($data['base_tax_value'],$data['twinrate']),2);
		
		$data['base_net_value'] = round(bcsub($data['base_gross_value'],$data['base_tax_value']),2);
		$data['twin_net_value'] = round(bcsub($data['twin_gross_value'],$data['twin_tax_value']),2);
		
		if(!isset($data['source'])) {
			$data['source'] = 'C';
		}
		
		$data['type_id'] = 1;
		
		$data['status'] = 'N';
		
		
		$account = new CBAccount();
		$account->load($data['cb_account_id']);
		
		if($account->currency_id==$data['currency_id']) {
			$data['account_value'] = $data['gross_value'];
		}
		else if($account->currency_id==$base_currency->id) {
			$data['account_value'] = $data['base_gross_value'];
		}
		else {
			$account_currency = new Currency();
			$account_currency->load($account->currency_id);
			$data['account_value'] = round(bcmul($data['base_gross_value'],$account_currency->rate),2);
		}
			
		
		$errors=array();
		$trans = DataObject::Factory($data,$errors,'CBTransaction');
		if($trans===false) {
			print_r($errors);
			return false;
		}
		$db=DB::Instance();
		$db->StartTrans();
		$db->debug=true;
		$trans->save();
		
		
		$success=$account->updateBalance($trans);
		if(!$success) {
			$db->FailTrans();
			return false;
		}
		$gl_trans = GLTransaction::makeFromCashbookTransaction($trans,$data,'R');
		if($gl_trans!==false) {
			$gl_trans->save();
		}
		else {
			$db->FailTrans();
		}
		$db->CompleteTrans();
		return $trans;
	}

}
?>
