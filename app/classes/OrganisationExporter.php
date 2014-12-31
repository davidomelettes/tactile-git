<?php

class OrganisationExporter extends Exporter {
	
	protected static $_returned_fields = array(
		'org.id', 'org.name', 'accountnumber', 'description',
		'phone' => 'p.contact', 'fax' => 'f.contact', 'email' => 'e.contact', 'website' => 'w.contact',
		'street1' => 'a.street1', 'street2' => 'a.street2', 'street3' => 'a.street3', 'town' => 'a.town', 'county' => 'a.county', 'postcode' => 'a.postcode', 'country_code' => 'a.country_code',
		'status' => 'company_statuses.name', 'source' => 'company_sources.name', 'classification' => 'company_classifications.name', 'industry' => 'company_industries.name',
		'org.created', 'org.lastupdated'
	);
	
	protected function _exportQuery() {
		$collection = new Omelette_OrganisationCollection();
		$sh = new SearchHandler($collection, false);
		$qb = $collection->getLoadQuery($sh);
		$qb->select_simple(self::$_returned_fields);
		$qb->orderby('org.'.$this->_order, 'ASC');
		
		$this->_addContactJoins($qb);
		$this->_addCRMJoins($qb);
		
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
		foreach ($tags as $tag) {
			$tag_string .= $db->qstr($tag).',';
		}
		$tag_string = rtrim($tag_string,',');
		
		$model = new Tactile_Organisation();
		
		$qb = $model->getQueryForTagSearch($tag_string, count($tags));
		
		$fields = self::$_returned_fields;
		$map = array(
			'phone'=>'p.contact',
			'email'=>'e.contact',
			'fax'=>'f.contact',
			'website'=>'w.contact'
		);
		foreach($fields as $alias => $field) {
			$fields[$alias] = str_replace('org.', 'ti.', $field);
			if(isset($map[$field])) {
				$fields[$field] = $map[$field];
				unset($fields[$alias]);
			}
		}
		
		$qb->select_simple($fields);
		$this->_addCRMJoins($qb, 'ti');
		if (!isModuleAdmin()) {
			$group_by = array_merge($fields,array('cr.roleid'));			
		} else {
			$group_by = $fields;
		}
		$qb->group_by($group_by);
		$qb->orderby('ti.'.$this->_order, 'ASC');
		
		$this->outputRows($qb);
		return $this->getFormatter()->getStream();
	}
	
	/**
	 * @return ConstraintChain
	 */
	/*function getDefaultConstraints() {
		$cc = new ConstraintChain();
		$cc->add(new Constraint('org.usercompanyid','=',$this->usercompanyid));
		
		return $cc;
	}*/
	
	protected function _addCRMJoins($qb, $alias = 'org') {
		$joins = array(
			'company_industries'=>'industry_id',
			'company_classifications'=>'classification_id',
			'company_sources'=>'source_id',
			'company_statuses'=>'status_id',
			'company_types'=>'type_id'
		);
		
		foreach($joins as $tablename => $fk) {
			$qb->left_join($tablename, $alias.'.'.$fk.'='.$tablename.'.id');
		}
		return $qb;
	}
	
	protected function _addContactJoins($qb, $alias = 'org') {
		$qb->left_join('organisation_addresses a',$alias.'.id=a.organisation_id AND a.main')
			->left_join('organisation_contact_methods p',$alias.'.id=p.organisation_id AND p.type=\'T\' AND p.main')
			->left_join('organisation_contact_methods f',$alias.'.id=f.organisation_id AND f.type=\'F\' AND f.main')
			->left_join('organisation_contact_methods e',$alias.'.id=e.organisation_id AND e.type=\'E\' AND e.main')
			->left_join('organisation_contact_methods w',$alias.'.id=w.organisation_id AND w.type=\'W\' AND w.main');
		return $qb;
	}
	
	protected function _addCustomFields($rows) {
		if (count($rows) < 1) {
			return array();
		}
		$db = DB::Instance();
		$orgs = array();
		foreach($rows as $row) {
			$orgs[$row['id']] = $row;
		}
		
		$sql = "SELECT o.id, cf.name as field, cf.type, cfm.value, cfm.enabled, cfo.value as option " .
				"FROM organisations o " .
				"LEFT JOIN custom_field_map cfm ON o.id=cfm.organisation_id " .
				"LEFT JOIN custom_fields cf ON cfm.field_id = cf.id " .
				"LEFT JOIN custom_field_options cfo ON cfm.option = cfo.id " .
				"WHERE o.id IN (".implode(',', array_keys($orgs)).") ORDER BY cf.name";
		
		$cf_map = $db->getArray($sql);
		if ($cf_map !== FALSE) {
			foreach ($cf_map as $data) {
				$organisation_id = $data['id'];
				$field_name = $data['field'];
				switch ($data['type']) {
					case 'c':
						$orgs[$organisation_id][$field_name] = ($data['enabled'] == 't' ? 'true' : 'false');
						break;
					case 's':
						$orgs[$organisation_id][$field_name] = $data['option'];
						break;
					case 'n':
						$orgs[$organisation_id][$field_name] = $data['value_numeric'];
						break;
					default:
						$orgs[$organisation_id][$field_name] = $data['value'];
				}
			}
		}
		return $orgs;
	}
	
	protected function _addTags($rows) {
		if (count($rows) === 0) {
			return array();
		}
		$db = DB::Instance();
		$orgs = array();
		foreach($rows as $row) {
			if (empty($row['id'])) {
				throw new Exception('Missing ID for export tagging!');
			}
			$orgs[$row['id']] = $row;
		}
		
		$query = 'SELECT tm.organisation_id, t.name FROM tag_map tm JOIN tags t ON (tm.tag_id=t.id)
			 WHERE tm.organisation_id IN ('.implode(',',array_keys($orgs)).') ORDER BY t.name';
		
		$tag_map = $db->GetArray($query);
		if($tag_map !==false) {
			foreach($tag_map as $tags) {
				$organisation_id = $tags['organisation_id'];
				$tagname = $tags['name'];
				if(!isset($orgs[$organisation_id]['tags'])) {
					$orgs[$organisation_id]['tags'] = array();
				}
				$orgs[$organisation_id]['tags'][] = $tagname;
			}
		}
		return $orgs;
	}
	
}
