<?php
/**
 * Model the db-stored bits of S3-stored files
 * 
 * @author gj
 */
class S3File extends DataObject implements TimelineItem {

	/**
	 * Constructor
	 * 
	 *@param $tablename	string	The name of a table in the database 
	 */
	public function __construct($tablename = 's3_files') {
		parent::__construct($tablename);
		$this->getField('size')->setFormatter(new FilesizeFormatter());
		$this->orderby = 'lower(filename)';
		
		$this->belongsTo('Organisation', 'organisation_id', 'organisation');
		$this->belongsTo('Person', 'person_id', 'person');
		$this->belongsTo('Opportunity', 'opportunity_id', 'opportunity');
		$this->belongsTo('Activity', 'activity_id', 'activity');
		$this->belongsTo('Email', 'email_id', 'email');
		
		$this->getField('filename')->setFormatter(new FilenameFormatter());
		
		//$this->addValidator(new FileLimitValidator());
	}
	
	/**
	 * Create a model for a file based on the contents of the $_FILES array
	 *
	 * @param Array $upload_data
	 * @return S3File
	 */
	public static function Create($file_data, &$errors = array()) {
		if(!defined('S3_DEFAULT_BUCKET')) {
			throw new Exception("The S3File Model assumes the existence of the S3_DEFAULT_BUCKET constant, so define one!");
		}
		$file = new S3File();
		$db = DB::Instance();
		$file_data['id'] = $db->GenID($file->getTableName().'_id_seq');
		
		$file_data['content_type'] = empty($file_data['content_type']) ? mime_content_type($file_data['tmp_name']) : $file_data['content_type'];
		if (empty($file_data['content_type'])) {
			$file_data['content_type'] = 'application/octet-stream';
		}
		$file_data['size'] = filesize($file_data['tmp_name']);
		$file_data['extension'] = strtolower(strrchr($file_data['name'],"."));
		$file_data['filename'] = $file_data['name'];
		$file_data['bucket'] = (isset($file_data['bucket']) ? $file_data['bucket'] : S3_DEFAULT_BUCKET);
		$file_data['object'] = EGS::getCompanyId().'/'.$file_data['id'].'/'.$file_data['name'];
		
		$file = DataObject::Factory($file_data, $errors, $file);
		return $file;
	}
	
	/**
	 * Returns an S3_Value_Object for the model, suitable for sending to S3
	 *
	 * @param String $path The filepath of the file
	 * @return S3_Value_Object
	 */
	public function getValueObject($path) {
		$object = S3_Value_Object::create($this->object, $this->bucket, $path, $this->content_type);
		return $object;
	}
	
	public function getAttachedTo() {
		$attached_to = array();
		$attachments = array(
			'tickets'		=> 'ticket_id',
			'activities'	=> 'activity_id', 
			'opportunities'	=> 'opportunity_id',
			'people'		=> 'person_id',
			'organisations'	=> 'organisation_id'
		);
		foreach ($attachments as $controller => $fkey) {
			$val = $this->{$fkey};
			if (!empty($val)) {
				$attached_to[$controller] = $this->{$fkey};
			}
		}
		return $attached_to;
	}
	
	public static function getUsage($usercompanyid) {
		$db = DB::Instance();
		$query = 'SELECT COALESCE(sum(size), 0) FROM s3_files WHERE usercompanyid='.$db->qstr($usercompanyid);
		$total = $db->GetOne($query);
		return $total;
	}
	
	public function canDelete() {
		return isModuleAdmin() || $this->owner == EGS::getUsername();
	}
	
	public function getTimelineType() {
		return 'File';
	}
	
	public function getTimelineDate() {
		$formatter = new TimelineTimestampFormatter();
		return $formatter->format($this->created);
	}
	
	public function getTimelineTime() {
		return $this->created;
	}
	
	public function getTimelineSubject() {
		return $this->getFormatted('filename');
	}
	
	public function getTimelineBody() {
		return $this->getFormatted('comment');
	}
	
	public function getTimelineURL() {
		return '/files/get/'.$this->id;
	}
	
	public function asJson() {
		$json = array();
		
		$string_fields = array('filename', 'comment', 'organisation', 'person', 'opportunity', 'activity', 'owner');
		$int_fields = array('id', 'size', 'organisation_id', 'person_id', 'opportunity_id', 'activity_id');
		$formatted_fields = array();
		$boolean_fields = array();
		$datetime_fields = array('created');
		$date_fields = array();
		
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
		foreach ($boolean_fields as $field) {
			$json[$field] = $this->{'is_'.$field}();
		}
		foreach ($datetime_fields as $field) {
			$value = $this->$field;
			$json[$field] = ((is_null($value) || '' === $value) ? null : date('Y-m-d\TH:i:sO', strtotime($value)));
		}
		foreach ($date_fields as $field) {
			$value = $this->$field;
			$json[$field] = ((is_null($value) || '' === $value) ? null : date('Y-m-d', strtotime($value)));
		}
		
		return json_encode($json);
	}
	
}
