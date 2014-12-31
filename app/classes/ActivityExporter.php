<?php

class ActivityExporter extends Exporter {
	
	protected static $_returned_fields = array(
		'act.id',
		'act.name',
		'act.created', 'act.lastupdated',
		'act.organisation_id', 'organisation', 'act.person_id', 'person', 'act.opportunity_id', 'opportunity',
		'act.description',
		'act.class', 'act.location',
		'act.later', 'act.date', 'act.time', 'act.end_date', 'act.end_time',
		'act.completed',
		'act.type',
		'act.owner', 'act.assigned_to'
	);
	
	protected function _exportQuery() {
		$collection = new Tactile_ActivityCollection();
		$sh = new SearchHandler($collection, false);
		$qb = $collection->getLoadQuery($sh);
		$qb->select_simple(self::$_returned_fields);
		$qb->orderby('act.'.$this->_order, 'ASC');
		
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
		
		$model = new Tactile_Activity();
		
		$qb = $model->getQueryForTagSearch($tag_string, count($tags));

		$fields = self::$_returned_fields;
		foreach($fields as $alias => $field) {
			$fields[$alias] = str_replace('act.', 'ti.', $field);
			if(isset($map[$field])) {
				$fields[$field] = $map[$field];
				unset($fields[$alias]);
			}
		}
		
		$qb->from('tactile_activities_overview ti');
		$qb->select_simple($fields);
		$qb->group_by(isModuleAdmin() ? $fields : array_merge($fields, array('oroles.roleid')));
		$qb->orderby('ti.'.$this->_order, 'ASC');		
		
		$this->outputRows($qb);
		return $this->getFormatter()->getStream();
	}
	
	protected function _addTags($rows) {
		$db = DB::Instance();
		$acts = array();
		foreach($rows as $row) {
		if (empty($row['id'])) {
				throw new Exception('Missing ID for export tagging!');
			}
			$acts[$row['id']] = $row;
		}
		
		$query = 'SELECT tm.activity_id, t.name FROM tag_map tm JOIN tags t ON (tm.tag_id=t.id)
			 WHERE tm.activity_id IN ('.implode(',',array_keys($acts)).') ORDER BY t.name';
		
		$tag_map = $db->GetArray($query);
		if($tag_map !==false) {
			foreach($tag_map as $tags) {
				$act_id = $tags['activity_id'];
				$tagname = $tags['name'];
				if(!isset($acts[$act_id]['tags'])) {
					$acts[$act_id]['tags'] = array();
				}
				$acts[$act_id]['tags'][] = $tagname;
			}
		}
		return $acts;
	}
	
	protected function _addCustomFields($rows) {
		if (count($rows) < 1) {
			return array();
		}
		$db = DB::Instance();
		$acts = array();
		foreach($rows as $row) {
			$acts[$row['id']] = $row;
		}
		
		$sql = "SELECT a.id, cf.name as field, cf.type, cfm.value, cfm.enabled, cfo.value as option " .
				"FROM tactile_activities a " .
				"LEFT JOIN custom_field_map cfm ON a.id=cfm.activity_id " .
				"LEFT JOIN custom_fields cf ON cfm.field_id = cf.id " .
				"LEFT JOIN custom_field_options cfo ON cfm.option = cfo.id " .
				"WHERE a.id IN (".implode(',', array_keys($acts)).") ORDER BY cf.name";
		
		$cf_map = $db->getArray($sql);
		if ($cf_map !== FALSE) {
			foreach ($cf_map as $data) {
				$act_id = $data['id'];
				$field_name = $data['field'];
				switch ($data['type']) {
					case 'c':
						$acts[$act_id][$field_name] = ($data['enabled'] == 't' ? 'true' : 'false');
						break;
					case 's':
						$acts[$act_id][$field_name] = $data['option'];
						break;
					case 'n':
						$acts[$act_id][$field_name] = $data['value_numeric'];
						break;
					default:
						$acts[$act_id][$field_name] = $data['value'];
				}
			}
		}
		return $acts;
	}
	
}
