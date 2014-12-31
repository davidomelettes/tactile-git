<?php
class Tactile_Person extends Person implements Taggable {
	
	protected $defaultDisplayFields = array('firstname','surname','organisation','phone','mobile','email');
	
	public static $autoTitles = array('Mr', 'Mr.', 'Mrs', 'Mrs.', 'Ms', 'Ms.', 'Miss', 'Master', 'Dr', 'Dr.', 'Sir', 'Dame', 'Lady', 'Lord', 'Madam');
	
	public function __construct() {
		parent::__construct();
		$cc = new ConstraintChain();
		$cc->add(new Constraint('main','=','true'));
		$this->setAlias('address','Tactile_Personaddress',$cc);
		
		$this->hasMany('Note', 'notes', 'n.person_id');
		$this->hasMany('Tactile_Activity','activities');
		$this->hasMany('S3File','s3_files', 'f.person_id');
		$this->hasMany('Email');
		$this->hasMany('Flag');
		
		$this->belongsTo('Country', 'country_code', 'country');
		$this->belongsTo('Language', 'language_code', 'language');
		$this->belongsTo('User', 'last_contacted_by', 'last_contacted_by_user');
		
		$this->getField('description')->setFormatter(new URLParsingFormatter());
		$this->getField('jobtitle')->setFormatter(new LinkingFormatter('/people/by_jobtitle/?q=%s'));
		$this->addValidator(new ContactLimitValidator());
	}
	
	public static function factoryFromString($string = null, $data = array(), &$errors = array()) {
		$string = trim($string);
		if (empty($string)) {
			return false;
		}
		$parts = preg_split('/\s+/', $string);
		switch (count($parts)) {
			case '0':
				return false;
				break;
			case '1':
				$data['firstname'] = '-';
				$data['surname'] = $parts[0];
				break;
			case '2':
				if (in_array($parts[0], self::$autoTitles)) {
					$data['title'] = $parts[0];
					$data['firstname'] = '-';
				} else {
					$data['firstname'] = $parts[0];
				}
				$data['surname'] = $parts[1];
				break;
			default:
				if (in_array($parts[0], self::$autoTitles)) {
					$data['title'] = $parts[0];
					$data['firstname'] = $parts[1];
				} else {
					$data['firstname'] = $parts[0];
				}
				$data['surname'] = $parts[count($parts)-1];
				break;
		}
		$saver = new ModelSaver();
		$person = $saver->save($data, 'Tactile_Person', $errors);
		return $person;
	}
	
	public function getQueryForRecentlyViewedSearch() {
		$db = DB::Instance();
		
		$query = new QueryBuilder($db, $this);
		//x = viewed item
		$fields = array(
			'x.id',
			'x.title',
			'x.firstname',
			'x.surname',
			'x.suffix',
			'x.organisation_id',
			'organisation'=>'org.name',
			'phone'=>'p.contact',
			'mobile'=>'m.contact',
			'email'=>'e.contact'
		);
		$cc = new ConstraintChain();
		$cc->add(new Constraint('x.usercompanyid','=',EGS::getCompanyId()));
		$cc->add(new Constraint('rv.owner','=',EGS::getUsername()));
		
		$query->select_simple($fields)
				->from('people x')
				->join('recently_viewed rv','x.id=rv.link_id AND rv.type='.$db->qstr(ViewedPage::TYPE_PERSON))
				->left_join('organisations org','org.id=x.organisation_id')
				->left_join('person_contact_methods p','x.id=p.person_id AND p.type=\'T\' AND p.main')
				->left_join('person_contact_methods m','x.id=m.person_id AND m.type=\'M\' AND m.main')
				->left_join('person_contact_methods e','x.id=e.person_id AND e.type=\'E\' AND e.main')
				->left_join('person_addresses a', 'x.id=a.person_id AND a.main')		
				->where($cc);
		
		return $query;
	}
	
	/**
	 * Returns the Select query that's used for loading people 'by_tag'
	 *
	 * @param String $tag_string
	 * @param Int $count
	 * @return QueryBuilder
	 */
	public function getQueryForTagSearch($tag_string, $count) {
		$db = DB::Instance();
		
		$qb = new QueryBuilder($db, $this);
		$qb->orderby('ti.surname','ASC')
			->from('people ti')
			->left_join('tag_map tm', 'ti.id = tm.person_id')
			->left_join('organisations org', 'org.id = ti.organisation_id')
			->left_join('person_contact_methods p', 'ti.id = p.person_id AND p.type = \'T\' AND p.main')
			->left_join('person_contact_methods m', 'ti.id = m.person_id AND m.type = \'M\' AND m.main')
			->left_join('person_contact_methods e', 'ti.id = e.person_id AND e.type = \'E\' AND e.main')
			->left_join('person_addresses a', 'ti.id=a.person_id AND a.main');
		
		$fields = array(
			'ti.id',
			'ti.firstname',
			'ti.surname',
			'ti.organisation_id',
			'ti.private',
			'organisation' => 'org.name',
			'phone' => 'p.contact',
			'mobile' => 'm.contact',
			'email' => 'e.contact'
		);
		$non_admin_fields = array(
			'ti.assigned_to',
			'ti.owner',
			'ti.private'
		);
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('ti.usercompanyid', '=', EGS::getCompanyId()));

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
			$cc_public->add(new Constraint('ti.private','=','false'));
			$cc_access->add($cc_public, 'OR');
			$cc->add($cc_access);
		}
		
		$qb->select_simple($fields)
			->where($cc)
			->group_by(isModuleAdmin() ? $fields : array_merge($fields, array('oroles.roleid')))
			->having(new Constraint('count(ti.id)', '=', $count));
			
		#print_r($qb->__toString());
			
		return $qb;
	}
	
	/**
	 * Returns the DELETE query used to delete people by tag
	 *
	 * @param string $tag_string
	 * @param int $count
	 * @return QueryBuilder
	 */
	public function getQueryForTagDeletion($tag_string,$count) {
		$select_query = $this->getQueryForTagSearch($tag_string,$count);
		$select_query->select_simple(array('ti.id'));
		$select_query->left_join('users u', 'u.person_id = ti.id');
		$select_query->where(new Constraint('u.username', 'IS', 'NULL'));
		
		$db = DB::Instance();
		$delete_query = new QueryBuilder($db, $this);
		$delete_query->delete();
		$delete_query->from('people');
		
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
				JOIN people per ON (per.id = tm.person_id)
				LEFT JOIN organisation_roles cr ON per.organisation_id = cr.organisation_id AND cr.read
				LEFT JOIN hasrole hr ON cr.roleid = hr.roleid
				WHERE t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . '
				AND (hr.username = ' . $db->qstr(EGS::getUsername()) . '
					OR per.owner = ' . $db->qstr(EGS::getUsername()) . '
					OR per.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
					OR (per.organisation_id IS NULL AND per.private = FALSE))
				AND tm.person_id IN 
				(SELECT tm.person_id FROM tag_map tm JOIN tags t
				ON tm.tag_id = t.id WHERE (t.name IN (' . $tag_string . ')) 
				GROUP BY tm.person_id HAVING COUNT(tm.person_id) = ' . $db->qstr($count) . ')
				GROUP BY tm.tag_id, t.name ORDER BY lower(t.name)';
		} else {
			return 'SELECT t.name FROM tag_map tm JOIN tags t ON(t.id = tm.tag_id)
				JOIN people per ON (per.id = tm.person_id)
				WHERE t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . ' AND tm.person_id IN 
				(SELECT tm.person_id FROM tag_map tm JOIN tags t
				ON tm.tag_id = t.id WHERE (t.name IN (' . $tag_string . ')) 
				GROUP BY tm.person_id HAVING COUNT(tm.person_id) = ' . $db->qstr($count) . ')
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
					tm.person_id IS NOT NULL
				) 
				LEFT JOIN people per ON (
					per.id = tm.person_id AND
					t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . '
				)
				LEFT JOIN organisation_roles oroles ON (
					per.organisation_id = oroles.organisation_id AND 
					oroles.read 
				)
				LEFT JOIN hasrole hr ON (
					oroles.roleid = hr.roleid AND 
					hr.username = ' . $db->qstr(EGS::getUsername()) . '
				)
				WHERE (
					per.owner = '.$db->qstr(EGS::getUsername()).' OR
					per.assigned_to = '.$db->qstr(EGS::getUsername()).' OR
					(per.organisation_id IS NULL AND per.private = FALSE) OR
					hr.username = '.$db->qstr(EGS::getUsername()).'
				)
				ORDER BY lower(t.name)';
		} else {
			return 'SELECT DISTINCT t.name, lower(t.name) FROM tags t
				JOIN tag_map tm ON (
					t.id = tm.tag_id AND
					t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . ' AND
					tm.person_id IS NOT NULL
				)
				JOIN people per ON (
					per.id = tm.person_id AND
					t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . '
				)
				ORDER BY lower(t.name)';
		}
	}
	
	
	public function canDelete() {
		return $this->owner == EGS::getUsername() || isModuleAdmin();
	}
	
	/**
	 * Returns a human-readable string informing of the roles able to read the person
	 * @return String
	 * @see getAccessString()
	 */
	function getReadString() {
		// Inherit company permissions if assigned to one
		if($this->organisation_id){
			$c = DataObject::Construct('Organisation');
			$c->load($this->organisation_id);
			return $c->getAccessString('read');
		} else {
			// Is this person marked as private?
			if ($this->isPrivate()) {
				// Are you the owner and the assignee?
				if($this->assigned_to && $this->owner == $this->assigned_to &&$this->owner == EGS_USERNAME){
					return 'only by you';
				// Are you the owner, and is this person assigned to someone else?
				} elseif($this->assigned_to && $this->owner != $this->assigned_to &&$this->owner == EGS_USERNAME){
					return 'by '.$this->getFormatted('assigned_to').' and you';
				// Are you the assignee, and is this person owned by someone else?
				} elseif($this->assigned_to && $this->owner != $this->assigned_to &&$this->assigned_to == EGS_USERNAME){
					return 'by '.$this->getFormatted('owner').' and you';
				// Can only be you
				} else {
					return 'only by you';
				}
			} else {
				// No, they are public
				return 'by everyone';
			}
		}
	}
	
	/**
	 * Returns a human-readable string informing of the roles able to write/edit the person
	 * @return String
	 * @see getAccessString()
	 */
	function getWriteString() {
		// Inherit company permissions if assigned to one
		if($this->organisation_id){
			$c = DataObject::Construct('Organisation');
			$c->load($this->organisation_id);
			return $c->getAccessString('write');
		} else {
			// Is this person marked as private?
			if ($this->isPrivate()) {
				// Are you the owner and the assignee?
				if ($this->assigned_to && $this->owner == $this->assigned_to && $this->owner == EGS_USERNAME){
					return 'only by you';
				// Are you the owner, and is this person assigned to someone else?
				} elseif($this->assigned_to && $this->owner != $this->assigned_to && $this->owner == EGS_USERNAME){
					return 'by '.$this->getFormatted('assigned_to') . ' and you';
				// Are you the assignee, and is this person owned by someone else?
				} elseif($this->assigned_to && $this->owner != $this->assigned_to && $this->assigned_to == EGS_USERNAME){
					return 'by '.$this->getFormatted('owner') . ' and you';
				// Can only be you
				} else {
					return 'only by you';
				}
			} else {
				// No, they are public
				return 'by everyone';
			}
		}
	}
	
	public function canEdit() {
		// Admin, Owner and assignee can edit
		if(isModuleAdmin() || $this->owner == EGS::getUsername() || $this->assigned_to == EGS::getUsername()) {
			return true;
		}
		
		// If not in a company, check privacy flag
		$c_id = $this->organisation_id;
		if(empty($c_id)) {
			return !$this->isPrivate();
		}
		
		// Else check the company
		$c = DataObject::Construct('Organisation');
		$c->load($this->organisation_id);
		return $c->canEdit();
	}
	
	function canView() {
		// Owner and assignee can edit
		if(isModuleAdmin() || $this->owner == EGS::getUsername() || $this->assigned_to == EGS::getUsername()) {
			return true;
		}
		
		// If not in a company, check privacy flag
		$c_id = $this->organisation_id;
		if(empty($c_id)) {
			return !$this->isPrivate();
		}
		
		// Else check the company
		$org = DataObject::Construct('Organisation');
		$org->load($this->organisation_id);
		return $org->canView();
	}
	
	function get_name() {
		return 'Person';
	}
	
	function isPrivate() {
		switch ($this->private) {
			case false:
			case 'f':
			case 'false':
				return false;
			default:
				return true;
		}
	}
	
	function __get($key) {
		if($key == 'name') {
			return $this->firstname . ' ' . $this->surname;
		}
		return parent::__get($key);
	}
	
	public function findByPhoneNumber($number) {
		$db = DB::Instance();
		$query = "SELECT per.*, c.name AS company FROM people per left join organisations org on per.organisation_id=org.id
			join person_contact_methods pcm on 
				(per.id = pcm.person_id and type IN ('T', 'M') AND replace(contact,' ','') =  " . $db->qstr($number) .")
				WHERE per.usercompanyid = " . $db->qstr(EGS::getCompanyId());
		$rows = $db->getArray($query);
		return $rows;
	}
	
	public function asJson() {
		$json = array();
		
		$string_fields = array('name', 'description', 'title', 'firstname', 'surname', 'suffix',
			'jobtitle', 'language', 'language_code', 'organisation',
			'phone', 'email',
			'owner', 'assigned_to');
		$address_fields = array('street1', 'street2', 'street3', 'town', 'county', 'postcode', 'country', 'country_code');
		$int_fields = array('id', 'organisation_id', 'reports_to');
		$boolean_fields = array('can_call', 'can_email');
		$formatted_fields = array();
		$datetime_fields = array('created', 'lastupdated');
		$date_fields = array('dob');
		
		foreach ($string_fields as $field) {
			$value = $this->$field;
			$json[$field] = ((is_null($value) || '' === $value) ? null : (string) $value);
		}
		foreach ($address_fields as $field) {
			$value = $this->address->$field;
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
		$ti=new TaggedItem($this);
		foreach ($ti->getTags() as $value) {
			$json['tags'][]=((is_null($value) || '' === $value) ? null : (string) $value);
		}
		$custom_fields = $this->getCustomFields();
		$json['custom_fields'] = json_decode($custom_fields->asJson());
		$custom_values = $this->getCustomValues();
		$json['custom_values'] = json_decode($custom_values->asJson());
		
		return json_encode($json);
	}
	
	public function hasAddress() {
		$fields = array('street1', 'street2', 'street3', 'town', 'postcode', 'county','country');
		foreach ($fields as $field) {
			$value = $this->address->$field;
			if (!empty($value)) {
				return true;
			}
		}
		return false;
	}
	
	public function getAddress($html=true) {
		$fields = array('street1', 'street2', 'street3', 'town', 'county', 'postcode', 'country_code');
		$output = array();
		
		foreach ($fields as $field) {
			switch ($field) {
				case 'country_code':
					$value = $this->address->country;
					break;
				default:
					$value = $this->address->$field;
			}
			if (!empty($value)) {
				$output[] = ($html ? "<span class=\"$field\">$value</span>" : $value);
			}
		}
		
		return ($html ? implode("<br />\n", $output) : $output);
	}
	
	public function getLogoUrl() {
		$file = new S3File();
		if (FALSE !== $file->load($this->logo_id)) {
			$protocol = (empty($_SERVER['HTTP_X_FARM']) || $_SERVER['HTTP_X_FARM'] != 'HTTPS') ? 'http' : 'https';
			return $protocol . '://s3.amazonaws.com/tactile_public/' .
				EGS::getCompanyId() . '/' . $file->id . '/' . $file->filename; 
		}
		return false;
	}

	public function getThumbnailUrl() {
		$file = new S3File();
		if (FALSE !== $file->load($this->thumbnail_id)) {
			$protocol = (empty($_SERVER['HTTP_X_FARM']) || $_SERVER['HTTP_X_FARM'] != 'HTTPS') ? 'http' : 'https';
			return $protocol . '://s3.amazonaws.com/tactile_public/' .
				EGS::getCompanyId() . '/' . $file->id . '/' . $file->filename; 
		}
		return false;
	}
	
	public function getTimelineDate() {
		$formatter = new PrettyTimestampFormatter();
		return $formatter->format(date('Y-m-d',strtotime($this->created)));
	}
	
	public function isUser() {
		$user = new Tactile_User();
		return (false !== $user->loadBy('person_id', $this->id));
	}
	
	public function getUser() {
		$user = new Tactile_User();
		return $user->loadBy('person_id', $this->id);
	}

	public function getPipelineDetails() {
		$id = $this->id;

		$db = DB::Instance();

		$query = 'SELECT 
			sum(o.cost) AS total,
			sum(o.cost*(o.probability/100.0)) AS weighted
			FROM
				opportunities o,
				opportunitystatus s
			WHERE
				o.status_id=s.id AND 
				s.open=true AND
				o.person_id ='.$db->qstr($id);

		$pipeline = $db->GetRow($query);

		return $pipeline;
	}	

	public function getTimeToClose() {
		$id = $this->id;

		$db = DB::Instance();

		$query = 'SELECT 
			CASE WHEN avg(o.enddate - date_trunc(\'day\', o.created)) < interval \'1 day\'
			THEN \'1 day\'
			ELSE avg(o.enddate - date_trunc(\'day\', o.created)) END AS wintime
			FROM
				opportunities o,
				opportunitystatus s
			WHERE
				o.status_id=s.id AND 
				s.won=true AND
				o.person_id='.$db->qstr($id);

		$closetime = $db->GetOne($query);

		return $closetime;
	}
	
	public function getWinRate() {
		$id = $this->id;

		$db = DB::Instance();

		$query = 'SELECT 
			count(*)
			FROM
				opportunities o,
				opportunitystatus s
			WHERE
				o.status_id=s.id AND 
				s.won=true AND
				o.person_id='.$db->qstr($id);

		$won = $db->GetOne($query);

		$query = 'SELECT 
			count(*)
			FROM
				opportunities o
			WHERE
				o.person_id='.$db->qstr($id);

		$total = $db->GetOne($query);

		if($won == 0 && $total == 0) {
			return 0;
		} else {
			return intval(($won/$total)*100);
		}
	}

	public function getCustomFields() {
		$customfieldsCollection = new CustomfieldCollection();
		$sh = new SearchHandler($customfieldsCollection, false);
		$sh->extract(true);
		$sh->addConstraint(new Constraint('people', '=', 'true'));
		$customfieldsCollection->load($sh);
		return $customfieldsCollection;
	}
	
	public function getCustomValues() {
		$customfieldsMapCollection = new CustomfieldMapCollection();
		$sh = new SearchHandler($customfieldsMapCollection, false);
		$sh->addConstraint(new Constraint('person_id','=',$this->id));
		$sh->extractOrdering();
		$customfieldsMapCollection->load($sh);
		return $customfieldsMapCollection;
	}
}
