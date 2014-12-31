<?php
class UKTaxCalculator implements TaxCalculation {
	public function calc_percentage($rate_id,$status_id,$amount) {
		$rate = new TaxRate();
		$rate->load($rate_id);
		$rate_percentage = $rate->percentage;
		
		$status = new TaxStatus();
		$status->load($status_id);
		
		if($status->apply_tax==='t') {
			$percentage = $rate_percentage;
		}
		else  {
			$percentage = 0;
		}
		
		return bcdiv($percentage,100);
		
	}
}
?>