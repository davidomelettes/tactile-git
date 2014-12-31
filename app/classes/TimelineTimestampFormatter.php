<?php

class TimelineTimestampFormatter extends PrettyTimestampFormatter {

	public function format($value) {
		if (empty($value)) {
			return "";
		}
		
		date_default_timezone_set('Europe/London');
		$now = $this->clock->getNow();
		
		$then = strtotime($value);
		
		$tz = PrettyTimestampFormatter::getDefaultTimezone();
		date_default_timezone_set($tz);
		
		if ('Monday' == date('l', $now)) {
			$start_of_week = strtotime("today $tz", $now);
			$start_of_previous_week = strtotime("-1 monday $tz", $now);
		} else {
			$start_of_week = strtotime("-1 monday $tz", $now);
			$start_of_previous_week = strtotime("-2 monday $tz", $now);
		}
		
		switch (true) {
			case $then > $now:
				return 'Future';
				
			case date('Y-m-d', $then) == date('Y-m-d', strtotime("tomorrow $tz", $now)):
				return 'Tomorrow';
				
			case date('Y-m-d', $then) == date('Y-m-d', strtotime("today $tz", $now)):
				return 'Today';
				
			case date('Y-m-d', $then) == date('Y-m-d', strtotime("yesterday $tz", $now)):
				return 'Yesterday';
				
			case date('Y-m-d', $then) >= date('Y-m-d', $start_of_week):
				return date('l', $then);
				
			case date('Y-m-d', $then) < date('Y-m-d', $start_of_week) && date('Y-m-d', $then) > date('Y-m-d', $start_of_previous_week):
				return 'Last Week';
				
			default:
				return 'Over a Week Ago';
		}
	}
	
}
