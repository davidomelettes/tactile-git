<?php

class ActivityTrack extends DataObject {
	
	public function __construct() {
		parent::__construct('activity_tracks');
		
		$this->validateUniquenessOf('name');
		
		$this->hasMany('ActivityTrackStage', 'stages', 'track_id');
	}
	
	public function getStageCount() {
		$db = DB::Instance();
		return $db->getOne("SELECT count(*) FROM activity_track_stages WHERE track_id = " . $db->qstr($this->id));
	}
	
	public function getReadString() {
		return 'by everyone';
	}
	
	public function getWriteString() {
		return 'by Admins only';
	}
	
	public function asJson() {
		$json = array();
		
		$string_fields = array('name', 'description', 'owner', 'alteredby');
		$int_fields = array('id');
		$boolean_fields = array();
		$formatted_fields = array();
		$datetime_fields = array('created', 'lastupdated');
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
		
		$stages = array();
		foreach ($this->stages as $stage) {
			$stages[] = $stage->asJson(false);
		}
		$json['stages'] = $stages;
		
		return json_encode($json);
	}
	
}
