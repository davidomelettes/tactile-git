<?php

/**
 * vEvent item for inserting into a vCalendar
 *
 */
class VCalEvent extends VCalObject {
	
	/**
	 * Start date
	 * Specify either using YYYYMMDD or YYYYMMDDTHHMMSS
	 *
	 * @var string
	 */
	private $_dt_start;
	
	/**
	 * End date
	 * Specify either using YYYYMMDD or YYYYMMDDTHHMMSS
	 *
	 * @var string
	 */
	private $_dt_end;
	
	public function setDateStart($date, $with_time=true) {
		date_default_timezone_set(CurrentlyLoggedInUser::Instance()->getTimezoneString());
		if ($with_time) {
			$this->_dt_start = date('Ymd\THi00', strtotime($date.' Europe/London'));
		} else {
			$this->_dt_start = date('Ymd', strtotime($date));
		}
	}
	
	public function setDateEnd($date, $with_time=true) {
		date_default_timezone_set(CurrentlyLoggedInUser::Instance()->getTimezoneString());
		if ($with_time) {
			$this->_dt_end = date('Ymd\THi00', strtotime($date.' Europe/London'));
		} else {
			$this->_dt_end = date('Ymd', strtotime($date));
		}
	}
	
	/**
	 * Output vEvent item as a string
	 *
	 * @return string
	 */
	public function toString() {
		$output = "BEGIN:VEVENT\r\n";
		$output .= "DTSTAMP:" . date('Ymd\THis') . "\r\n";
		
		$output .= "SUMMARY:{$this->escapeString($this->_name)}\r\n";
		
		if (!empty($this->_dt_start)) {
			$output .= "DTSTART:" . $this->_dt_start . "\r\n";
		}
		
		if (!empty($this->_dt_end)) {
			$output .= "DTEND:" . $this->_dt_end . "\r\n";
		}
		
		if (!empty($this->_location)) {
			$output .= "LOCATION:" . $this->escapeString($this->_location) . "\r\n";
		}
		
		if (!empty($this->_description)) {
			$output .= "DESCRIPTION:" . $this->escapeString($this->_description) . "\r\n";
		}
		
		$output .= "END:VEVENT";
		
		return $output;
	}
}
