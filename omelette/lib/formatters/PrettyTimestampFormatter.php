<?php
/**
 * Formats a date/time into a 'relative' phrase for recent dates
 * This implements DateFieldFormatter and TimestampFieldFormatter so that it can be injected and used as the default
 * @author gj
 */
class PrettyTimestampFormatter implements TimestampFieldFormatter, DateFieldFormatter {
	
	
	private $use_timezone = true;
	
	
	protected static $timezone = 'Europe/London';
	
	public function __construct($use_timezone = true) {
		$this->use_timezone = $use_timezone;
		$this->clock = new OmeletteClock();
	}
	
	public static function setDefaultTimezone($tz) {
		self::$timezone = $tz;
	}
	
	public static function getDefaultTimezone() {
		return self::$timezone;
	}
	
	public function setClock($clock) {
		$this->clock = $clock;
	}
	
	public function format($value) {
		if (empty($value)) {
			return "";
		}
		
		date_default_timezone_set('Europe/London');
		$now = $this->clock->getNow();
		if (FALSE === ($current_user = CurrentlyLoggedInUser::Instance())) {
			$time_format = '24h';
		} else {
			$time_format = Omelette_Magic::getValue('time_format', $current_user->getRawUsername(), '24h');
		}
		
		$time_part = (strpos($value,':') !== false);
		if ($this->use_timezone && !$time_part) {
			// Without a time component, dates are relative to user's timezone, not server's
			date_default_timezone_set(self::$timezone);
		}
		$then = strtotime($value);
		
		$diff = $now - $then;
		$minutes = $diff/60;
		$days = (int)$diff/(60*60*24);
		
		if ($this->use_timezone && $time_part) {
			$tz = self::$timezone;
		} else {
			$tz = 'Europe/London';
		}
		date_default_timezone_set($tz);
		
		switch(true) {
			//when we just have a date, the strings will be different
			case !$time_part: {
				if ($this->use_timezone) {
					$tz = self::$timezone;
					date_default_timezone_set($tz);
				}
				switch(true) {
					case date('Y-m-d',$then) == date('Y-m-d',strtotime("today $tz", $now)):
						return 'Today';
					case date('Y-m-d',$then) == date('Y-m-d',strtotime("tomorrow $tz", $now)):
						return 'Tomorrow';
					case date('Y-m-d',$then) == date('Y-m-d',strtotime("yesterday $tz", $now)):
						return 'Yesterday';
					case $days>-7 && $days<0:
						return 'Next '.date('l',$then);
					case $days>0 && $days<7:
						return 'Last '.date('l',$then);
					case $days>-14 && $days<14:
						return date('D d M',$then);
					case date('Y',$then) == date('Y', $now):
						return date('d M',$then);
					default:
						return date('d M Y',$then);
				}
				break;
			}
			//with times, there are more possibilities and the +- differences get confusing if they're lumped together
			case $diff>=0: {
				switch(true) {
					//these are in the past (or, if you're really, really quick the present!)
					case $diff < 120:
						return 'Just Now';
					case $minutes < 60:
						return (int)$minutes.' minutes ago';
					case date('Y-m-d', $then) == date('Y-m-d',strtotime("today $tz", $now)):
						$format = $time_format === '12h' ? 'g:ia' : 'H:i';
						return 'Today, '.date($format, $then);
					case date('Y-m-d', $then) == date('Y-m-d',strtotime("yesterday $tz", $now)):
						$format = $time_format === '12h' ? 'g:ia' : 'H:i';
						return 'Yesterday, '.date($format, $then);
					case $days < 7:
						$format = $time_format === '12h' ? 'l, g:ia' : 'l, H:i';
						return 'Last '.date($format, $then);
					case $days < 14:
						$format = $time_format === '12h' ? 'D d M, g:ia' : 'D d M, H:i';
						return date($format, $then);
					case date('Y', $then) == date('Y', $now):
						$format = $time_format === '12h' ? 'd M, g:ia' : 'd M, H:i';
						return date($format, $then);
					default:
						$format = $time_format === '12h' ? 'd M Y, g:ia' : 'd M Y, H:i';
						return date($format, $then);
				}
				break;
			}
			case $diff < 0: {
				//these are in the future
				switch(true) {
					case $diff > -120:
						return 'Very Soon';
					case $minutes > -60:
						return 'In '.(int)abs($minutes).' minutes';
					case date('Y-m-d',$then)==date('Y-m-d',strtotime("today $tz", $now)):
						$format = $time_format === '12h' ? 'g:ia' : 'H:i';
						return 'Today, '.date($format, $then);
					case date('Y-m-d',$then)==date('Y-m-d',strtotime("tomorrow $tz", $now)):
						$format = $time_format === '12h' ? 'g:ia' : 'H:i';
						return 'Tomorrow, '.date($format, $then);
					case $days > -7:
						$format = $time_format === '12h' ? 'l, g:ia' : 'l, H:i';
						return 'Next '.date($format, $then);
					case $days > -14:
						$format = $time_format === '12h' ? 'D d M, g:ia' : 'D d M, H:i';
						return date($format, $then);
					case date('Y',$then) == date('Y', $now):
						$format = $time_format === '12h' ? 'd M, g:ia' : 'd M, H:i';
						return date($format, $then);
					default:
						$format = $time_format === '12h' ? 'd M Y, g:ia' : 'd M Y, H:i';
						return date($format, $then);
				}				
			}
		}
	}
}
