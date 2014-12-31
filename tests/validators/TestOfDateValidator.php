<?php
class TestOfDateValidator extends UnitTestCase {
	
	function __construct() {
		parent::UnitTestCase();
		echo "Running TestOfDateValidator\n";
	}
	
	function setup() {
		global $injector;
		$injector = new Phemto();
		$injector->register('Prettifier');
	}
	
	function testStandardDateFormatIsAccepted() {		
		$validator = new OmeletteDateValidator();
		
		$date = '2008-02-01';
		
		$field = new DataField('testfield', $date);
		
		$errors = array();
		
		$result = $validator->test($field, $errors);
		
		$this->assertEqual(count($errors), 0);
		$this->assertEqual($date, $result);
	}
	
	function testStandardDateWithTimeIsAccepted() {
		$validator = new OmeletteDateValidator();
		
		$date = '2008-02-01 12:30:23';
		
		$field = new DataField('testfield', $date);
		
		$errors = array();
		
		$result = $validator->test($field, $errors);
		
		$this->assertEqual(count($errors), 0);
		$this->assertEqual($date, $result);
	}
	
	function testDMYFormatIsAccepted() {
		$validator = new OmeletteDateValidator();
		
		$date = '12/03/2007';
		EGS::setDateFormat('d/m/Y');
		$field = new DataField('testfield', $date);
		
		$errors = array();
		
		$result = $validator->test($field, $errors);
		
		$this->assertEqual(count($errors), 0);
		$this->assertEqual('2007-03-12', $result);
	}
	
	function testMDYFormatIsAccepted() {
		$validator = new OmeletteDateValidator();
		
		$date = '12/03/2007';
		EGS::setDateFormat('m/d/Y');
		$field = new DataField('testfield', $date);
		
		$errors = array();
		
		$result = $validator->test($field, $errors);
		
		$this->assertEqual(count($errors), 0);
		$this->assertEqual('2007-12-03', $result);
	}
	
	function testShortDMYFormatIsAccepted() {
		$validator = new OmeletteDateValidator();
		
		$date = '12/03/07';
		EGS::setDateFormat('d/m/Y');
		$field = new DataField('testfield', $date);
		
		$errors = array();
		
		$result = $validator->test($field, $errors);
		
		$this->assertEqual(count($errors), 0);
		$this->assertEqual('2007-03-12', $result);
	}
	
	function testShortMDYFormatIsAccepted() {
		$validator = new OmeletteDateValidator();
		
		$date = '12/03/07';
		EGS::setDateFormat('m/d/Y');
		$field = new DataField('testfield', $date);
		
		$errors = array();
		
		$result = $validator->test($field, $errors);
		
		$this->assertEqual(count($errors), 0);
		$this->assertEqual('2007-12-03', $result);
	}
	
	function testInvalidDMYFormatIsRejected() {
		$validator = new OmeletteDateValidator();
		
		$date = '12/20/2007';
		EGS::setDateFormat('d/m/Y');
		$field = new DataField('testfield', $date);
		
		$errors = array();
		
		$result = $validator->test($field, $errors);
		
		$this->assertEqual(count($errors), 1);
		$this->assertFalse($result);
	}
	
	function testInvalidMDYFormatIsRejected() {
		$validator = new OmeletteDateValidator();
		
		$date = '20/12/2007';
		EGS::setDateFormat('m/d/Y');
		$field = new DataField('testfield', $date);
		
		$errors = array();
		
		$result = $validator->test($field, $errors);
		
		$this->assertEqual(count($errors), 1);
		$this->assertFalse($result);
	}
	
	function testGenerallyInvalidFormatIsRejected() {
		$validator = new OmeletteDateValidator();
		
		$date = 'Foobar';
		EGS::setDateFormat('m/d/Y');
		$field = new DataField('testfield', $date);
		
		$errors = array();
		
		$result = $validator->test($field, $errors);
		
		$this->assertEqual(count($errors), 1);
		$this->assertFalse($result);
	}
	
}
?>