<?php

/**
 * Represents the difference between two unix timestamps (or any arbitrary pair of numbers of seconds as integers)
 * as a duration, using English words (e.g. "4 hours")
 */
class TimeIntervalInWords {
	
	const LN_MIN	= 60;
	const LN_HOUR	= 3600;
	const LN_DAY	= 86400;
	
	private $_start_ts;
	private $_end_ts;
	
	/**
	 * Set the start and end times, defaulting to the current time
	 *
	 * @param int $start_ts
	 * @param int $end_ts
	 */
	public function __construct($start_ts=null, $end_ts=null) {
		$this->setTimes($start_ts, $end_ts);
	}
	
	public function setTimes($start_ts=null, $end_ts=null) {
		if (isset($start_ts)) {
			$this->_start_ts = $start_ts;
		} else {
			$this->_start_ts = time();
		}
		if (isset($end_ts)) {
			$this->_end_ts = $end_ts;
		} else {
			$this->_end_ts = time();
		}
	}
	
	/**
	 * Return the size of the interval in words
	 *
	 * @param int $accuracy Number of units to specify (e.g. 1: "more than 3 days", 2: "3 days and 2 hours")
	 * @param boolean $auto_dig Whether to automatically include a less significant unit if the current unit is below a certain threshold
	 * @return string
	 */
	public function getInterval($accuracy=1, $auto_dig=true) {
		
		$duration = abs($this->_end_ts - $this->_start_ts);
		
		$stack = $this->_chopTime($duration);
		
		$units = array();
		if ($stack['days']) {
			$accuracy--;
			$units[] = $stack['days'] . ' day' . ($stack['days'] > 1 ? 's' : '');
			// Display hours if fewer than 2 days
			if ($auto_dig && !$accuracy && $stack['days'] < 2 && $stack['hours']) {
				$units[] = $stack['hours'] . ' hour' . ($stack['hours'] > 1 ? 's' : '');
			}
		}
		
		if ($stack['hours'] && $accuracy) {
			$accuracy--;
			$units[] = $stack['hours'] . ' hour' . ($stack['hours'] > 1 ? 's' : '');
			// Display minutes if fewer than 6 hours
			if ($auto_dig && !$accuracy && $stack['hours'] < 6 && $stack['minutes'] && !$stack['days']) {
				$units[] = $stack['minutes'] . ' minute' . ($stack['minutes'] > 1 ? 's' : '');
			}
		}
		
		if ($stack['minutes'] && $accuracy) {
			$accuracy--;
			$units[] = $stack['minutes'] . ' minute' . ($stack['minutes'] > 1 ? 's' : '');
			// Display seconds if fewer than 6 minutes
			if ($auto_dig && !$accuracy && $stack['minutes'] < 6 && $stack['seconds'] && (!$stack['hours'] && !$stack['days'])) {
				$units[] = $stack['seconds'] . ' second' . ($stack['seconds'] > 1 ? 's' : '');
			}
		}
		
		if ($stack['seconds'] && $accuracy) {
			$accuracy--;
			$units[] = $stack['seconds'] . ' second' . ($stack['seconds'] > 1 ? 's' : '');
		}
		
		if ($units) {
			$output = array_pop($units);
			if (
				(FALSE !== strpos($output, 'day') && ($stack['hours'] || $stack['minutes'] || $stack['seconds'])) ||
				(FALSE !== strpos($output, 'hour') && ($stack['minutes'] || $stack['seconds'])) ||
				(FALSE !== strpos($output, 'minute') && $stack['seconds'])
			) {
				$output = 'more than ' . $output;
			}
			if ($units) {
				$output = implode(', ', $units) . ' and ' . $output;
			}
		}
		
		if (empty($output)) {
			$output = 'none';
		}
		
		return $output;
	}
	
	/**
	 * Segment a duration in seconds into a number of days, hours, minutes, and seconds
	 *
	 * @param int $duration Duration in seconds
	 * @return array
	 */
	private function _chopTime($duration) {
		
		$stack = array();
		
		if ($duration >= self::LN_DAY) {
			// Days
			$days = floor($duration / self::LN_DAY);
			
			$stack['days'] = $days;
		
			$duration %= self::LN_DAY;
		} else {
			$stack['days'] = 0;
		}
		
		if ($duration >= self::LN_HOUR) {
			// Hours
			$hours = floor($duration / self::LN_HOUR);

			$stack['hours'] = $hours;
			
			$duration %= self::LN_HOUR;
		} else {
			$stack['hours'] = 0;
		}
		
		if ($duration >= self::LN_MIN) {
			// Minutes
			$mins = floor($duration / self::LN_MIN);
			
			$stack['minutes'] = $mins;
			
			$duration %= self::LN_MIN;
		} else {
			$stack['minutes'] = 0;
		}

		if ($duration >= 1) {
			// Seconds
			$secs = $duration;

			$stack['seconds'] = $secs;
			
			$duration = 0;
		} else {
			$stack['seconds'] = 0;
		}
		
		return $stack;
		
	}
	
}
