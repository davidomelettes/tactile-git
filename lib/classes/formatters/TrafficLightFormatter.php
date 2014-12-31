<?php
class TrafficLightFormatter implements FieldFormatter {
	public $is_safe=true;
	function format($value) {
		if(!($value=='red'||$value=='amber'||$value=='green')) {
			return '-';
		}
		$value='<img src="/themes/default/graphics/'.$value.'.png" alt="'.$value.'" />';
		return $value;
	}
	

}

?>