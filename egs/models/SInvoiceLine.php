<?php
class SInvoiceLine extends InvoiceLine {

	function __construct() {
		parent::__construct('si_lines');
		$this->idField='id';
		
		
 		$this->belongsTo('SInvoice', 'invoice_id', 'invoice');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('Currency', 'twin_currency', 'twin');
 		$this->belongsTo('GLAccount', 'glaccount_id', 'glaccount');
 		$this->belongsTo('GLCentre', 'glcentre_id', 'glcentre'); 
		
		$this->belongsTo('TaxRate', 'tax_rate_id', 'tax_rate'); 
		$this->belongsTo('TaxStatus', 'tax_status_id', 'tax_status'); 

	}
	
	
	/**
	 * Build the line
	 *
	 */
	public static function makeLine($data,&$errors) {
		$line = new SInvoiceLine();
		foreach($data as $key=>$value) {
			$line->$key = $value;
		}
		$line->line_number = $data['line_num'];
		
		//net value is unit-price * quantity
		$line->net_value = round($line->sales_qty * $line->sales_price,2);
		
		//tax  (in the UK at least) is dependent on the tax_rate of the item, and the tax status of the customer.
		//this function is a wrapper to a call to a config-dependent function
		$tax_percentage = calc_tax_percentage($data['tax_rate_id'],$data['tax_status_id'],$line->net_value);
		$line->tax_percentage=$tax_percentage;
		
		//tax_value is net value with tax added
		$line->tax_value = trunc(bcmul($line->net_value,$tax_percentage),2);
		
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
	
	public function sortOutValues($data) {
		//net value is unit-price * quantity
		$this->net_value = round(bcmul($this->sales_qty,$this->sales_price),2);
		
		//tax  (in the UK at least) is dependent on the tax_rate of the item, and the tax status of the customer.
		//this function is a wrapper to a call to a config-dependent method
		$tax_percentage = calc_tax_percentage($data['tax_rate_id'],$data['tax_status_id'],$this->net_value);
		$this->tax_percentage=$tax_percentage;
		
		//tax_value is the tax percentage of the net value
		$this->tax_value = trunc(bcmul($this->net_value,$tax_percentage),2);
	}

}
?>
