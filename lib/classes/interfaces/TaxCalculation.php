<?php
interface TaxCalculation {
	public function calc_percentage($rate_id,$status_id,$amount);	
}
?>