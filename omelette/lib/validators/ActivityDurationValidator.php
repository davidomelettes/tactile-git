<?php

class ActivityDurationValidator implements ModelValidation{
	
	private $start_date = 'date';
	private $end_date = 'end_date';
	private $start_time = 'time';
	private $end_time = 'end_time';
	
	function test(DataObject $do, Array &$errors) {
		$start_date = $do->getField($this->start_date)->value;
		$end_date = $do->getField($this->end_date)->value;
		$start_time = $do->getField($this->start_time)->value;
		$end_time = $do->getField($this->end_time)->value;
		
		// Don't both checking todos
		if ($do->getField('class')->value == 'todo') {
			return $do;
		}
		
		// Remember the orginal values for reporting later if erroring
		$start_time_val = $start_time;
		$end_time_val = $end_time;
		
		if (empty($start_date) || empty($end_date)) {
			return $do;
		}
		
		if (empty($end_time)) {
			if ($start_date == $end_date) {
				// strtotime() returns midnight if just a date is given.
				// So, if a start time is specified but not an end time,
				// we need to ensure the end time is always after the start time if the two days are the same.
				$end_time = '23:59:59';
			} else {
				$end_time = '00:00:00';
			}
		}
		if (empty($start_time)) {
			$start_time = '00:00:00';
		}
		
		$start = $start_date . ' ' . $start_time;
		$end = $end_date . ' ' . $end_time;
		
		$start_ts = strtotime($start);
		$end_ts = strtotime($end);
		
		if ($start_ts === FALSE || $end_ts === FALSE) {
			$errors[] = "There was a problem trying to parse one of your times: ($start_date $start_time_val - $end_date $end_time_val)";
			return false;
		}
		
		if ($end_ts < $start_ts) {
			$errors[] = "The end time ($end_date $end_time_val) cannot be before the start ($start_date $start_time_val)";
			return false;
		}
		
		return $do;
	}
}
