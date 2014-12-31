<?php

class TestOfTimeIntervalInWords extends UnitTestCase {
	
	function setup() {
		parent::setup();
	}
	
	function testOfDurations() {
		
		$interval = new TimeIntervalInWords(0, 0);
		$this->assertEqual($interval->getInterval(), 'none');
		
		$interval->setTimes(0, 1);
		$this->assertEqual($interval->getInterval(), '1 second');
		
		$interval->setTimes(1, 0);
		$this->assertEqual($interval->getInterval(), '1 second');
		
		$interval->setTimes(-1, 0);
		$this->assertEqual($interval->getInterval(), '1 second');
		
		$interval->setTimes(0, -1);
		$this->assertEqual($interval->getInterval(), '1 second');
		
		$interval->setTimes(0, 1);
		$this->assertEqual($interval->getInterval(1), '1 second');
		
		$interval->setTimes(0, 1);
		$this->assertEqual($interval->getInterval(2), '1 second');
		
		$interval->setTimes(0, 1);
		$this->assertEqual($interval->getInterval(999), '1 second');
		
		$interval->setTimes(0, 60);
		$this->assertEqual($interval->getInterval(), '1 minute');
		
		$interval->setTimes(0, 61);
		$this->assertEqual($interval->getInterval(), '1 minute and 1 second');
		
		$interval->setTimes(0, 61);
		$this->assertEqual($interval->getInterval(1), '1 minute and 1 second');
		
		$interval->setTimes(0, 61);
		$this->assertEqual($interval->getInterval(2), '1 minute and 1 second');
		
		$interval->setTimes(0, 120);
		$this->assertEqual($interval->getInterval(), '2 minutes');
		
		$interval->setTimes(0, 121);
		$this->assertEqual($interval->getInterval(), '2 minutes and 1 second');
		
		$interval->setTimes(0, 121);
		$this->assertEqual($interval->getInterval(2), '2 minutes and 1 second');
		
		$interval->setTimes(0, 122);
		$this->assertEqual($interval->getInterval(2), '2 minutes and 2 seconds');
		
		$interval->setTimes(0, 359);
		$this->assertEqual($interval->getInterval(), '5 minutes and 59 seconds');
		
		// Over 6 minutes loses the automatic seconds inclusion...
		$interval->setTimes(0, 361);
		$this->assertEqual($interval->getInterval(), 'more than 6 minutes');
		
		// ...But this can be overidden
		$interval->setTimes(0, 361);
		$this->assertEqual($interval->getInterval(2), '6 minutes and 1 second');
		
		$interval->setTimes(0, 3600);
		$this->assertEqual($interval->getInterval(), '1 hour');
		
		// Seconds shouldn't display when we are over one hour...
		$interval->setTimes(0, 3601);
		$this->assertEqual($interval->getInterval(), 'more than 1 hour');
		
		$interval->setTimes(0, 3601);
		$this->assertEqual($interval->getInterval(2), '1 hour and 1 second');
		
		// ...But minutes should...
		$interval->setTimes(0, 3660);
		$this->assertEqual($interval->getInterval(), '1 hour and 1 minute');
		
		$interval->setTimes(0, 3660);
		$this->assertEqual($interval->getInterval(2), '1 hour and 1 minute');
		
		$interval->setTimes(0, 3661);
		$this->assertEqual($interval->getInterval(), '1 hour and more than 1 minute');
		
		$interval->setTimes(0, 3661);
		$this->assertEqual($interval->getInterval(2), '1 hour and more than 1 minute');
		
		$interval->setTimes(0, 21540);
		$this->assertEqual($interval->getInterval(), '5 hours and 59 minutes');
		
		// ...Until we hit 6 hours...
		$interval->setTimes(0, 21661);
		$this->assertEqual($interval->getInterval(), 'more than 6 hours');
		
		// ...But as before, this can be overridden
		$interval->setTimes(0, 21661);
		$this->assertEqual($interval->getInterval(2), '6 hours and more than 1 minute');
		
		$interval->setTimes(0, 21661);
		$this->assertEqual($interval->getInterval(3), '6 hours, 1 minute and 1 second');
		
		$interval->setTimes(0, 86400);
		$this->assertEqual($interval->getInterval(), '1 day');
		
		$interval->setTimes(0, 86401);
		$this->assertEqual($interval->getInterval(), 'more than 1 day');
		
		$interval->setTimes(0, 86401);
		$this->assertEqual($interval->getInterval(2), '1 day and 1 second');
		
		$interval->setTimes(0, 86460);
		$this->assertEqual($interval->getInterval(), 'more than 1 day');
		
		$interval->setTimes(0, 86460);
		$this->assertEqual($interval->getInterval(2), '1 day and 1 minute');
		
		$interval->setTimes(0, 86461);
		$this->assertEqual($interval->getInterval(), 'more than 1 day');
		
		$interval->setTimes(0, 86461);
		$this->assertEqual($interval->getInterval(2), '1 day and more than 1 minute');
		
		$interval->setTimes(0, 86461);
		$this->assertEqual($interval->getInterval(3), '1 day, 1 minute and 1 second');
		
		// Less than two days should also display the hours
		$interval->setTimes(0, 90000);
		$this->assertEqual($interval->getInterval(), '1 day and 1 hour');
		
		$interval->setTimes(0, 93600);
		$this->assertEqual($interval->getInterval(), '1 day and 2 hours');
		
		$interval->setTimes(0, 93661);
		$this->assertEqual($interval->getInterval(), '1 day and more than 2 hours');
		
		$interval->setTimes(0, 93661);
		$this->assertEqual($interval->getInterval(2), '1 day and more than 2 hours');
		
		$interval->setTimes(0, 93661);
		$this->assertEqual($interval->getInterval(3), '1 day, 2 hours and more than 1 minute');
		
		$interval->setTimes(0, 93661);
		$this->assertEqual($interval->getInterval(4), '1 day, 2 hours, 1 minute and 1 second');
		
		$interval->setTimes(0, 172800);
		$this->assertEqual($interval->getInterval(), '2 days');
		
		$interval->setTimes(0, 172801);
		$this->assertEqual($interval->getInterval(), 'more than 2 days');
		
		$interval->setTimes(0, 176400);
		$this->assertEqual($interval->getInterval(), 'more than 2 days');
		
		$interval->setTimes(0, 176461);
		$this->assertEqual($interval->getInterval(), 'more than 2 days');
		
		$interval->setTimes(0, 172801);
		$this->assertEqual($interval->getInterval(2), '2 days and 1 second');
		
		$interval->setTimes(0, 176461);
		$this->assertEqual($interval->getInterval(2), '2 days and more than 1 hour');
		
		$interval->setTimes(0, 176461);
		$this->assertEqual($interval->getInterval(3), '2 days, 1 hour and more than 1 minute');
		
		$interval->setTimes(0, 176461);
		$this->assertEqual($interval->getInterval(4), '2 days, 1 hour, 1 minute and 1 second');
		
		$interval->setTimes(0, 86400000);
		$this->assertEqual($interval->getInterval(), '1000 days');
		
		// Prent automatic inclusion of less significant units
		$interval->setTimes(0, 3660);
		$this->assertEqual($interval->getInterval(1, false), 'more than 1 hour');
	}
	
}
