<?php

class OpportunityExporter extends Exporter {
	
	protected static $_returned_fields = array(
		'opp.id', 'opp.name', 'status','opp.organisation_id', 'organisation',
		'opp.person_id', 'person', 'opp.description', 'cost', 'probability',
		'enddate', 'type', 'opp.source', 'opp.owner', 'opp.assigned_to',
		'opp.created', 'opp.lastupdated'
	);
	
	protected function _exportQuery() {
		$collection = new Tactile_OpportunityCollection();
		$sh = new SearchHandler($collection, false);
		$qb = $collection->getLoadQuery($sh);
		$qb->select_simple(self::$_returned_fields);
		$qb->orderby('opp.'.$this->_order, 'ASC');
		
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
				$qb->where(new Constraint('opp.'.$key, '=', $value));
			} else {
				$qb->where(new Constraint('opp.'.$key, 'ILIKE', $value));
			}
		}
		
		$this->outputRows($qb);
		return $this->getFormatter()->getStream();
	}
	
	public function getByTag($tags) {
		if (!is_array($tags)) {
			$tags = array($tags);
		}
		$db = DB::Instance();
		$tag_string = '';
		foreach($tags as $tag) {
			$tag_string .= $db->qstr($tag).',';
		}
		$tag_string = rtrim($tag_string,',');
		
		$model = new Tactile_Opportunity();
		
		$qb = $model->getQueryForTagSearch($tag_string, count($tags));
		
		$fields = self::$_returned_fields;
		foreach($fields as $alias => $field) {
			$fields[$alias] = str_replace('opp.', 'ti.', $field);
			if(isset($map[$field])) {
				$fields[$field] = $map[$field];
				unset($fields[$alias]);
			}
		}
		
		$qb->from('opportunities_overview ti');
		$qb->select_simple($fields);
		$qb->group_by(isModuleAdmin() ? $fields : array_merge($fields, array('oroles.roleid')));
		$qb->orderby('ti.'.$this->_order, 'ASC');
		
		$this->outputRows($qb);
		return $this->getFormatter()->getStream();
	}
	
	protected function _addTags($rows) {
		$db = DB::Instance();
		$opps = array();
		foreach($rows as $row) {
			if (empty($row['id'])) {
				throw new Exception('Missing ID for export tagging!');
			}
			$opps[$row['id']] = $row;
		}
		
		$query = 'SELECT tm.opportunity_id, t.name FROM tag_map tm JOIN tags t ON (tm.tag_id=t.id)
			 WHERE tm.opportunity_id IN ('.implode(',',array_keys($opps)).') ORDER BY t.name';
		
		$tag_map = $db->GetArray($query);
		if($tag_map !==false) {
			foreach($tag_map as $tags) {
				$opp_id = $tags['opportunity_id'];
				$tagname = $tags['name'];
				if(!isset($opps[$opp_id]['tags'])) {
					$opps[$opp_id]['tags'] = array();
				}
				$opps[$opp_id]['tags'][] = $tagname;
			}
		}
		return $opps;
	}
	
	protected function _addCustomFields($rows) {
		if (count($rows) < 1) {
			return array();
		}
		$db = DB::Instance();
		$opps = array();
		foreach($rows as $row) {
			$opps[$row['id']] = $row;
		}
		
		$sql = "SELECT o.id, cf.name as field, cf.type, cfm.value, cfm.enabled, cfo.value as option " .
				"FROM opportunities o " .
				"LEFT JOIN custom_field_map cfm ON o.id=cfm.opportunity_id " .
				"LEFT JOIN custom_fields cf ON cfm.field_id = cf.id " .
				"LEFT JOIN custom_field_options cfo ON cfm.option = cfo.id " .
				"WHERE o.id IN (".implode(',', array_keys($opps)).") ORDER BY cf.name";
		
		$cf_map = $db->getArray($sql);
		if ($cf_map !== FALSE) {
			foreach ($cf_map as $data) {
				$opp_id = $data['id'];
				$field_name = $data['field'];
				switch ($data['type']) {
					case 'c':
						$opps[$opp_id][$field_name] = ($data['enabled'] == 't' ? 'true' : 'false');
						break;
					case 's':
						$opps[$opp_id][$field_name] = $data['option'];
						break;
					case 'n':
						$opps[$opp_id][$field_name] = $data['value_numeric'];
						break;
					default:
						$opps[$opp_id][$field_name] = $data['value'];
				}
			}
		}
		return $opps;
	}
	
}
