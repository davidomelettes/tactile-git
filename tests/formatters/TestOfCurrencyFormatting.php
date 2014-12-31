<?php
class TestOfCurrencyFormatting extends ControllerTest {
	
	function testOpportunityCostFormatting() {
		EGS::setCurrencySymbol('');
		$opp = DataObject::Construct('Opportunity');
		
		$opp->cost = 12000.45;
		
		$this->assertEqual($opp->getFormatted('cost'), '12,000.45');
	}
	
	
}
?>