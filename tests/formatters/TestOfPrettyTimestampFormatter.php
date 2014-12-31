<?php

Mock::generate('OmeletteClock','MockClock');
class TestOfPrettyTimestampFormatter extends UnitTestCase {
	
	function setup() {
		parent::setup();
		date_default_timezone_set('Europe/London');
	}
	
	function testWithDateOnly() {
		
		$formatter = new PrettyTimestampFormatter();
		
		$clock = new MockClock();
		$formatter->setClock($clock);
		
		$clock->setReturnValue('getNow', strtotime('2008-02-01 13:23:43'));	// This was a Friday!
				
		$this->assertEqual($formatter->format('2008-02-01'), 'Today');
		
		$this->assertEqual($formatter->format('2008-02-02'), 'Tomorrow');
		$this->assertEqual($formatter->format('2008-01-31'), 'Yesterday');
		
		$this->assertEqual($formatter->format('2008-02-03'), 'Next Sunday');
		$this->assertEqual($formatter->format('2008-01-30'), 'Last Wednesday');
		
		$this->assertEqual($formatter->format('2008-02-12'), 'Tue 12 Feb');
		$this->assertEqual($formatter->format('2008-01-21'), 'Mon 21 Jan');
		
		$this->assertEqual($formatter->format('2008-02-22'), '22 Feb');
		$this->assertEqual($formatter->format('2008-01-11'), '11 Jan');
		
		$this->assertEqual($formatter->format('2007-11-22'), '22 Nov 2007');
		$this->assertEqual($formatter->format('2007-11-11'), '11 Nov 2007');
		
		$this->assertEqual($formatter->format('2009-01-22'), '22 Jan 2009');
		$this->assertEqual($formatter->format('2009-01-11'), '11 Jan 2009');
	}
	
	function testWithTimes() {
		$formatter = new PrettyTimestampFormatter();
		
		$clock = new MockClock();
		$formatter->setClock($clock);
		$clock->setReturnValue('getNow', strtotime('2008-01-23 13:23:34'));	//wednesday
				
		$this->assertEqual($formatter->format('2008-01-23 13:23:34'), 'Just Now');
		$this->assertEqual($formatter->format('2008-01-23 13:23:32'), 'Just Now');
		$this->assertEqual($formatter->format('2008-01-23 13:21:35'), 'Just Now');
		
		$this->assertEqual($formatter->format('2008-01-23 13:21:34'), '2 minutes ago');
		$this->assertEqual($formatter->format('2008-01-23 13:20:13'), '3 minutes ago');
		$this->assertEqual($formatter->format('2008-01-23 12:24:13'), '59 minutes ago');
		
		$this->assertEqual($formatter->format('2008-01-23 12:22:13'), 'Today, 12:22');
		$this->assertEqual($formatter->format('2008-01-23 00:00:01'), 'Today, 00:00');
		
		$this->assertEqual($formatter->format('2008-01-22 23:59:59'), 'Yesterday, 23:59');
		$this->assertEqual($formatter->format('2008-01-22 00:00:01'), 'Yesterday, 00:00');
		
		$this->assertEqual($formatter->format('2008-01-21 23:59:59'), 'Last Monday, 23:59');
		$this->assertEqual($formatter->format('2008-01-17 13:30:23'), 'Last Thursday, 13:30');
		
		$this->assertEqual($formatter->format('2008-01-14 11:23:43'), 'Mon 14 Jan, 11:23');
		$this->assertEqual($formatter->format('2008-01-11 11:23:43'), 'Fri 11 Jan, 11:23');
		
		$this->assertEqual($formatter->format('2008-01-09 11:23:43'), '09 Jan, 11:23');
		$this->assertEqual($formatter->format('2008-01-02 11:23:43'), '02 Jan, 11:23');
		
		$this->assertEqual($formatter->format('2007-12-09 11:23:43'), '09 Dec 2007, 11:23');
		
		
		$this->assertEqual($formatter->format('2008-01-23 13:23:35'), 'Very Soon');
		$this->assertEqual($formatter->format('2008-01-23 13:25:33'), 'Very Soon');
		
		$this->assertEqual($formatter->format('2008-01-23 13:25:35'), 'In 2 minutes');
		$this->assertEqual($formatter->format('2008-01-23 13:27:35'), 'In 4 minutes');
		$this->assertEqual($formatter->format('2008-01-23 14:23:33'), 'In 59 minutes');
		
		$this->assertEqual($formatter->format('2008-01-23 14:23:35'), 'Today, 14:23');
		$this->assertEqual($formatter->format('2008-01-23 23:59:59'), 'Today, 23:59');
		
		$this->assertEqual($formatter->format('2008-01-24 00:00:01'), 'Tomorrow, 00:00');
		$this->assertEqual($formatter->format('2008-01-24 23:59:59'), 'Tomorrow, 23:59');
		
		$this->assertEqual($formatter->format('2008-01-25 14:23:35'), 'Next Friday, 14:23');
		$this->assertEqual($formatter->format('2008-01-29 14:23:35'), 'Next Tuesday, 14:23');
		
		$this->assertEqual($formatter->format('2008-01-30 14:23:35'), 'Wed 30 Jan, 14:23');
		$this->assertEqual($formatter->format('2008-02-05 14:23:35'), 'Tue 05 Feb, 14:23');
		
		$this->assertEqual($formatter->format('2008-02-10 14:23:35'), '10 Feb, 14:23');
		$this->assertEqual($formatter->format('2008-12-14 14:23:35'), '14 Dec, 14:23');
		
		$this->assertEqual($formatter->format('2009-02-10 14:23:35'), '10 Feb 2009, 14:23');
	}
	
	
	function testWithDifferentTimezone() {
		PrettyTimestampFormatter::setDefaultTimezone('Europe/Paris');
		
		$formatter = new PrettyTimestampFormatter();
		
		$clock = new MockClock();
		$formatter->setClock($clock);
		$clock->setReturnValue('getNow', strtotime('2008-01-23 13:23:34'));	//wednesday
		
		$this->assertEqual($formatter->format('2008-01-23 13:23:34'), 'Just Now');
		$this->assertEqual($formatter->format('2008-01-23 12:23:34'), 'Today, 13:23');
		$this->assertEqual($formatter->format('2008-01-23 14:23:34'), 'Today, 15:23');
		$this->assertEqual($formatter->format('2008-01-23 10:23:34'), 'Today, 11:23');
		
		$this->assertEqual($formatter->format('2008-01-22 23:59:59'), 'Today, 00:59');
		$this->assertEqual($formatter->format('2008-01-21 23:59:59'), 'Yesterday, 00:59');
		
		$this->assertEqual($formatter->format('2008-01-23 23:30:23'), 'Tomorrow, 00:30');
	}
	
	function testDatesWithTimezoneWhenCloseToMidnight() {
		PrettyTimestampFormatter::setDefaultTimezone('Europe/Paris'); //+1
		
		$formatter = new PrettyTimestampFormatter();
		
		$clock = new MockClock();
		$formatter->setClock($clock);
		$clock->setReturnValue('getNow', strtotime('2008-01-23 23:23:34 Europe/London'));	//wednesday
		
		$this->assertEqual($formatter->format('2008-01-23'), 'Yesterday');
		$this->assertEqual($formatter->format('2008-01-24'), 'Today');
		$this->assertEqual($formatter->format('2008-01-25'), 'Tomorrow');
		
		$this->assertEqual($formatter->format('2008-01-26'), 'Next Saturday');
	}
	
	function testDatesWithBiggerTimezoneOffset() {
		PrettyTimestampFormatter::setDefaultTimezone('Pacific/Tongatapu'); //+13
		
		$formatter = new PrettyTimestampFormatter();
		
		$clock = new MockClock();
		$formatter->setClock($clock);
		$clock->setReturnValue('getNow', strtotime('2008-01-23 11:23:34 Europe/London'));	//wednesday
		
		$this->assertEqual($formatter->format('2008-01-23'), 'Yesterday');
		$this->assertEqual($formatter->format('2008-01-24'), 'Today');
		$this->assertEqual($formatter->format('2008-01-25'), 'Tomorrow');
		
		$this->assertEqual($formatter->format('2008-01-26'), 'Next Saturday');
	}
	
	function testWithNegativeTimezone() {
		PrettyTimestampFormatter::setDefaultTimezone('Atlantic/Azores');
		
		$formatter = new PrettyTimestampFormatter();
		
		$clock = new MockClock();
		$formatter->setClock($clock);
		$clock->setReturnValue('getNow', strtotime('2008-01-23 13:23:34 Europe/London'));	//wednesday
		
		$this->assertEqual($formatter->format('2008-01-23 13:23:34'), 'Just Now');
		$this->assertEqual($formatter->format('2008-01-23 12:23:34'), 'Today, 11:23');
		$this->assertEqual($formatter->format('2008-01-23 14:23:34'), 'Today, 13:23');
		$this->assertEqual($formatter->format('2008-01-23 10:23:34'), 'Today, 09:23');
		
		$this->assertEqual($formatter->format('2008-01-24 00:59:59'), 'Today, 23:59');
		$this->assertEqual($formatter->format('2008-01-23 00:59:59'), 'Yesterday, 23:59');
		
		$this->assertEqual($formatter->format('2008-01-23 01:30:23'), 'Today, 00:30');
	}
	
	function testDatesWithNegativeOffset() {
		PrettyTimestampFormatter::setDefaultTimezone('Atlantic/Azores'); //-1
		
		$formatter = new PrettyTimestampFormatter();
		
		$clock = new MockClock();
		$formatter->setClock($clock);
		$clock->setReturnValue('getNow', strtotime('2008-01-10 00:23:34 Europe/London'));	//thursday in e/l
		
		$this->assertEqual($formatter->format('2008-01-08'), 'Yesterday'); // 8th, tues
		$this->assertEqual($formatter->format('2008-01-09'), 'Today'); // 9th, weds
		$this->assertEqual($formatter->format('2008-01-10'), 'Tomorrow'); // 10th, thurs
		$this->assertEqual($formatter->format('2008-01-11'), 'Next Friday'); // 11th, fri
		$this->assertEqual($formatter->format('2008-01-12'), 'Next Saturday'); // 12th, sat
	}
	
	function testWithLargerNegativeOffset() {
		PrettyTimestampFormatter::setDefaultTimezone('America/Los_Angeles'); //-8
		
		$formatter = new PrettyTimestampFormatter();
		
		$clock = new MockClock();
		$formatter->setClock($clock);
		$clock->setReturnValue('getNow', strtotime('2008-01-23 13:23:34  Europe/London'));	//wednesday
		
		$this->assertEqual($formatter->format('2008-01-23 13:23:34'), 'Just Now');
		$this->assertEqual($formatter->format('2008-01-23 12:23:34'), 'Today, 04:23');
		$this->assertEqual($formatter->format('2008-01-23 14:23:34'), 'Today, 06:23');
		$this->assertEqual($formatter->format('2008-01-23 10:23:34'), 'Today, 02:23');
		
		$this->assertEqual($formatter->format('2008-01-24 04:59:59'), 'Today, 20:59');
		
		$this->assertEqual($formatter->format('2008-01-23 06:59:59'), 'Yesterday, 22:59');
		
		$this->assertEqual($formatter->format('2008-01-22 05:30:23'), 'Last Monday, 21:30');
	}
	
}
?>