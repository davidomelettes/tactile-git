<?php

/**
 *
 */
class Email extends DataObject implements TimelineItem {

	/**
	 * 
	 *@param $tablename	string	The name of a table in the database 
	 */
	public function __construct($tablename='emails') {
		parent::__construct($tablename);
		$this->hasMany('S3File', 'files', 'f.email_id');
		$this->belongsTo('Organisation');
		$this->belongsTo('Person');
		$this->belongsTo('Opportunity');
		$this->orderby = 'received';
		$this->orderdir = 'desc';
		
		$this->getField('body')->setFormatter(new EmailBodyFormatter());
		$this->setAdditional('direction');
		$this->setAdditional('email_attachments');
		
		$this->getField('received')->blockValidator('DateValidator'); // Because this is STUPID
	}
	
	/**
	 * Returns whether this email is incoming or outgoing
	 *
	 * @return string
	 */
	public function getDirection() {
		$owner = new User();
		$owner->loadby('username', $this->owner);
		
		// Owner shares 'to' email address
		$db = DB::Instance();
		$query = "SELECT person_id
			FROM person_contact_methods pcm
			LEFT JOIN people p ON p.id = pcm.person_id
			WHERE p.usercompanyid = " . $db->qstr(EGS::getCompanyId()) . "
			AND p.id = " . $db->qstr($owner->person_id) . "
			AND pcm.type = 'E' AND pcm.contact ILIKE " . $db->qstr($this->email_to); 
		$t = $db->getOne($query);
		
		// Owner shares 'from' email address
		$query = "SELECT person_id
			FROM person_contact_methods pcm
			LEFT JOIN people p ON p.id = pcm.person_id
			WHERE p.usercompanyid = " . $db->qstr(EGS::getCompanyId()) . "
			AND p.id = " . $db->qstr($owner->person_id) . "
			AND pcm.type = 'E' AND pcm.contact ILIKE " . $db->qstr($this->email_from);
		$f = $db->getOne($query);
		
		if (!empty($t)) {
			return 'incoming';
		}
		if (!empty($f)) {
			return 'outgoing';
		} 
		return '';
	}
	
	public function asJson() {
		$json = array();
		
		$string_fields = array('subject', 'email_from', 'email_to', 'opportunity', 'organisation');
		$int_fields = array('id', 'opportunity_id', 'organisation_id');
		$formatted_fields = array('owner');
		$datetime_fields = array('received', 'created');
		
		$value = $this->body;
		$json['body'] = ((is_null($value) || '' === $value) ? null : (string) input_verify_utf8($value));
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
		
		return json_encode(array('email'=>$json));
	}
	
	public function getTimelineType() {
		return 'Email';
	}
	
	public function getTimelineDate() {
		$formatter = new TimelineTimestampFormatter();
		return $formatter->format($this->received);
	}
	
	public function getTimelineTime() {
		return $this->received;
	}
	
	public function getTimelineSubject() {
		return $this->getFormatted('subject');
	}
	
	public function getTimelineBody() {
		return $this->getFormatted('body');
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
			return '/emails/assign/'.$this->id;
		}
	}
	
}
