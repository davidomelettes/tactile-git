<?php

class Flag extends DataObject implements TimelineItem {
	
	public function __construct($tablename='flags') {
		parent::__construct($tablename);
		$this->belongsTo('Organisation');
		$this->belongsTo('Person');
		$this->belongsTo('Opportunity');
		$this->belongsTo('Activity');
	}
	
	public function asJson() {
		$json = array();
		
		$string_fields = array('title', 'organisation', 'person', 'opportunity', 'activity');
		$int_fields = array('id', 'organisation_id', 'person_id', 'opportunity_id', 'activity_id');
		$formatted_fields = array('owner');
		$datetime_fields = array('created');
		
		foreach ($string_fields as $field) {
			$value = $this->$field;
			$json[$field] = ((is_null($value) || '' === $value) ? null : (string) $value);
		}
		foreach ($int_fields as $field) {
			$value = $this->$field; 
			$json[$field] = ((is_null($value) || '' === $value) ? null : (int) $value);
		}
		foreach ($formatted_fields as $field) {
			$value = $this->getFormatted($field);
			$json[$field] = ((is_null($value) || '' === $value) ? null : $value);
		}
		foreach ($datetime_fields as $field) {
			$value = $this->$field;
			$json[$field] = ((is_null($value) || '' === $value) ? null : date('Y-m-d\TH:i:sO', strtotime($value)));
		}
		
		$value = $this->person_id;
		if (empty($value)) {
			$json['person_id'] = null;
			$json['person'] = null;
		} else {
			$json['person_id'] = (int) $value;
			$json['person'] = $this->person;
		}
		
		return json_encode(array('flag'=>$json));
	}
	
	public function getTimelineType() {
		return 'Flag';
	}
	
	public function getTimelineDate() {
		$formatter = new TimelineTimestampFormatter();
		return $formatter->format($this->created);
	}
	
	public function getTimelineTime() {
		return $this->created;
	}
	
	public function getTimelineSubject() {
		return $this->getFormatted('title');
	}
	
	public function getTimelineBody() {
		return '';
	}
	
	public function getTimelineURL() {
		$activity_id = $this->activity_id;
		$opportunity_id = $this->opportunity_id;
		$person_id = $this->person_id;
		$organisation_id = $this->organisation_id;
		if (!empty($activity_id)) {
			return '/activities/view/'.$activity_id;
		} elseif (!empty($opportunity_id)) {
			return '/opportunities/view/'.$opportunity_id;
		} elseif (!empty($person_id)) {
			return '/people/view/'.$person_id;
		} elseif (!empty($organisation_id)) {
			return '/organisations/view/'.$organisation_id;
		} else {
			return '';
		}
	}
}
