<?php

class ActivityTrackStage extends DataObject {

	public function __construct() {
		parent::__construct('activity_track_stages');
		
		$this->belongsTo('Activitytype', 'type_id', 'type');
		$this->belongsTo('User', 'assigned_to', 'stage_assigned_to');
		
		$this->getField('x_days')->addValidator(new NumericRangeValidator(0, 3560));
		$this->getField('assigned_to')->setFormatter(new UsernameFormatter());
	}
	
	public function getDueDate() {
		$x_days = $this->x_days;
		return date(EGS::getDateFormat(), strtotime('+'.$x_days.' days'));
	}
	
	public function asJson($asJson=true) {
		$json = array();
		
		$string_fields = array('name', 'description', 'type', 'alteredby', 'assigned_to');
		$int_fields = array('id', 'type_id', 'x_days');
		$boolean_fields = array();
		$formatted_fields = array();
		$datetime_fields = array();
		$date_fields = array();
		
		foreach ($string_fields as $field) {
			$value = $this->$field;
			$json[$field] = ((is_null($value) || '' === $value) ? null : (string) $value);
		}
		foreach ($int_fields as $field) {
			$value = $this->$field; 
			$json[$field] = ((is_null($value) || '' === $value) ? null : (int) $value);
		}
		foreach ($boolean_fields as $field) {
			$json[$field] = $this->{'is_'.$field}();
		}
		foreach ($formatted_fields as $field) {
			$value = $this->getFormatted($field);
			$json[$field] = ((is_null($value) || '' === $value) ? null : $value);
		}
		foreach ($datetime_fields as $field) {
			$value = $this->$field;
			$json[$field] = ((is_null($value) || '' === $value) ? null : date('Y-m-d\TH:i:sO', strtotime($value)));
		}
		foreach ($date_fields as $field) {
			$value = $this->$field;
			$json[$field] = ((is_null($value) || '' === $value) ? null : date('Y-m-d', strtotime($value)));
		}
		
		$due = $this->getDueDate();
		$json['date'] = $due;
		
		return $asJson ? json_encode($json) : $json;
	}
	
}
