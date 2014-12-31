<?php
/**
 *
 * @author gj
 */
class TimeValidator implements FieldValidation {
	
	private $msg = '%s needs to be a valid time- with Hours and Minutes';
	
	public function __construct($msg=null) {
		if($msg!==null) {
			$this->msg = $msg;
		}
	}
	
	public function test(DataField $field, array &$errors=array(), $date_field=null) {
		$value = $field->value;
		if(empty($value)) {
			return $value;
		}
		$pattern = '#(\d\d?):(\d\d?)#';
		$valid = preg_match($pattern,$value,$matches);
		if($valid!==0) {
			$hours = $matches[1];
			$minutes = $matches[2];
			$valid = $hours < 24 && $minutes < 60;
			if($valid!==false) {
				date_default_timezone_set('Europe/London');
				
				$time_string = $hours.':'.$minutes.' '.CurrentlyLoggedInUser::Instance()->getTimezoneString();
				if (!is_null($date_field)) {
					$date_value = $date_field->value;
					if (!empty($date_value)) {
						// Evaluate the time with the context of this date
						// (need to know this in order to apply any DST correctly)
						$time_string = $date_value . ' ' . $hours.':'.$minutes.' '.CurrentlyLoggedInUser::Instance()->getTimezoneString();
					}
				}
				return date('H:i',strtotime($time_string));
			}
		}
		$errors[$field->name] =sprintf($this->msg,$field->tag);
		return false;
	}
}
?>