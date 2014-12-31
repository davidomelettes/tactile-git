<?php
AutoLoader::Instance()->addPath(FILE_ROOT.'omelette/lib/payment/');

class TestOfPaymentCard extends UnitTestCase {
	
	function testInvalidMastercards() {
		// Mastercards begin 51-55 and have 16 digits in total
		$numbers = array(
			5123456890432189,
			4567890432134566,
			55432345
		);
		foreach($numbers as $number) {
			$card = new PaymentCard(array('card_type'=>PaymentCard::MASTERCARD, 'card_number'=>$number));
			$this->assertFalse($card->cardNumberIsValid());
		}
	}
	
	function testInvalidMaestros() {
		// Maestro card have one of these 4 prefixes, and either 16 or 18 digits in total
		// (5020|5038|6304|6759)
		$numbers = array(
			5020345678905432,
			503874563456298012,
			4567890432134534,
			348790858949984789,
			55432345
		);
		foreach($numbers as $number) {
			$card = new PaymentCard(array('card_type'=>PaymentCard::MAESTRO, 'card_number'=>$number));
			$this->assertFalse($card->cardNumberIsValid());
		}
	}

	
	function testInvalidVisas() {
		//Visa cards begin with a 4, and have either 13 or 16 digits in total
		$numbers = array(
			50203456789054321,
			403874563456,
			45678904321345342222,
		);
		foreach($numbers as $number) {
			$card = new PaymentCard(array('card_type'=>PaymentCard::MAESTRO, 'card_number'=>$number));
			$this->assertFalse($card->cardNumberIsValid());
		}
	}
}

?>
