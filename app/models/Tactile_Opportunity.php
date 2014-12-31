<?php

class Tactile_Opportunity extends Opportunity implements Taggable, TimelineItem {

	protected $defaultDisplayFields = array('name','organisation','person','status','cost','enddate','assigned_to', 'archived');
	
	public function __construct() {
		parent::__construct();
		$this->hasMany('Note', 'notes', 'n.opportunity_id');
		$this->hasMany('Email');
		$this->hasMany('Tactile_Activity','activities');
		$this->hasMany('S3File', 's3_files', 'f.opportunity_id');
		$this->hasMany('Flag');
		
		$this->getField('description')->setFormatter(new URLParsingFormatter());
		
		$this->addValidator(new OpportunityLimitValidator());
		
		$this->getField('probability')->addValidator(new NumericRangeValidator(0,100));
		$this->getField('status_id')->setnotnull();
		$this->getField('enddate')->tag = prettify('expected_close_date');
	}
	
	public static function getDefaultStatusId() {
		$statuses = new OpportunitystatusCollection();
		$sh = new SearchHandler($statuses, false);
		$sh->extract();
		$sh->setLimit(1);
		$sh->addConstraint(new Constraint('open', '=', 'TRUE'));
		$sh->setOrderby('position');
		$statuses->load($sh);
		$default_status = $statuses->current();
		return $default_status->id;
	}
	
	public static function factoryFromString($string = null, $data = array(), &$errors = array()) {
		$string = trim($string);
		if (empty($string)) {
			return false;
		}
		if (preg_match('/\d{4}-\d{2}-\d{2}/', $string, $matches) && FALSE !== strtotime($matches[0])) {
			$enddate = $matches[0];
		} elseif (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $string, $matches)) {
			$format = EGS::getDateFormat();
			$date = false;
			if ($format = 'm/d/Y') {
				$date = strtotime($matches[3].'-'.$matches[2].'-'.$matches[1]);
			} else {
				$date = strtotime($matches[3].'-'.$matches[1].'-'.$matches[2]);
			}
			$enddate = ($date === FALSE) ? date('Y-m-d', strtotime('+7 days')) : strtotime($date);
		} else {
			$enddate = date('Y-m-d', strtotime('+7 days'));
		}
		$data['name'] = $string;
		$data['enddate'] = $enddate;
		$data['status_id'] = Tactile_Opportunity::getDefaultStatusId();
		$saver = new ModelSaver();
		$opp = $saver->save($data, 'Tactile_Opportunity', $errors);
		return $opp;
	} 

	function get_name() {
		return 'Opportunity';
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
		// Else check the organisation
		$org = DataObject::Construct('Organisation');
		$org->load($this->organisation_id);
		return $org->canView();
	}
	
	public static function getValueGroupedByStatus() {
		$db = DB::Instance();
		$query = 'SELECT st.name, sum(o.cost) FROM opportunities o 
			LEFT JOIN opportunitystatus st ON (o.status_id=st.id)
			WHERE o.usercompanyid='.$db->qstr(EGS::getCompanyId()).' GROUP BY st.name';
		$costs = $db->GetAssoc($query);
		if($costs===false) {	
			echo $query;
			die($db->ErrorMsg());
		}
		return $costs;		
	}
	
	/**
	 * Returns the Select query that's used for loading opportunities 'by_tag'
	 *
	 * @param String $tag_string
	 * @param Int $count
	 * @return QueryBuilder
	 */
	public function getQueryForTagSearch($tag_string, $count) {
		$db = DB::Instance();
		
		$qb = new QueryBuilder($db, $this);
		$qb->orderby('ti.name','ASC')
			->from('opportunities ti')
			->left_join('tag_map tm', 'ti.id = tm.opportunity_id')
			->left_join('organisations org', 'org.id = ti.organisation_id')
			->left_join('people p', 'p.id = ti.person_id')
			->left_join('opportunitystatus os', 'os.id = ti.status_id');
		
		$fields = array(
			'ti.id',
			'ti.name',
			'ti.organisation_id',
			'ti.person_id',
			'organisation' => 'org.name',
			'person' => 'p.firstname || \' \' || p.surname',
			'status' => 'os.name',
			'ti.cost',
			'ti.enddate',
			'ti.assigned_to',
			'ti.archived'
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
	 * Returns the DELETE query used to delete opportunities by tag
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
		$delete_query->from('opportunities');
		
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
			return 'SELECT t.name FROM tag_map tm JOIN tags t ON (t.id = tm.tag_id)
				JOIN opportunities o ON (o.id = tm.opportunity_id)
				LEFT JOIN organisation_roles cr ON o.organisation_id = cr.organisation_id AND cr.read
				LEFT JOIN hasrole hr ON cr.roleid = hr.roleid
				WHERE t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . '
				AND (hr.username = ' . $db->qstr(EGS::getUsername()) . '
					OR o.owner = ' . $db->qstr(EGS::getUsername()) . '
					OR o.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
					OR o.organisation_id IS NULL)
				AND tm.opportunity_id IN 
				(SELECT tm.opportunity_id FROM tag_map tm JOIN tags t 
				ON tm.tag_id = t.id WHERE (t.name IN (' . $tag_string . ')) 
				GROUP BY tm.opportunity_id HAVING COUNT(tm.opportunity_id) = ' . $db->qstr($count) . ') 
				GROUP BY tm.tag_id, t.name ORDER BY lower(t.name)';
		} else {
			return 'SELECT t.name FROM tag_map tm JOIN tags t ON (t.id = tm.tag_id)
				JOIN opportunities o ON (o.id = tm.opportunity_id)
				WHERE t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . ' AND tm.opportunity_id IN 
				(SELECT tm.opportunity_id FROM tag_map tm JOIN tags t 
				ON tm.tag_id = t.id WHERE (t.name IN (' . $tag_string . ')) 
				GROUP BY tm.opportunity_id HAVING COUNT(tm.opportunity_id) = ' . $db->qstr($count) . ') 
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
					tm.opportunity_id IS NOT NULL
				) 
				LEFT JOIN opportunities o ON (
					o.id = tm.opportunity_id AND
					t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . '
				)
				LEFT JOIN organisation_roles oroles ON (
					o.organisation_id = oroles.organisation_id AND 
					oroles.read 
				)
				LEFT JOIN hasrole hr ON (
					oroles.roleid = hr.roleid AND 
					hr.username = ' . $db->qstr(EGS::getUsername()) . '
				)
				WHERE (
					o.owner = '.$db->qstr(EGS::getUsername()).' OR
					o.assigned_to = '.$db->qstr(EGS::getUsername()).' OR
					o.organisation_id IS NULL OR
					hr.username = '.$db->qstr(EGS::getUsername()).'
				)
				ORDER BY lower(t.name)';
		} else {
			return 'SELECT DISTINCT t.name, lower(t.name) FROM tags t
				JOIN tag_map tm ON (
					t.id = tm.tag_id AND
					t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . ' AND
					tm.opportunity_id IS NOT NULL
				)
				JOIN opportunities o ON (
					o.id = tm.opportunity_id AND
					t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . '
				)
				ORDER BY lower(t.name)';
		}
	}
	
	public function getQueryForRecentlyViewedSearch() {
		$db = DB::Instance();
		
		$query = new QueryBuilder($db, $this);
		$fields = array(
			'opp.id',
			'opp.name',
			'company'=>'org.name',
			'opp.organisation_id',
			'opp.person_id',
			'person'=>'p.firstname || \' \' || p.surname',
			'status' => 'stat.name',
			'opp.cost',
			'opp.enddate',
			'opp.assigned_to',
			'opp.archived'
		);
		$cc = new ConstraintChain();
		$cc->add(new Constraint('opp.usercompanyid','=',EGS::getCompanyId()));
		$cc->add(new Constraint('rv.owner','=',EGS::getUsername()));
		
		$query->select_simple($fields)
			->from('opportunities opp')
			->left_join('opportunitystatus stat', 'stat.id=opp.status_id')
			->join('recently_viewed rv','opp.id=rv.link_id AND rv.type='.$db->qstr(ViewedPage::TYPE_OPPORTUNITY))
			->left_join('organisations org','opp.organisation_id=org.id')
			->left_join('people p','opp.person_id=p.id')
			->where($cc);
			
		return $query;
	}
	
	public function asJson() {
		$json = array();
		
		$string_fields = array('name', 'description', 'organisation', 'owner', 'assigned_to', 'alteredby');
		$int_fields = array('id', 'probability', 'organisation_id');
		$float_fields = array('cost');
		$boolean_fields = array('archived');
		$formatted_fields = array('status', 'type', 'source');
		$datetime_fields = array('created', 'lastupdated');
		$date_fields = array('enddate');
		
		foreach ($string_fields as $field) {
			$value = $this->$field;
			$json[$field] = ((is_null($value) || '' === $value) ? null : (string) $value);
		}
		foreach ($int_fields as $field) {
			$value = $this->$field; 
			$json[$field] = ((is_null($value) || '' === $value) ? null : (int) $value);
		}
		foreach ($float_fields as $field) {
			$value = $this->$field;
			$json[$field] = ((is_null($value) || '' === $value) ? null : (float) $value);
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
		
		$value = $this->person_id;
		if (empty($value)) {
			$json['person_id'] = null;
			$json['person'] = null;
		} else {
			$json['person_id'] = (int) $value;
			$json['person'] = $this->person;
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
	
	public function getTimelineType() {
		return 'Opportunity';
	}
	
	public function getTimelineDate() {
		$formatter = new TimelineTimestampFormatter();
		return $formatter->format($this->created);
	}
	
	public function getTimelineTime() {
		return $this->created;
	}
	
	public function getTimelineSubject() {
		return $this->getFormatted('name');
	}
	
	public function getTimelineBody() {
		return $this->getFormatted('description');
	}
	
	public function getTimelineURL() {
		return '/opportunities/view/'.$this->id;
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
		$sh->addConstraint(new Constraint('opportunities', '=', 'true'));
		$customfieldsCollection->load($sh);
		return $customfieldsCollection;
	}
	
	public function getCustomValues() {
		$customfieldsMapCollection = new CustomfieldMapCollection();
		$sh = new SearchHandler($customfieldsMapCollection, false);
		$sh->addConstraint(new Constraint('opportunity_id','=',$this->id));
		$sh->extractOrdering();
		$customfieldsMapCollection->load($sh);
		return $customfieldsMapCollection;
	}

	public function isWon() {
		$db = DB::Instance();
		$query = 'SELECT won
			FROM opportunities_overview
			WHERE id='.$db->qstr($this->id);
		return $db->GetOne($query);
	}

	public function age() {
		$db = DB::Instance();
		$query = "SELECT 
			CASE WHEN won THEN
				CASE WHEN date_trunc('days', enddate - created) < interval '1 day' THEN '1 day'
				ELSE date_trunc('days', enddate - created) END
			 ELSE 
				CASE WHEN date_trunc('days', now() - created) < interval '1 day' THEN '1 day'
				ELSE date_trunc('days', now() - created) END
			END AS age
			FROM opportunities_overview
			WHERE id=".$db->qstr($this->id);

		return $db->GetOne($query);
	}
	
	public function getRelatedContacts() {
		$db = DB::Instance();
		$query = '
			SELECT
				CASE WHEN c.person_id IS NOT NULL THEN \'person\' ELSE \'organisation\' END AS type,
				CASE WHEN c.person_id IS NOT NULL THEN p.firstname || \' \' || p.surname ELSE o.name END AS name,
				CASE WHEN c.person_id IS NOT NULL THEN p.id ELSE o.id END AS id,
				c.relationship
			FROM
				opportunity_contacts c LEFT OUTER JOIN
				organisations o ON (c.organisation_id=o.id AND o.usercompanyid=' . $db->qstr(EGS::getCompanyId()) . ') LEFT OUTER JOIN
				people p ON (c.person_id=p.id AND p.usercompanyid=' . $db->qstr(EGS::getCompanyId()) . ')
			WHERE
				c.opportunity_id=' . $db->qstr($this->id).'
			ORDER BY name';
		return $db->getAll($query);
	}
	
}
