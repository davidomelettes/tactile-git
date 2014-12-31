<?php

/**
 * vTodo item for inserting into a vCalendar
 */
class VCalTodo extends VCalObject {
	
	/**
	 * Due date/time
	 * Specify either using YYYYMMDD or YYYYMMDDTHHMMSS
	 *
	 * @var string
	 */
	private $_dt_due;
	
	/**
	 * The date the Todo was completed
	 *
	 * @var string
	 */
	private $_completed;
	
	public function setDateDue($date) {
		date_default_timezone_set(CurrentlyLoggedInUser::Instance()->getTimezoneString());
                $this->_dt_due = date('Ymd\THi00', strtotime($date.' Europe/London'));
	}
	
	/**
	 * Set the Todo's completed-date
	 *
	 * @param string $completed
	 */
	public function setCompleted($completed) {
		$this->_completed = $completed;
	}
	
	/**
	 * Output vTodo item as a string
	 *
	 * @return string
	 */
	public function toString() {
		$output = "BEGIN:VTODO\r\n";
		$output .= "DTSTAMP:" . date('Ymd\THis') . "\r\n";
		
		$output .= "SUMMARY:{$this->escapeString($this->_name)}\r\n";
		
		if (!empty($this->_dt_due)) {
			$output .= "DUE:" . $this->_dt_due . "\r\n";
		}
		
		if(!empty($this->_completed)) {
			$output .= "COMPLETED:" . $this->_completed."\r\n";
		}
		
		if (!empty($this->_description)) {
			$output .= "DESCRIPTION:" . $this->escapeString($this->_description) . "\r\n";
		}
		
		$output .= "END:VTODO";
		
		return $output;
	}
}
