<?php

/**
 * vCalendar/iCalendar class for listing events and todo items
 */
class VCalendar {
	
	/**
	 * Collection of VCalObject items
	 *
	 * @var array
	 */
	private $_items = array();
	
	public function __construct() {
		
	}
	
	/**
	 * Add a VCalObject item to the calendar
	 *
	 * @param VCalObject $item
	 */
	public function addItem($item) {
		if ($item instanceof VCalObject) {
			$this->_items[] = $item;
		} else {
			throw new Exception('Argument is not of type VCalObject!');
		}
	}
	
	/**
	 * Output the calendar as a string
	 *
	 * @return string
	 */
	public function toString($title=null) {
		$output = "BEGIN:VCALENDAR\r\n" .
			"PRODID:-//omelett.es//Tactile//EN\r\n" .
			"VERSION:2.0\r\n" .
			"CALSCALE:GREGORIAN\r\n" .
			"METHOD:PUBLISH\r\n" . 
			"X-WR-TIMEZONE:".CurrentlyLoggedInUser::Instance()->getTimezoneString()."\r\n" .
			(!empty($title) ? "X-WR-CALNAME:$title\r\n" : '');
			
		foreach ($this->_items as $item) {
			$output .= $item->toString() . "\r\n";
		}
		
		$output .= 'END:VCALENDAR';
		
		return $output;
	}
	
}
