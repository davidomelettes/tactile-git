<?php

class Note extends DataObject implements TimelineItem {
	protected $defaultDisplayFields = array('owner','created','organisation','person','opportunity','activity','title','note');
	public function __construct() {
		parent::__construct('notes');
		$this->belongsTo('Organisation');
		$this->belongsTo('Person');
		$this->belongsTo('Opportunity');
		$this->belongsTo('Activity');
		$this->orderby = 'lastupdated';
		$this->orderdir = 'desc';
		$this->getField('note')->setFormatter(new URLParsingFormatter());
	}

	public function asJson() {
		$json = array();
		
		$string_fields = array('title', 'note', 'opportunity', 'activity', 'organisation', 'owner');
		$int_fields = array('id', 'opportunity_id', 'activity_id', 'organisation_id');
		$boolean_fields = array('private');
		$datetime_fields = array('created', 'lastupdated');
		
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
		
		return json_encode(array('note'=>$json));
	}
	
	public function getTimelineType() {
		return 'Note';
	}
	
	public function getTimelineDate() {
		$formatter = new TimelineTimestampFormatter();
		return $formatter->format($this->lastupdated);
	}
	
	public function getTimelineTime() {
		return $this->created;
	}
	
	public function getTimelineSubject() {
		return $this->getFormatted('title');
	}
	
	public function getTimelineBody() {
		return $this->getFormatted('note');
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
