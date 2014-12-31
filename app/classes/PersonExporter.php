<?php

class PersonExporter extends Exporter {
	
	protected static $_returned_fields = array(
		'per.id', 'title', 'firstname', 'surname', 'suffix','jobtitle','organisation'=>'org.name', 'per.organisation_id',
		'dob', 'can_call', 'can_email', 'language_code',
		'phone'=>'p.contact', 'mobile'=>'m.contact', 'email'=>'e.contact',
		'a.street1', 'a.street2', 'a.street3', 'a.town', 'a.county', 'a.postcode', 'a.country_code',
		'per.description',
		'per.created', 'per.lastupdated'
	);
	
	protected function _exportQuery() {
		$collection = new Omelette_PersonCollection();
		$sh = new SearchHandler($collection, false);
		$qb = $collection->getLoadQuery($sh);
		$qb->select_simple(self::$_returned_fields);
		$qb->orderby('per.'.$this->_order, 'ASC');
		
		$this->_addContactJoins($qb);
		
		return $qb;
	}
	
	public function getCount(QueryBuilder $qb) {
		$db = DB::Instance();
		$cqb = clone($qb);
		$cqb->select_simple(array("count(*)"));
		$cqb->orderby('','');
		$count = $db->GetOne($cqb->__toString());
		if ($count === FALSE) {
			throw new Exception('Failed to count results: '.$cqb->__toString()." \n".$db->ErrorMsg());
		}
		return $count;
	}
	
	public function getBy($key = null, $value = null) {
		$db = DB::Instance();
		
		$qb = $this->_exportQuery();
		if (!(is_null($key) || is_null($value))) {
			if (is_bool($value)) {
				$qb->where(new Constraint($key, '=', $value));
			} else {
				$qb->where(new Constraint($key, 'ILIKE', $value));
			}
		}
		
		$this->outputRows($qb);
		return $this->getFormatter()->getStream();
	}
	
	public function getByTag($tags) {
		if(!is_array($tags)) {
			$tags = array($tags);
		}
		$db = DB::Instance();
		$tag_string = '';
		foreach($tags as $tag) {
			$tag_string .= $db->qstr($tag).',';
		}
		$tag_string = rtrim($tag_string,',');
		
		$model = new Tactile_Person();
		
		$qb = $model->getQueryForTagSearch($tag_string, count($tags));
		
		$fields = self::$_returned_fields;
		$map = array(
			'phone'=>'p.contact',
			'email'=>'e.contact',
			'mobile'=>'m.contact'
		);
		foreach($fields as $alias => $field) {
			$fields[$alias] = str_replace('per.', 'ti.', $field);
			if(isset($map[$field])) {
				$fields[$field] = $map[$field];
				unset($fields[$alias]);
			}
		}
		
		$qb->select_simple($fields);
		$qb->group_by(isModuleAdmin() ? $fields : array_merge($fields, array('oroles.roleid')));
		$qb->orderby('ti.'.$this->_order, 'ASC');
		
		$this->outputRows($qb);
		return $this->getFormatter()->getStream();
	}
	
	protected function _addCustomFields($rows) {
		if (count($rows) < 1) {
			return array();
		}
		$db = DB::Instance();
		$people = array();
		foreach($rows as $row) {
			$people[$row['id']] = $row;
		}
		
		$sql = "SELECT per.id, cf.name as field, cf.type, cfm.value, cfm.enabled, cfo.value as option " .
				"FROM people per " .
				"LEFT JOIN custom_field_map cfm ON per.id=cfm.person_id " .
				"LEFT JOIN custom_fields cf ON cfm.field_id = cf.id " .
				"LEFT JOIN custom_field_options cfo ON cfm.option = cfo.id " .
				"WHERE per.id IN (".implode(',', array_keys($people)).") ORDER BY cf.name";
		
		$cf_map = $db->getArray($sql);
		if ($cf_map !== FALSE) {
			foreach ($cf_map as $data) {
				$person_id = $data['id'];
				$field_name = $data['field'];
				switch ($data['type']) {
					case 'c':
						$people[$person_id][$field_name] = ($data['enabled'] == 't' ? 'true' : 'false');
						break;
					case 's':
						$people[$person_id][$field_name] = $data['option'];
						break;
					case 'n':
						$people[$person_id][$field_name] = $data['value_numeric'];
						break;
					default:
						$people[$person_id][$field_name] = $data['value'];
				}
			}
		}
		return $people;
	}
	
	protected function _addTags($rows) {
		if (count($rows) === 0) {
			return array();
		}
		$db = DB::Instance();
		$people = array();
		foreach($rows as $row) {
			if (empty($row['id'])) {
				throw new Exception('Missing ID for export tagging!');
			}
			$people[$row['id']] = $row;
		}
		
		$query = 'SELECT tm.person_id, t.name FROM tag_map tm JOIN tags t ON (tm.tag_id=t.id)
			 WHERE tm.person_id IN ('.implode(',',array_keys($people)).') ORDER BY t.name';
		
		$tag_map = $db->GetArray($query);
		if($tag_map !==false) {
			foreach($tag_map as $tags) {
				$person_id = $tags['person_id'];
				$tagname = $tags['name'];
				if(!isset($people[$person_id]['tags'])) {
					$people[$person_id]['tags'] = array();
				}
				$people[$person_id]['tags'][] = $tagname;
			}
		}
		return $people;
	}
	
	protected function _addContactJoins($qb, $alias = 'per') {
		$qb->left_join('person_addresses a',$alias.'.id=a.person_id AND a.main')
			->left_join('person_contact_methods p',$alias.'.id=p.person_id AND p.type=\'T\' AND p.main')
			->left_join('person_contact_methods m',$alias.'.id=m.person_id AND m.type=\'M\' AND m.main')
			->left_join('person_contact_methods e',$alias.'.id=e.person_id AND e.type=\'E\' AND e.main');
		return $qb;
	}
}
