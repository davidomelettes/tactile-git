<?php
/**
 * Extends Company, mostly for access-control reasons. Some of these wouldn't do any harm being in Company,
 * but probably aren't that useful with EGS's access control.
 * @author gj
 */
class Tactile_Organisation extends Organisation implements Taggable {

	private $roles = array();

	protected $defaultDisplayFields = array('name', 'phone', 'fax', 'email', 'website');

	/**
	 * Over-ridden so that we haveMany notes, and accountnumbers are always handled
	 * @see parent::__construct()
	 * @constructor
	 */
	public function __construct() {
		parent::__construct();
		$cc = new ConstraintChain();
		$cc->add(new Constraint('main','=','true'));
		$this->setAlias('address','Tactile_Organisationaddress',$cc);
		
		$this->hasMany('Note', 'notes', 'n.organisation_id');
		$this->hasMany('Tactile_Activity', 'activities');
		$this->hasMany('S3File','s3_files', 'f.organisation_id');
		$this->hasMany('Email', 'emails', 'e.organisation_id');
		$this->hasMany('Flag');
		$this->hasMany('Tactile_Organisationaddress', 'addresses', 'organisation_id');
		
		$this->belongsTo('CompanyClassification','classification_id','company_classification');
		$this->belongsTo('CompanyIndustry','industry_id','company_industry');
		$this->belongsTo('CompanyRating','rating_id','company_rating');
		$this->belongsTo('CompanySource','source_id','company_source');
		$this->belongsTo('CompanyStatus','status_id','company_status');
		$this->belongsTo('CompanyType','type_id','company_type');
		$this->belongsTo('CompanyType','type_id','company_type');
		$this->belongsTo('User', 'last_contacted_by', 'last_contacted_by_user');
		
		$this->assignAutoHandler('accountnumber', new AccountNumberHandler(true));
		$this->getField('accountnumber')->dropnotnull();
		$this->getField('website')->setFormatter(new OmeletteURLFormatter());
		$this->getField('description')->setFormatter(new URLParsingFormatter());
		$this->getField('company_status')->setFormatter(new LinkingFormatter('/people/by_jobtitle/?q=%s'));
		
		$this->addValidator(new ContactLimitValidator());
	}
	
	public static function factoryFromString($string = null, $data = array(), &$errors = array()) {
		$string = trim($string);
		if (empty($string)) {
			return false;
		}
		$data['name'] = $string;
		$saver = new ModelSaver();
		$org = $saver->save($data, 'Tactile_Organisation', $errors);
		return $org;
	} 

	/**
	 * Returns the Select query that's used for loading companies 'by_tag'
	 *
	 * @param String $tag_string
	 * @param Int $count
	 * @return QueryBuilder
	 */
	public function getQueryForTagSearch($tag_string, $count) {
		$db = DB::Instance();
		
		$qb = new QueryBuilder($db, $this);
		$qb->orderby('ti.name','ASC')
			->from('organisations ti')
			->left_join('tag_map tm', 'ti.id = tm.organisation_id')
			->left_join('organisation_addresses a', 'ti.id = a.organisation_id AND a.main')
			->left_join('organisation_contact_methods p', 'ti.id = p.organisation_id AND p.type = \'T\' AND p.main')
			->left_join('organisation_contact_methods e', 'ti.id = e.organisation_id AND e.type = \'E\' AND e.main')
			->left_join('organisation_contact_methods f', 'ti.id = f.organisation_id AND f.type = \'F\' AND f.main')
			->left_join('organisation_contact_methods w', 'ti.id = w.organisation_id AND w.type = \'W\' AND w.main');
	
		$fields = array(
			'ti.id',
			'ti.name',
			'town' => 'a.town',
			'county' => 'a.county',
			'phone' => 'p.contact',
			'fax' => 'f.contact',
			'email' => 'e.contact',
			'website' => 'w.contact',
		);
		$non_admin_fields = array(
			'ti.assigned_to',
			'ti.owner',
			'organisation_id' => 'ti.id',
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
			
			$qb->left_join('organisation_roles oroles', 'oroles.organisation_id = ti.id AND oroles.read');
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
			$cc_public->add(new Constraint('ti.id','IS','NULL'));
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
	 * Returns the DELETE query used to delete companies by tag
	 *
	 * @param string $tag_string
	 * @param int $count
	 * @return QueryBuilder
	 */
	public function getQueryForTagDeletion($tag_string,$count) {
		$select_query = $this->getQueryForTagSearch($tag_string,$count);
		$select_query->select_simple(array('ti.id'));
		$select_query->left_join('tactile_accounts ta', 'ta.organisation_id = ti.id');
		$select_query->where(new Constraint('ta.id', 'IS', 'NULL'));
		
		$db = DB::Instance();
		$delete_query = new QueryBuilder($db, $this);
		$delete_query->delete();
		$delete_query->from('organisations');
		
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
	public function getQueryForRestrictedTagList($tag_string, $count) {
		$db = DB::Instance();
		
		if (!isModuleAdmin()) {
			return 'SELECT t.name FROM tag_map tm JOIN tags t ON(t.id = tm.tag_id)
			JOIN organisations org ON (org.id=tm.organisation_id)
			LEFT JOIN organisation_roles cr ON org.id = cr.organisation_id AND cr.read
			LEFT JOIN hasrole hr ON cr.roleid = hr.roleid
			WHERE t.usercompanyid= ' . $db->qstr(EGS::getCompanyId()) . '
			AND hr.username = ' . $db->qstr(EGS::getUsername()) . '
			AND tm.organisation_id IN 
				(SELECT tm.organisation_id FROM tag_map tm JOIN tags t 
				ON tm.tag_id=t.id WHERE (t.name IN (' . $tag_string . ')) 
				GROUP BY tm.organisation_id HAVING COUNT(tm.organisation_id) = ' . $db->qstr($count) . ') 
			GROUP BY tm.tag_id, t.name ORDER BY lower(t.name)';
		} else {
			return 'SELECT t.name FROM tag_map tm JOIN tags t ON(t.id = tm.tag_id)
			JOIN organisations org ON (org.id=tm.organisation_id)
			WHERE t.usercompanyid= ' . $db->qstr(EGS::getCompanyId()) . ' AND tm.organisation_id IN 
				(SELECT tm.organisation_id FROM tag_map tm JOIN tags t 
				ON tm.tag_id=t.id WHERE (t.name IN (' . $tag_string . ')) 
				GROUP BY tm.organisation_id HAVING COUNT(tm.organisation_id) = ' . $db->qstr($count) . ') 
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
					tm.organisation_id IS NOT NULL
				) 
				LEFT JOIN organisations o ON (
					t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . ' AND
					o.id = tm.organisation_id
				)
				LEFT JOIN organisation_roles oroles ON (
					o.id = oroles.organisation_id AND 
					oroles.read
				)
				LEFT JOIN hasrole hr ON (
					oroles.roleid = hr.roleid AND 
					hr.username = ' . $db->qstr(EGS::getUsername()) . '
				)
				WHERE (
					o.owner = '.$db->qstr(EGS::getUsername()).' OR
					o.assigned_to = '.$db->qstr(EGS::getUsername()).' OR
					hr.username = '.$db->qstr(EGS::getUsername()).'
				)
				ORDER BY lower(t.name)';
		} else {
			return 'SELECT DISTINCT t.name, lower(t.name) FROM tags t
				JOIN tag_map tm ON (
					t.id = tm.tag_id AND
					t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . ' AND
					tm.organisation_id IS NOT NULL
				)
				JOIN organisations o ON (
					o.id = tm.organisation_id AND
					t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . '
				)
				ORDER BY lower(t.name)';
		}
	}

	public function getQueryForRecentlyViewedSearch() {
		$db = DB::Instance();
		
		$query = new QueryBuilder($db, $this);
		//x = viewed item
		$fields = array('x.id', 'x.name', 'website'=>'w.contact', 
				'phone'=>'p.contact', 'fax'=>'f.contact', 
				'email'=>'e.contact', 'town'=>'a.town',
				'county'=>'a.county');
		$cc = new ConstraintChain();
		$cc->add(new Constraint('x.usercompanyid','=',EGS::getCompanyId()));
		$cc->add(new Constraint('hr.username','=',EGS::getUsername()));
		
		$cc->add(new Constraint('rv.owner','=',EGS::getUsername()));
		
		$query->select_simple($fields)
				->from('organisations x')
				->join('recently_viewed rv','x.id=rv.link_id AND rv.type='.$db->qstr(ViewedPage::TYPE_ORGANISATION))
				->left_join('organisation_roles cr','x.id=cr.organisation_id AND cr.read')
				->left_join('hasrole hr','cr.roleid=hr.roleid')
				->left_join('organisation_addresses a', 'x.id = a.organisation_id AND a.main')
				->left_join('organisation_contact_methods p','x.id=p.organisation_id AND p.type=\'T\' AND p.main')
				->left_join('organisation_contact_methods f','x.id=f.organisation_id AND f.type=\'F\' AND f.main')
				->left_join('organisation_contact_methods e','x.id=e.organisation_id AND e.type=\'E\' AND e.main')
				->left_join('organisation_contact_methods w','x.id=w.organisation_id AND w.type=\'W\' AND w.main')
				->where($cc)
				->orderby('x.name','ASC');
		return $query;
	}
	
	/**
	 * Returns a human-readable string informing of the roles able to read the company
	 * @return String
	 * @see getAccessString()
	 */
	function getReadString() {
		return $this->getAccessString('read');
	}

	/**
	 * Returns a human-readable string informing of the roles able to write/edit the company
	 * @return String
	 * @see getAccessString()
	 */
	function getWriteString() {
		return $this->getAccessString('write');
	}

	/**
	 * Returns a human-readable string informing of the roles that have access of a particular type to the company
	 * @param String $type Either 'read' or 'write'
	 */
	function getAccessString($type) {
		$type_roles = $this->getRoles($type);
		$everyone = '//' . Omelette::getUserSpace();
		$user = EGS::getUsername();
		if(in_array($everyone, $type_roles)) {
			$string = 'by everyone';
		} else if(count($type_roles) == 1 && current($type_roles) == $user) {
			$string = 'only by you';
		} else {
			$string = 'by ';
			$and_you = false;
			if (count($type_roles) > 0) {
				foreach($type_roles as $role) {
					if($role !== $everyone && $role !== $user) {
						$string .= str_replace('//' . Omelette::getUserSpace(), '', $role) . ', ';
					} elseif ($role == $user) {
						$and_you = true;
					}
				}
				$string = preg_replace('#, $#', '', $string);
				if(EGS::getUsername() == $this->owner || $and_you) {
					$string .= ' and you';
				}
			} else {
				$string .= 'nobody!';
			}
		}
		return $string;
	}

	/**
	 * Returns an array of roles (name=>id) with access of $type to the company 
	 * The function performs a query once, which is the cached for future calls
	 * 
	 * @todo should probably cache the array-manipulation
	 * @param String $type Either 'read' or 'write'
	 */
	function getRoles($type) {
		$id = $this->id;
		
		if(empty($id)) {
			return array();
		}
		$roles = $this->roles;
		if(empty($roles)) {
			$db = DB::Instance();
			$query = 'SELECT r.name AS role, read, write, r.id AS id FROM organisation_roles cr JOIN roles r ON (cr.roleid=r.id) WHERE cr.organisation_id=' . $db->qstr($this->id) . ' AND r.usercompanyid=' . $db->qstr(EGS::getCompanyId());
			$roles = $db->GetAssoc($query);
			$this->roles = $roles;
		}
		$type_roles = array();
		foreach($roles as $role=>$access) {
			if($type == 'write' && $access['write'] == 't') {
				$type_roles[$access['id']] = $role;
			} else if($type == 'read' && $access['read'] == 't') {
				$type_roles[$access['id']] = $role;
			}
		}
		return $type_roles;
	}

	/**
	 * Returns an Array of roles (name=>id) that have read-access to the company
	 * 
	 * @return Array
	 */
	function getRead() {
		return $this->getAccess('read');
	}

	/**
	 * Returns an Array of roles (name=>id) that have write-access to the company
	 * 
	 * @return Array
	 */
	function getWrite() {
		return $this->getAccess('write');
	}

	/**
	 * Returns a code for the type of access set on the company for $type
	 * if the 'everyone' role has access, then 'everyone' is returned
	 * if just the current user has access, then 'private'
	 * else 'multi'
	 * 
	 * @param String $type Either 'read' or 'write'
	 * @return String
	 */
	function getAccess($type) {
		$type_roles = $this->getRoles($type);
		$everyone = '//' . Omelette::getUserSpace();
		$user = EGS::getUsername();
		if(in_array($everyone, $type_roles)) {
			$return = 'everyone';
		} else if(count($type_roles) == 1 && current($type_roles) == $user) {
			$return = 'private';
		} else {
			$return = 'multi';
		}
		return $return;
	}

	/**
	 * Returns an array of role-ids that have read access to the company
	 * 
	 * This kind-of assumes that you've already worked out that the access-type is 'multi',
	 * as it won't make sense otherwise
	 * 
	 * @see getAccess()
	 * @return Array
	 */
	function getReadRoles() {
		if($this->getAccess('read') == 'multi') {
			return array_keys($this->getRoles('read'));
		}
	}

	/**
	 * Returns an array of role-ids that have read access to the company
	 * 
	 * This kind-of assumes that you've already worked out that the access-type is 'multi',
	 * as it won't make sense otherwise
	 * 
	 * @see getAccess()
	 * @return Array
	 */
	function getWriteRoles() {
		if($this->getAccess('write') == 'multi') {
			return array_keys($this->getRoles('write'));
		}
	}

	/**
	 * Returns true iff the active user should be able delete the company
	 */
	function canDelete() {
		return (isModuleAdmin() || $this->owner == EGS::getUsername()); 
	}

	/**
	 * Returns true iff the active user can edit/write to the company
	 * 
	 * @return Boolean
	 */
	function canEdit() {
		// Admins can edit
		if (isModuleAdmin()) {
			return true;
		}
		
		return self::CheckAccess('write', $this);
	}

	/**
	 * Returns true iff the active user is able to view the company
	 * 
	 * @return Boolean
	 */
	function canView() {
		// Admins can view
		if (isModuleAdmin()) {
			return true;
		}
		
		return self::CheckAccess('read', $this);
	}

	/**
	 * over-ridden so that we mask as a Company model
	 */
	function get_name() {
		return 'Organisation';
	}
	
	public static function findByPhoneNumber($number) {
		$db = DB::Instance();
		$query = "SELECT c.* FROM company c join organisation_contact_methods ccm on 
				(c.id = ccm.organisation_id and type IN ('T') AND replace(contact,' ','') =  " . $db->qstr($number) .")
				WHERE c.usercompanyid = " . $db->qstr(EGS::getCompanyId());
		$rows = $db->getArray($query);
		return $rows;
	}
	
	public function asJson() {
		$json = array();
		
		$string_fields = array('name', 'description', 'accountnumber',
			'phone', 'email',
			'owner', 'assigned_to');
		$address_fields = array('street1', 'street2', 'street3', 'town', 'county', 'postcode', 'country', 'country_code');
		$int_fields = array('id', 'parent_id');
		$formatted_fields = array('parent');
		$datetime_fields = array('created', 'lastupdated');
		$crm_fields = array('status', 'source', 'classification', 'rating', 'industry', 'type');
		
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
		foreach ($formatted_fields as $field) {
			$value = $this->getFormatted($field);
			$json[$field] = ((is_null($value) || '' === $value) ? null : $value);
		}
		foreach ($datetime_fields as $field) {
			$value = $this->$field;
			$json[$field] = ((is_null($value) || '' === $value) ? null : date('Y-m-d\TH:i:sO', strtotime($value)));
		}
		foreach ($crm_fields as $field) {
			$value = $this->{'company_'.$field};
			$json[$field] = ((is_null($value) || '' === $value) ? null : $value);
		}
		$value = $this->getFormatted('parent');
		$json['parent'] = ((is_null($value) || '' === $value ? null : $value));
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
				o.organisation_id ='.$db->qstr($id);

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
				o.organisation_id='.$db->qstr($id);

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
				o.organisation_id='.$db->qstr($id);

		$won = $db->GetOne($query);

		$query = 'SELECT 
			count(*)
			FROM
				opportunities o
			WHERE
				o.organisation_id='.$db->qstr($id);

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
		$sh->addConstraint(new Constraint('organisations', '=', 'true'));
		$customfieldsCollection->load($sh);
		return $customfieldsCollection;
	}
	
	public function getCustomValues() {
		$customfieldsMapCollection = new CustomfieldMapCollection();
		$sh = new SearchHandler($customfieldsMapCollection, false);
		$sh->addConstraint(new Constraint('organisation_id','=',$this->id));
		$sh->extractOrdering();
		$customfieldsMapCollection->load($sh);
		return $customfieldsMapCollection;
	}
}
