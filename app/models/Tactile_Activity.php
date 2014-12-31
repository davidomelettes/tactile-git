<?php

class Tactile_Activity extends DataObject implements Taggable, TimelineItem {
	
	protected $defaultDisplayFields = array('name','organisation','class','location','date','time','end_date','end_time','later','assigned_to','assigned_by');
	
	public function __construct($table='tactile_activities') {
		parent::__construct($table);
		$this->orderby='date';
		$this->orderdir='asc';
		$this->hasMany('Note', 'notes', 'n.activity_id');
		$this->hasMany('S3File','s3_files', 'f.activity_id');
		$this->hasMany('Flag');
		
		$this->belongsTo('Activitytype', 'type_id', 'type');
		$this->belongsTo('User', 'owner', 'activity_owner');
		$this->belongsTo('User','assigned_to','activity_assigned_to');
		$this->belongsTo('User', 'alteredby', 'activity_alteredby');
		$this->belongsTo('User', 'assigned_by', 'activity_assigned_by');
		$this->belongsTo('Opportunity', 'opportunity_id', 'opportunity');
		$this->belongsTo('Campaign', 'campaign_id', 'campaign');
		$this->belongsTo('Organisation', 'organisation_id', 'organisation');
		$this->belongsTo('Person', 'person_id', 'person');
		
		$this->assignAutoHandler('assigned_by',new CurrentUserHandler());
		$this->getField('time')->addValidator(new TimeValidator('If you want to specify a time, it must be valid'));
		$this->getField('end_time')->addValidator(new TimeValidator('If you want to specify an end time, it must be valid'));
		
		$this->addValidator(new ActivityDurationValidator());
		
		$this->setAdditional('datetime','timestamp');
		$this->setAdditional('end_datetime','timestamp');
		$this->setAdditional('overview','bool');
		$this->getField('datetime')->setFormatter(new PrettyTimestampFormatter());
		$this->getField('date')->setFormatter(new PrettyTimestampFormatter());
		$this->getField('end_datetime')->setFormatter(new PrettyTimestampFormatter());
		$this->getField('end_date')->setFormatter(new PrettyTimestampFormatter());
		$this->getField('description')->setFormatter(new URLParsingFormatter());
		
		$this->getField('assigned_to')->setFormatter(new UsernameFormatter());
		$this->getField('assigned_by')->setFormatter(new UsernameFormatter());
		
		// This is only here so that is_safe returns true
		$this->getField('type')->setFormatter(new LinkingFormatter('/activities/by_type/?q=%s'));
	}
	
	/**
	 * Returns the Select query that's used for loading activities 'by_tag'
	 *
	 * @param String $tag_string
	 * @param Int $count
	 * @return String
	 */
	public function getQueryForTagSearch($tag_string, $count) {
		$db = DB::Instance();
		
		$qb = new QueryBuilder($db, $this);
		$qb->orderby('ti.name','ASC')
			->from('tactile_activities ti')
			->left_join('tag_map tm', 'ti.id = tm.activity_id')
			->left_join('organisations org', 'org.id = ti.organisation_id')
			->left_join('people p', 'p.id = ti.person_id')
			->left_join('opportunities opp', 'opp.id = ti.opportunity_id');
		
		$query = new QueryBuilder($db, $this);
		
		$fields = array(
			'ti.id',
			'ti.name',
			'ti.location',
			'ti.organisation_id',
			'ti.person_id',
			'ti.opportunity_id',
			'organisation' => 'org.name',
			'person' => 'p.firstname || \' \' || p.surname',
			'opportunity' => 'opp.name',
			'ti.date',
			'ti.time',
			'ti.end_date',
			'ti.end_time',
			'ti.later',
			'ti.assigned_to',
			'ti.assigned_by'
		);
		$non_admin_fields = array(
			'ti.owner'
		);
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('ti.usercompanyid','=',EGS::getCompanyId()));
		
		$qb_tags = new QueryBuilder($db);
		$qb_tags_cc = new ConstraintChain();
		$qb_tags_cc->add(new Constraint('name', 'IN', '('.$tag_string.')'))
			->add(new Constraint('usercompanyid', '=', EGS::getCompanyId()));
		$qb_tags->select_simple(array('id'))
			->from('tags')
			->where($qb_tags_cc);
		$cc->add(new Constraint('tm.tag_id', 'IN', '('.$qb_tags->__toString().')'));
		
		// Module Admins can see everything
		if (!isModuleAdmin()) {
			$fields = array_merge($fields, $non_admin_fields);
			
			$qb->left_join('organisation_roles oroles', 'oroles.organisation_id = ti.organisation_id AND oroles.read');
			$qb_roles = new QueryBuilder($db);
			$qb_roles->select_simple(array('roleid'))
				->from('hasrole')
				->where(new Constraint('username', '=', EGS::getUsername()));
			$c_roles = new Constraint('oroles.roleid', 'IN', '('.$qb_roles->__toString().')');
			
			$cc_access = new ConstraintChain();
			$cc_access->add($c_roles);
			$cc_access->add(new Constraint('ti.owner','=',EGS::getUsername()), 'OR');
			$cc_access->add(new Constraint('ti.assigned_to','=',EGS::getUsername()), 'OR');
			$cc_public = new ConstraintChain();
			$cc_public->add(new Constraint('ti.organisation_id','IS','NULL'));
			$cc_access->add($cc_public, 'OR');
			$cc->add($cc_access);
		}
		
		$qb->select_simple($fields)
			->where($cc)
			->group_by(isModuleAdmin() ? $fields : array_merge($fields, array('oroles.roleid')))
			->having(new Constraint('count(ti.id)', '=', $count));
		
		return $qb;
	}
	
	/**
	 * Returns the DELETE query used to delete activities by tag
	 *
	 * @param string $tag_string
	 * @param int $count
	 * @return QueryBuilder
	 */
	public function getQueryForTagDeletion($tag_string,$count) {
		$select_query = $this->getQueryForTagSearch($tag_string,$count);
		$select_query->select_simple(array('ti.id'));
		
		$db = DB::Instance();
		$delete_query = new QueryBuilder($db, $this);
		$delete_query->delete();
		$delete_query->from('tactile_activities');
		
		$delete_query->sub_select('id', 'IN', $select_query);
		
		return $delete_query;
	}
	
	/**
	 * Returns the query that allows the listing of tags attached to the currently used filter
	 *
	 * @param String $tag_string
	 * @param Int $count
	 * @return string
	 */
	public function getQueryForRestrictedTagList($tag_string,$count) {
		$db = DB::Instance();
		
		if (!isModuleAdmin()) {
			return 'SELECT t.name FROM tag_map tm JOIN tags t ON(t.id = tm.tag_id)
				JOIN tactile_activities a ON (a.id = tm.activity_id)
				LEFT JOIN organisation_roles cr ON a.organisation_id = cr.organisation_id AND cr.read
				LEFT JOIN hasrole hr ON cr.roleid = hr.roleid
				WHERE t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . '
				AND (hr.username = ' . $db->qstr(EGS::getUsername()) . '
					OR a.owner = ' . $db->qstr(EGS::getUsername()) . '
					OR a.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
					OR a.organisation_id IS NULL)
				AND tm.activity_id IN 
				(SELECT tm.activity_id FROM tag_map tm JOIN tags t 
				ON tm.tag_id = t.id WHERE (t.name IN ('.$tag_string.')) 
				GROUP BY tm.activity_id HAVING COUNT(tm.activity_id) = '.$count.') 
				GROUP BY tm.tag_id, t.name ORDER BY lower(t.name)';
		} else {
			return 'SELECT t.name FROM tag_map tm JOIN tags t ON(t.id = tm.tag_id)
				JOIN tactile_activities a ON (a.id = tm.activity_id)
				WHERE t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . '
				AND tm.activity_id IN 
				(SELECT tm.activity_id FROM tag_map tm JOIN tags t 
				ON tm.tag_id = t.id WHERE (t.name IN ('.$tag_string.')) 
				GROUP BY tm.activity_id HAVING COUNT(tm.activity_id) = '.$count.') 
				GROUP BY tm.tag_id, t.name ORDER BY lower(t.name)';
		}
	}
	
	/**
	 * Returns the query to return the full list down the side
	 *
	 * @return String
	 */
	public function getQueryForFullTagList() {
		$db = DB::Instance();
		
		if (!isModuleAdmin()) {
			return 'SELECT DISTINCT t.name, lower(t.name) FROM tags t 
				JOIN tag_map tm ON (
					t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . ' AND 
					t.id = tm.tag_id AND 
					tm.activity_id IS NOT NULL
				) 
				LEFT JOIN tactile_activities a ON (
					a.id = tm.activity_id AND
					t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . '
				)
				LEFT JOIN organisation_roles oroles ON (
					a.organisation_id = oroles.organisation_id AND 
					oroles.read 
				)
				LEFT JOIN hasrole hr ON (
					oroles.roleid = hr.roleid AND 
					hr.username = ' . $db->qstr(EGS::getUsername()) . '
				)
				WHERE (
					a.owner = '.$db->qstr(EGS::getUsername()).' OR
					a.assigned_to = '.$db->qstr(EGS::getUsername()).' OR
					a.organisation_id IS NULL OR
					hr.username = '.$db->qstr(EGS::getUsername()).'
				)
				ORDER BY lower(t.name)';
		} else {
			return 'SELECT DISTINCT t.name, lower(t.name) FROM tags t
				JOIN tag_map tm ON (
					t.id = tm.tag_id AND
					t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . ' AND
					tm.activity_id IS NOT NULL
				)
				JOIN tactile_activities a ON (
					a.id = tm.activity_id AND
					t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . '
				)
				ORDER BY lower(t.name)';
		}
	}
	
	
	public function is_overdue() {
		return ($this->overdue == 'true' || $this->overdue == 't');
	}
	public function due_today() {
		return (date('Y.m.d', strtotime($this->date))==date('Y.m.d', strtotime('today')));
	}
	
	public function starts_today() {
		return (date('Y.m.d', strtotime($this->_startdate))==date('Y.m.d', strtotime('today')));
	}
	
	public function is_later() {
		return $this->later =='t';
	}
	
	public function is_upcoming() {
		return !$this->is_overdue() && !$this->due_today() && !$this->is_later() && strtotime($this->date) < strtotime('+4 days');
	}
	
	function canDelete() {
		return $this->owner == EGS::getUsername();
	}
	function canEdit() {
		// If not attached to an Organisation, check ownership
		$org_id = $this->organisation_id;
		if (empty($org_id)) {
			return ($this->owner == EGS::getUsername() || $this->assigned_to == EGS::getUsername());
		}
		
		// Override Organisation persmissions if assigned to activity
		if ($this->assigned_to == EGS::getUsername()) {
			return true;
		}
		
		// Else check the Organisation
		$org = DataObject::Construct('Organisation');
		$org->load($org_id);
		return $org->canEdit();
			
	}
	
	function canView() {
		$org_id = $this->organisation_id;
		if (empty($org_id)) {
			return true;
		}
		
		// Override Organisation persmissions if assigned to activity
		if ($this->assigned_to == EGS::getUsername()) {
			return true;
		}
		
		// Else check the organisation
		$org = DataObject::Construct('Organisation');
		$org->load($this->organisation_id);
		return $org->canView();
	}

	/**
	 * Returns the date-string for the activity, which depends on the presence of 'later' and a 'time' part
	 *
	 * @return String
	 */
	public function date_string() {
		if($this->is_later()) {
			return 'Later';
		}
		$time = $this->time;
		if(empty($time)) {
			return $this->getFormatted('date');
		}
		$this->datetime = $this->date.' '.$this->time;
		return $this->getFormatted('datetime');
	}
	
	public function end_date_string() {
		$time = $this->end_time;
		if(empty($time)) {
			return $this->getFormatted('end_date');
		}
		$this->end_datetime = $this->end_date.' '.$this->end_time;
		return $this->getFormatted('end_datetime');
	}
	
	public function duration() {
		$datetime_start = $this->date.' '.$this->time;
		$datetime_end = $this->end_date.' '.$this->end_time;
		if (FALSE !== strtotime($datetime_start) && FALSE !== strtotime($datetime_end)) {
			return strtotime($datetime_end) - strtotime($datetime_start);
		} else {
			return FALSE;
		}
	}
	
	public function duration_string() {
		$datetime_start = $this->date.' '.$this->time;
		$datetime_end = $this->end_date.' '.$this->end_time;
		if (FALSE !== ($start_ts = strtotime($datetime_start)) && FALSE !== ($end_ts = strtotime($datetime_end))) {
			$interval = new TimeIntervalInWords($start_ts, $end_ts);
			return $interval->getInterval();
		} else {
			return '?';
		}
	}
	
	/**
	 * Returns a string showing the 'assigned' information- 'to' and 'by' (providing they're different)
	 *
	 * @return String
	 */
	public function assigned_string($html=false) {
		$to_html = '<span class="assigned_to' . ($this->assigned_to == EGS::getUsername() ? ' me' : '') .
			'"><a href="/activities/to_user/?q=' . urlencode($this->getFormatted('assigned_to')) . '">' . $this->getFormatted('assigned_to') . '</a></span>';
		$by_html = ' by <span class="assigned_by' . ($this->assigned_by == EGS::getUsername() ? ' me' : '') .
			'"><a href="/activities/by_user/?q=' . urlencode($this->getFormatted('assigned_by')) . '">' . $this->getFormatted('assigned_by') . '</a></span>';

		if ($this->assigned_to == $this->assigned_by) {
			return ($html ? $to_html : $this->getFormatted('assigned_to'));
		} else {
			return ($html ? $to_html . $by_html : $this->getFormatted('assigned_to') . ' by ' . $this->getFormatted('assigned_by'));
		}
	}
	
	/**
	 * Returns the above function when on a view page as you can't pass arguments
	 * @return String
	 */
	public function assigned_string_view() {
		return $this->assigned_string(true);
	}

	public function notifyAssignedTo() {
		require_once 'Zend/Mail.php';
		$user = new Omelette_User();
		$user->load($this->assigned_to);
		if(false===($email = $user->getEmail())) {
			return false;
		}

		$mail = new Omelette_Mail('activity_notify_assigned');
		$mail->getView()->set('Activity',$this);
		
		$mail->getMail()
			->setSubject('Tactile CRM: You have been assigned an activity')
			->addTo($email)
			->setFrom(TACTILE_EMAIL_FROM,TACTILE_EMAIL_NAME);
		return $mail->send();
	}
	
	public function notifyChange() {
		require_once 'Zend/Mail.php';
		$user = new Omelette_User();
		$user->load($this->assigned_to);
		if(false===($email = $user->getEmail())) {
			return false;
		}
		
		$mail = new Omelette_Mail('activity_notify_change');
		$mail->getView()->set('Activity',$this);
		
		$mail->getMail()
			->setSubject('Tactile CRM: One of your activities has been edited')
			->addTo($email)
			->setFrom(TACTILE_EMAIL_FROM,TACTILE_EMAIL_NAME);
		return $mail->send();
	}
	
	public function notifyCompleted() {
		require_once 'Zend/Mail.php';
		$user = new Omelette_User();
		$user->load($this->assigned_to);
		if(false===($email = $user->getEmail())) {
			return false;
		}
		
		$mail = new Omelette_Mail('activity_notify_completed');
		$mail->getView()->set('Activity',$this);
		
		$mail->getMail()
			->setSubject('Tactile CRM: One of your activities has been completed')
			->addTo($email)
			->setFrom(TACTILE_EMAIL_FROM,TACTILE_EMAIL_NAME);
		return $mail->send();
	}
	
	public function notifyUncompleted() {
		require_once 'Zend/Mail.php';
		$user = new Omelette_User();
		$user->load($this->assigned_to);
		if(false===($email = $user->getEmail())) {
			return false;
		}
		
		$mail = new Omelette_Mail('activity_notify_uncompleted');
		$mail->getView()->set('Activity',$this);
		
		$mail->getMail()
			->setSubject('Tactile CRM: One of your activities has been Uncompleted')
			->addTo($email)
			->setFrom(TACTILE_EMAIL_FROM,TACTILE_EMAIL_NAME);
		return $mail->send();
	}
	
	function get_name() {
		return 'Activity';
	}
	
	function isEvent() {
		$class = $this->class;
		if ($class === 'event') {
			return true;
		} else {
			return false;
		}
	}
	
	function isHappeningNow() {
		if ($this->isEvent()) {
			$start_date = $this->date;
			$start_time = $this->time;
			$start = trim($start_date . ' ' . $start_time);
			$end_date = $this->end_date;
			$end_time = $this->end_time;
			$end = trim($end_date . ' ' . (!empty($end_time) ? $end_time : '23:59:59'));
			if (strtotime($start) < time() && strtotime($end) > time()) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	function toVCalendarItem() {
		if ($this->isEvent()) {
			$item = new VCalEvent($this->name);
		} else {
			$item = new VCalTodo($this->name);
		}
		/* @var $item VCalObject */
		$type = $this->type;
		$desc = $this->description;
		if (!empty($type)) {
			$item->setDescription("(" . $type . ") " . $desc);
		} else {
			$item->setDescription($desc);
		}
		
		if ($this->isEvent()) {
				
			$loc = $this->location;
			if (!empty($loc)) {
				$item->setLocation($loc);
			}
			
			if (!$this->is_later()) {
				$time = $this->time;
				if (empty($time)) {
					if (FALSE !== ($date = strtotime($this->date))) {
						$item->setDateStart(date('Ymd', $date), false);
					}
				} else {
					if (FALSE !== ($datetime = strtotime($this->date . ' ' . $time))) {
						$item->setDateStart(date('Ymd\THis', $datetime));
					}
				}
				
				$end_time = $this->end_time;
				if (empty($end_time)) {
					if (FALSE !== ($end_date = strtotime($this->end_date))) {
						// END DATES ARE NON-INCLUSIVE!
						$end_date = strtotime('+1 day', $end_date);
						$item->setDateEnd(date('Ymd', $end_date), false);
					}
				} else {
					if (FALSE !== ($end_datetime = strtotime($this->end_date . ' ' . $end_time))) {
						$item->setDateEnd(date('Ymd\THis', $end_datetime));
					}
				}
			}
			
		} else {
			// Must be a VTodo
			if (!$this->is_later()) {
				$time = $this->time;
				if (empty($time)) {
					if (FALSE !== ($date = strtotime($this->date))) {
						$item->setDateDue(date('Ymd', $date));
					}
				} else {
					if (FALSE !== ($datetime = strtotime($this->date . ' ' . $time))) {
						$item->setDateDue(date('Ymd\THis', $datetime));
					}
				}
			}
			if($this->is_completed()) {
				$item->setCompleted(date('Ymd\THis', strtotime($this->completed)));
			}
		}
		
		return $item;
	}
	
	/**
	 * @return boolean
	 */
	function is_completed() {
		$completed = $this->completed;
		return !empty($completed);
	}
	
	static function getMostRecentlyModifiedDate($username) {
		$db = DB::Instance();
		$query = "SELECT lastupdated FROM tactile_activities WHERE assigned_to = " . $db->qstr($username) .
			" AND completed IS NULL ORDER BY lastupdated DESC LIMIT 1"; 
		$date = $db->getOne($query);

		return $date;
	}
	
	public function getQueryForRecentlyViewedSearch() {
		$db = DB::Instance();
		
		$query = new QueryBuilder($db, $this);
		$fields = array(
			'ta.id',
			'ta.name',
			'ta.location',
			'organisation'=>'org.name',
			'ta.organisation_id',
			'ta.person_id',
			'person'=>'p.firstname || \' \' || p.surname',
			'ta.date',
			'ta.time',
			'ta.end_date',
			'ta.end_time',
			'ta.later',
			'ta.assigned_to',
			'ta.assigned_by'
		);
		$cc = new ConstraintChain();
		$cc->add(new Constraint('ta.usercompanyid','=',EGS::getCompanyId()));
		$cc->add(new Constraint('rv.owner','=',EGS::getUsername()));
		
		$query->select_simple($fields)
			->from('tactile_activities ta')
			->join('recently_viewed rv','ta.id=rv.link_id AND rv.type='.$db->qstr(ViewedPage::TYPE_ACTIVITY))
			->left_join('organisations org','ta.organisation_id=org.id')
			->left_join('people p','ta.person_id=p.id')
			->where($cc);
			
		return $query;
	}
	
	public function asJson() {
		$json = array();
		
		$string_fields = array('name','location', 'description', 'time', 'end_time', 'type',
			'organisation', 'opportunity', 'class', 'assigned_to', 'assigned_by', 'owner', 'alteredby');
		$int_fields = array('id', 'organisation_id', 'opportunity_id');
		$formatted_fields = array();
		$boolean_fields = array('later');
		$datetime_fields = array('created', 'lastupdated', 'completed');
		$date_fields = array('date', 'end_date');
		
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
		
		$value = $this->person_id;
		if (empty($value)) {
			$json['person_id'] = null;
			$json['person'] = null;
		} else {
			$json['person_id'] = (int) $value;
			$json['person'] = $this->person;
		}
		
		if ($json['class'] == 'todo') {
			unset($json['end_date']);
			unset($json['end_time']);
			unset($json['location']); 
		} else {
			unset($json['later']);
		}
		$ti=new TaggedItem($this);
		foreach ($ti->getTags() as $value) {
			$json['tags'][]=((is_null($value) || '' === $value) ? null : (string) $value);
		}
		$custom_fields = $this->getCustomFields();
		$json['custom_fields'] = json_decode($custom_fields->asJson());
		$custom_values = $this->getCustomValues();
		$json['custom_values'] = json_decode($custom_values->asJson());
		
		$json['date_string'] = $this->date_string();
		$json['end_date_string'] = $this->end_date_string();
		
		return json_encode($json);
	}
	
	public function getTimelineType() {
		if ($this->is_completed()) {
			return 'Completed Activity';
		} elseif ($this->is_overdue()) {
			return 'Overdue Activity';
		} else {
			return 'New Activity';
		}
	}
	
	public function getTimelineDate() {
		$formatter = new TimelineTimestampFormatter();
		if ($this->is_completed()) {
			return $formatter->format($this->completed);
		} elseif ($this->is_overdue()) {
			return $formatter->format($this->due);
		} else {
			return $formatter->format($this->created);
		}
	}
	
	public function getTimelineTime() {
		if ($this->is_completed()) {
			return $this->completed;
		} elseif ($this->is_overdue()) {
			return $this->due;
		} else {
			return $this->created;
		}
	}
	
	public function getTimelineSubject() {
		return $this->getFormatted('name');
	}
	
	public function getTimelineBody() {
		return $this->getFormatted('description');
	}
	
	public function getTimelineURL() {
		return '/activities/view/'.$this->id;
	}
	
	function getReadString() {
		// Inherit company permissions if assigned to one
		if ($this->organisation_id){
			$org = DataObject::Construct('Organisation');
			$org->load($this->organisation_id);
			return $org->getAccessString('read');
		} else {
			// No, they are public
			return 'by everyone';
		}
	}
	
	function getWriteString() {
		// Inherit company permissions if assigned to one
		if($this->organisation_id){
			$org = DataObject::Construct('Organisation');
			$org->load($this->organisation_id);
			return $org->getAccessString('write');
		} else {
			// No, they are public
			return 'by everyone';
		}
	}
	
	public function getCustomFields() {
		$customfieldsCollection = new CustomfieldCollection();
		$sh = new SearchHandler($customfieldsCollection, false);
		$sh->extract(true);
		$sh->addConstraint(new Constraint('activities', '=', 'true'));
		$customfieldsCollection->load($sh);
		return $customfieldsCollection;
	}
	
	public function getCustomValues() {
		$customfieldsMapCollection = new CustomfieldMapCollection();
		$sh = new SearchHandler($customfieldsMapCollection, false);
		$sh->addConstraint(new Constraint('activity_id','=',$this->id));
		$sh->extractOrdering();
		$customfieldsMapCollection->load($sh);
		return $customfieldsMapCollection;
	}
	
	/**
	 * 
	 * Handles the processing of submitted form data, e.g. the conversion of 'Today' to an actual time
	 * @param Array $data
	 * @throws Exception
	 */
	static public function processFormData($data) {
		$user = CurrentlyLoggedInUser::Instance();
		$user_zone = CurrentlyLoggedInUser::Instance()->getTimezoneString();
		date_default_timezone_set($user_zone);
		
		// In case class is not specified
		if (!isset($data['class']) && !isset($data['id'])) {
			$data['class'] = 'todo';
		}
		
		// In case date_choice is not specified...
		if (!isset($data['date_choice']) && !isset($data['id'])) {
			if (isset($data['later']) && $data['later'] != false) {
				// they've asked for later
				$data['date_choice'] = 'later';
			} else {
				// default to date
				$data['date_choice'] = 'date';
			}
		}
		
		if (isset($data['date_choice'])) {
			switch($data['date_choice']) {
				case 'later': {	//later means no date/time
					$data['later']		= true;
					$data['date']		= '';
					$data['time']		= '';
					$data['end_date']	= '';
					$data['end_time']	= '';
					break;
				}
				case 'today': { //today means set date, handle optional time and make sure not later
					$data['later'] = false;
					switch ($data['class']) {
						case 'event':
							$data['date'] = date(EGS::getDateFormat());
							if (!empty($data['time_hours']) || !empty($data['time_minutes'])) {
								$data['time'] = $data['time_hours'].':'.$data['time_minutes'];
								//$this->activity->getField('time')->setnotnull();
							}
							$data['end_date'] = date(EGS::getDateFormat());
							if (!empty($data['end_time_hours']) || !empty($data['end_time_minutes'])) {
								$data['end_time'] = $data['end_time_hours'].':'.$data['end_time_minutes'];
								//$this->activity->getField('end_time')->setnotnull();
							}
							break;
						case 'todo':
						default:
							$data['end_date']	= '';
							$data['end_time']	= '';
							$data['date'] = date(EGS::getDateFormat());
							if (!empty($data['time_hours']) || !empty($data['time_minutes'])) {
								$data['time'] = $data['time_hours'].':'.$data['time_minutes'];
								//$this->activity->getField('time')->setnotnull();
							}
							break;
					}
					break;
				}
				case 'tomorrow': {	//see today
					$data['later'] = false;
					switch ($data['class']) {
						case 'event':
							$data['date'] = date(EGS::getDateFormat(),strtotime('tomorrow'));
							if (!empty($data['time_hours']) || !empty($data['time_minutes'])) {
								$data['time'] = $data['time_hours'].':'.$data['time_minutes'];
								//$this->activity->getField('time')->setnotnull();
							}
							$data['end_date'] = date(EGS::getDateFormat(),strtotime('tomorrow'));
							if (!empty($data['end_time_hours']) || !empty($data['end_time_minutes'])) {
								$data['end_time'] = $data['end_time_hours'].':'.$data['end_time_minutes'];
								//$this->activity->getField('end_time')->setnotnull();
							}
							break;
						case 'todo':
						default:
							$data['end_date']	= '';
							$data['end_time']	= '';
							$data['date'] = date(EGS::getDateFormat(),strtotime('tomorrow'));
							if (!empty($data['time_hours']) || !empty($data['time_minutes'])) {
								$data['time'] = $data['time_hours'].':'.$data['time_minutes'];
								//$this->activity->getField('time')->setnotnull();
							}
							break;
					}
					break;
				}
				case 'date': {	//date becomes non-optional, handle hours and make sure not later
					$data['later'] = false;
					switch ($data['class']) {
						case 'event':
							//$this->activity->getField('date')->setnotnull();
							if (!empty($data['time_hours']) || !empty($data['time_minutes'])) {
								$data['time'] = $data['time_hours'].':'.$data['time_minutes'];
								//$this->activity->getField('time')->setnotnull();
							}
							//$this->activity->getField('end_date')->setnotnull();
							if (!empty($data['end_time_hours']) || !empty($data['end_time_minutes'])) {
								$data['end_time'] = $data['end_time_hours'].':'.$data['end_time_minutes'];
								//$this->activity->getField('end_time')->setnotnull();
							}
							break;
						case 'todo':
						default:
							$data['end_date'] = '';
							$data['end_time'] = '';
							//$this->activity->getField('date')->setnotnull();
							if (!empty($data['time_hours']) || !empty($data['time_minutes'])) {
								$data['time'] = $data['time_hours'].':'.$data['time_minutes'];
								//$this->activity->getField('time')->setnotnull();
							}
							break;
					}
					break;
				}
				default: {
					throw new Exception("Invalid type of date chosen");
				}
			}

			//any day adjustments for tz need to be 'local'
			date_default_timezone_set('Europe/London');
			if ($data['date_choice'] != 'later' && !empty($data['time'])) {
				$date_part = fix_date($data['date']);
				if ($date_part !== false) {
					$date_string = $date_part.' '.$data['time'].' '.$user_zone;
					$data['date'] = date(EGS::getDateFormat(), strtotime($date_string));
				}
				if (!empty($data['end_time'])) {
					$end_date_part = fix_date($data['end_date']);
					if ($end_date_part !== false) {
						$end_date_string = $end_date_part.' '.$data['end_time'].' '.$user_zone;
						$data['end_date'] = date(EGS::getDateFormat(), strtotime($end_date_string));
					}
				}
			}
		}
		
		return $data;
	}
	
}	
