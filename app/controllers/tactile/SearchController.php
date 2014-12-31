<?php

function searchGroupSort($a, $b) {
	$ca = count($a);
	$cb = count($b);
	if ($ca === $cb) {
		return 0;
	}
	return $ca > $cb ? 1 : -1;
}

class SearchController extends Controller {
	
	protected function _filterBy($input, $key, $value) {
		$output = array();
		foreach ($input as $id => $array) {
			if ($array[$key] == $value) {
				$output[$id] = $array;
			}
		}
		return $output;
	}
	
	public function index() {
		$name = !empty($this->_data['name']) ? $this->_data['name'] : '';
		$name = strtolower(trim($name));
		if (empty($name)) {
			return false;
		}
		$db = DB::Instance();
		
		$types = array('organisations', 'people', 'opportunities', 'activities');
		$qbs = array();
		foreach ($types as $type) {
			$qb = new QueryBuilder($db);
			$qb->orderby('name', 'asc')
				->from($type == 'activities' ? 'tactile_activities s' : ($type . ' s'));
			
			$fields = array();
			$non_admin_fields = array();
			switch ($type) {
				case 'organisations':
					$fields = array('s.id', 'usercompanyid', 'type' => "'$type'", 'name');
					$non_admin_fields = array('assigned_to', 'owner', 'organisation_id' => 's.id', 'private' => 'false');
					break;
				case 'people':
					$fields = array('s.id', 'usercompanyid', 'type' => "'$type'", 'name' => "(firstname::varchar || ' '::varchar || surname::varchar)");
					$non_admin_fields = array('assigned_to', 'owner', 's.organisation_id', 'private');
					break;
				case 'opportunities':
					$fields = array('s.id', 'usercompanyid', 'type' => "'$type'", 'name');
					$non_admin_fields = array('assigned_to', 'owner', 's.organisation_id', 'private' => 'false');
					break;
				case 'activities':
					$fields = array('s.id', 'usercompanyid', 'type' => "'$type'", 'name');
					$non_admin_fields = array('assigned_to', 'owner', 's.organisation_id', 'private' => 'false');
					break;
			}
			
			$cc = new ConstraintChain();
			$cc->add(new Constraint('usercompanyid', '=', EGS::getCompanyId()));
			if ($type == 'people') {
				$cc->add(new Constraint("(firstname::varchar || ' '::varchar || surname::varchar)", 'ILIKE', $name.'%'));
			} else {
				$cc->add(new Constraint('name', 'ILIKE', $name.'%'));
			}
			
			if (!isModuleAdmin()) {
				$fields = array_merge($fields, $non_admin_fields);
				
				if ($type == 'organisations') {
					$qb->left_join('organisation_roles oroles', 'oroles.organisation_id = s.id AND oroles.read');
				} else {
					$qb->left_join('organisation_roles oroles', 'oroles.organisation_id = s.organisation_id AND oroles.read');
				}
				$qb_roles = new QueryBuilder($db);
				$qb_roles->select_simple(array('roleid'))
					->from('hasrole')
					->where(new Constraint('username', '=', EGS::getUsername()));
				$c_roles = new Constraint('oroles.roleid', 'IN', '('.$qb_roles->__toString().')');
				
				$cc_access = new ConstraintChain();
				$cc_access->add($c_roles);
				$cc_access->add(new Constraint('owner','=',EGS::getUsername()), 'OR');
				$cc_access->add(new Constraint('assigned_to','=',EGS::getUsername()), 'OR');
				$cc_public = new ConstraintChain();
				$cc_public->add(new Constraint(($type == 'organisations' ? 's.id' : 's.organisation_id'),'IS','NULL'));
				if ($type == 'people') {
					$cc_public->add(new Constraint('private','=','false'));
				}
				$cc_access->add($cc_public, 'OR');
				$cc->add($cc_access);
				
			}
			
			$qb->select_simple($fields);
			$qb->where($cc);
			
			$qbs[] = $qb;
		}
		
		$union_qb = array_shift($qbs);
		foreach ($qbs as $select_qb) {
			$union_qb->union($select_qb, TRUE);
		}
		$union_qb->union_order(array('type', 'name'), array('asc', 'asc'));
		
		if (FALSE === ($results = $db->getAssoc($union_qb->__toString()))) {
			throw new Exception('Failed to load Search Results! ' . $db->ErrorMsg());
		}
			
		if (count($results) > 10) {
			$this->view->set('fold_results', true);
		}
		
		$orgs = $this->_filterBy($results, 'type', 'organisations');
		$people = $this->_filterBy($results, 'type', 'people');
		$opps = $this->_filterBy($results, 'type', 'opportunities');
		$acts = $this->_filterBy($results, 'type', 'activities');
		
		$orgs_by_acnum = new Omelette_OrganisationCollection();
		$sh = new SearchHandler($orgs_by_acnum, false);
		$sh->extract();
		$sh->addConstraint(new Constraint('accountnumber', 'ILIKE', $name));
		$orgs_by_acnum->load($sh);
		
		$people_by_surname = new Omelette_PersonCollection();
		$sh = new SearchHandler($people_by_surname, false);
		$sh->extractOrdering();
		$sh->extractPaging();
		$sh->addConstraint(new Constraint('surname', 'ILIKE', $name.'%'));
		$people_by_surname->load($sh);
	
		if (preg_match('/@/', $name)) {
			$orgs_by_email = new Omelette_OrganisationCollection();
			$sh = new SearchHandler($orgs_by_email, false);
			$sh->extract();
			$sh->addConstraint(new Constraint('e.contact', '=', $name));
			$orgs_by_email->load($sh);
			
			$people_by_email = new Omelette_PersonCollection();
			$sh = new SearchHandler($people_by_email, false);
			$sh->extractOrdering();
			$sh->extractPaging();
			$sh->addConstraint(new Constraint('e.contact', '=', $name));
			$people_by_email->load($sh);
		} else {
			$orgs_by_email = array();
			$people_by_email = array();
		}
		
		$group_results = array(
			'organisations' 					=> $orgs,
			'people'							=> $people,
			'opportunities'						=> $opps,
			'activities'						=> $acts,
			'organisations_by_accountnumber'	=> $orgs_by_acnum,
			'people_by_surname'					=> $people_by_surname,
			'organisations_by_email'			=> $orgs_by_email,
			'people_by_email'					=> $people_by_email
		);
		
		// Tally up final results
		$total_results = 0;
		$groups_with_results = 0;
		$results_to_display = array();
		foreach ($group_results as $key => $group) {
			$count = count($group);
			$total_results += $count;
			if ($count) {
				$groups_with_results++;
			}
			$results_to_display[$key] = 0;
			$this->view->set($key, $group);
		}
		$this->view->set('total_results', $total_results);
		
		if ($total_results > 0) {
			// Establish the minimum results to display per group, and count how many spaces are not used up
			$max_results = $groups_with_results > 3 ? 18 : 20;
			$min_per_group = floor($max_results / $groups_with_results); 
			$free_slots = 0;
			foreach ($group_results as $key => $group) {
				$count = count($group);
				if ($count > 0) {
					$results_to_display[$key] = $count > $min_per_group ? $min_per_group : $count;
					if ($count < $min_per_group) {
						$free_slots += ($min_per_group - $count);
					}
				} else {
					$results_to_display[$key] = 0;
				}
			}
			
			// Sort results and assign free spaces to other groups if needed
			$sorted_results = $group_results;
			uasort($sorted_results, 'searchGroupSort');
			$n = 0;
			while ($free_slots > 0 && $n < $max_results) {
				foreach ($sorted_results as $key => $group) {
					if ($free_slots > 0 && count($group) > $results_to_display[$key]) {
						$results_to_display[$key]++;
						$free_slots--;
					}
				}
				$n++;
			}
		}
		
		$this->view->set('results_to_display', $results_to_display);
		$this->view->set('query', $name);
	}
	
	public function advanced() {
		$db = DB::Instance();
		
		$types = array('org'=>'organisations', 'per'=>'people', 'opp'=>'opportunities', 'act'=>'activities');
		$user_options = $db->getAssoc("SELECT username, regexp_replace(username, '//[^/]+$', '') FROM users WHERE enabled AND username like " . $db->qstr('%//'.Omelette::getUserSpace()) . " ORDER BY username");
		
		$qb_fields = array(
			'gen_name' => array(
				'label'		=> 'Name',
				'operators'	=> array('IS', 'IS NOT', 'CONTAINS', 'DOES NOT CONTAIN', 'BEGINS WITH'),
				'type'		=> 'db_column',
				'column'	=> 'name',
				'accept'	=> 'text'
			),
			'gen_description' => array(
				'label'		=> 'Description',
				'operators'	=> array('IS', 'IS NOT', 'CONTAINS', 'DOES NOT CONTAIN', 'BEGINS WITH'),
				'type'		=> 'db_column',
				'column'	=> 'description',
				'accept'	=> 'text'
			),
			'gen_assigned_to' => array(
				'label'		=> 'Assigned To User',
				'operators'	=> array('IS', 'IS NOT'),
				'type'		=> 'db_column',
				'column'	=> 'assigned_to',
				'accept'	=> 'select',
				'options'	=> $user_options
			),
			'gen_owner' => array(
				'label'		=> 'Created By User',
				'operators'	=> array('IS', 'IS NOT'),
				'type'		=> 'db_column',
				'column'	=> 'owner',
				'accept'	=> 'select',
				'options'	=> $user_options
			),
			'gen_created' => array(
				'label'		=> 'Creation Time',
				'operators'	=> array('BEFORE', 'AFTER'),
				'type'		=> 'db_column',
				'column'	=> 'created',
				'accept'	=> 'date'
			),
			'gen_lastupdated' => array(
				'label'		=> 'Last Updated Time',
				'operators'	=> array('BEFORE', 'AFTER'),
				'type'		=> 'db_column',
				'column'	=> 'lastupdated',
				'accept'	=> 'date'
			),
			'org_status' => array(
				'label'		=> 'Organisation Status',
				'operators'	=> array('IS', 'IS NOT'),
				'type'		=> 'db_column',
				'column'	=> 'status_id',
				'accept'	=> 'select',
				'options'	=> $db->getAssoc("SELECT id, name FROM company_statuses WHERE usercompanyid = " . $db->qstr(EGS::getCompanyId()) . " ORDER BY name")
			),
			'org_source' => array(
				'label'		=> 'Organisation Source',
				'operators'	=> array('IS', 'IS NOT'),
				'type'		=> 'db_column',
				'name'		=> 'source_id',
				'accept'	=> 'select',
				'options'	=> $db->getAssoc("SELECT id, name FROM company_sources WHERE usercompanyid = " . $db->qstr(EGS::getCompanyId()) . " ORDER BY name")
			),
			'org_classification' => array(
				'label'		=> 'Organisation Classification',
				'operators'	=> array('IS', 'IS NOT'),
				'type'		=> 'db_column',
				'column'	=> 'classification_id',
				'accept'	=> 'select',
				'options'	=> $db->getAssoc("SELECT id, name FROM company_classifications WHERE usercompanyid = " . $db->qstr(EGS::getCompanyId()) . " ORDER BY name")
			),
			'org_rating' => array(
				'label'		=> 'Organisation Rating',
				'operators'	=> array('IS', 'IS NOT'),
				'type'		=> 'db_column',
				'column'	=> 'rating_id',
				'accept'	=> 'select',
				'options'	=> $db->getAssoc("SELECT id, name FROM company_ratings WHERE usercompanyid = " . $db->qstr(EGS::getCompanyId()) . " ORDER BY name")
			),
			'org_industry' => array(
				'label'		=> 'Organisation Industry',
				'operators'	=> array('IS', 'IS NOT'),
				'type'		=> 'db_column',
				'column'	=> 'industry_id',
				'accept'	=> 'select',
				'options'	=> $db->getAssoc("SELECT id, name FROM company_industries WHERE usercompanyid = " . $db->qstr(EGS::getCompanyId()) . " ORDER BY name")
			),
			'org_type' => array(
				'label'		=> 'Organisation Type',
				'operators'	=> array('IS', 'IS NOT'),
				'type'		=> 'db_column',
				'column'	=> 'type_id',
				'accept'	=> 'select',
				'options'	=> $db->getAssoc("SELECT id, name FROM company_types WHERE usercompanyid = " . $db->qstr(EGS::getCompanyId()) . " ORDER BY name")
			),
			'org_address' => array(
				'label'		=> 'Organisation Address',
				'operators'	=> array('IS', 'IS NOT', 'CONTAINS', 'DOES NOT CONTAIN', 'BEGINS WITH'),
				'type'		=> 'db_join',
				'column'	=> "array_to_string(ARRAY[oa.street1, oa.street2, oa.street3, oa.town, oa.county, oa.postcode, oac.name], ' ')",
				'accept'	=> 'text',
				'join'		=> array('organisation_addresses oa' => 'oa.organisation_id = org.id AND oa.main', 'countries oac' => 'oa.country_code = oac.code')
			),
			'per_can_call' => array(
				'label'		=> 'Can Call',
				'type'		=> 'db_column',
				'column'	=> 'can_call',
				'accept'	=> 'boolean'
			),
			'per_can_email' => array(
				'label'		=> 'Can Email',
				'type'		=> 'db_column',
				'column'	=> 'can_email',
				'accept'	=> 'boolean'
			),
			'per_address' => array(
				'label'		=> 'Person Address',
				'operators'	=> array('IS', 'IS NOT', 'CONTAINS', 'DOES NOT CONTAIN', 'BEGINS WITH'),
				'type'		=> 'db_join',
				'column'	=> "array_to_string(ARRAY[pa.street1, pa.street2, pa.street3, pa.town, pa.county, pa.postcode, pac.name], ' ')",
				'accept'	=> 'text',
				'join'		=> array('person_addresses pa' => 'pa.person_id = per.id AND pa.main', 'countries pac' => 'pa.country_code = pac.code')
			),
			'opp_enddate' => array(
				'label'		=> 'Opportunity Close Date',
				'operators'	=> array('BEFORE', 'AFTER'),
				'type'		=> 'db_column',
				'column'	=> 'enddate',
				'accept'	=> 'date'
			),
			'opp_cost' => array(
				'label'		=> 'Opportunity Value',
				'operators'	=> array('IS', 'IS NOT', 'GREATER THAN', 'LESS THAN'),
				'type'		=> 'db_column',
				'column'	=> 'cost',
				'accept'	=> 'numeric'
			),
			'opp_probability' => array(
				'label'		=> 'Opportunity Probability',
				'operators'	=> array('IS', 'IS NOT', 'GREATER THAN', 'LESS THAN'),
				'type'		=> 'db_column',
				'column'	=> 'probability',
				'accept'	=> 'numeric'
			),
			'opp_status' => array(
				'label'		=> 'Opportunity Sales Stage',
				'operators'	=> array('IS', 'IS NOT'),
				'type'		=> 'db_column',
				'column'	=> 'status_id',
				'accept'	=> 'select',
				'options'	=> $db->getAssoc("SELECT id, name FROM opportunitystatus WHERE usercompanyid = " . $db->qstr(EGS::getCompanyId()) . " ORDER BY name")
			),
			'opp_type' => array(
				'label'		=> 'Opportunity Type',
				'operators'	=> array('IS', 'IS NOT'),
				'type'		=> 'db_column',
				'column'	=> 'type_id',
				'accept'	=> 'select',
				'options'	=> $db->getAssoc("SELECT id, name FROM opportunitytype WHERE usercompanyid = " . $db->qstr(EGS::getCompanyId()) . " ORDER BY name")
			),
			'opp_source' => array(
				'label'		=> 'Opportunity Source',
				'operators'	=> array('IS', 'IS NOT'),
				'type'		=> 'db_column',
				'column'	=> 'source_id',
				'accept'	=> 'select',
				'options'	=> $db->getAssoc("SELECT id, name FROM opportunitysource WHERE usercompanyid = " . $db->qstr(EGS::getCompanyId()) . " ORDER BY name")
			),
			'act_type' => array(
				'label'		=> 'Activity Type',
				'operators'	=> array('IS', 'IS NOT'),
				'type'		=> 'db_column',
				'column'	=> 'type_id',
				'accept'	=> 'select',
				'options'	=> $db->getAssoc("SELECT id, name FROM activitytype WHERE usercompanyid = " . $db->qstr(EGS::getCompanyId()) . " ORDER BY name")
			)
		);
		
		// Append custom fields to relevant sections
		$cfields = new CustomfieldCollection();
		$cfields_sh = new SearchHandler($cfields, false);
		$cfields_sh->extract(true);
		$cfields_sh->perpage = 0;
		$cfields_sh->setOrderBy('name');
		$cfields->load($cfields_sh);
		foreach ($cfields as $field) {
			$cfield_data = array();
			foreach ($types as $short => $long) {
				if ($field->$long !== 'f') {
					$cfield_data = array(
						'label'				=> $field->name,
						'type'				=> 'custom_field',
						'field_id'			=> $field->id,
						'custom_field_type'	=> $field->type
					);
					switch ($field->type) {
						case 'c':
							$cfield_data['accept'] = 'boolean';
							break;
						case 'n':
							$cfield_data['accept'] = 'numeric';
							$cfield_data['operators'] = array('IS', 'IS NOT', 'GREATER THAN', 'LESS THAN');
							break;
						case 's':
							$cfield_data['accept'] = 'select';
							$cfield_data['operators'] = array('IS', 'IS NOT');
							$cfield_data['options'] = $db->getAssoc("SELECT id, value FROM custom_field_options WHERE field_id = " . $db->qstr($field->id) . " ORDER BY value");
							break;
						default:
							$cfield_data['accept'] = 'text';
							$cfield_data['operators'] = array('IS', 'IS NOT', 'CONTAINS', 'DOES NOT CONTAIN', 'BEGINS WITH');
					}
					$qb_fields[$short.'_'.$field->id] = $cfield_data;
				}
			}
		}
		
		$this->view->set('qb_fields', $qb_fields);
		
		// Was this a form submission?
		if (!empty($this->_data['r'])) {
			$r = $this->_data['r'];
			$tablePrefix = $r;
			
			switch ($r) {
				case 'act':
					$collection = new Tactile_ActivityCollection();
					break;
				case 'opp':
					$collection = new Tactile_OpportunityCollection();
					break;
				case 'per':
					$collection = new Omelette_PersonCollection();
					break;
				case 'org':
				default:
					$r = 'org';
					$collection = new Omelette_OrganisationCollection();
			}
			$this->view->set('r', $r);
			
			$sh = new SearchHandler($collection, false);
			$sh->extractOrdering();
			$sh->extractPaging();
			$this->_handleSearchFields($sh, $qb_fields, $tablePrefix);
			Controller::index($collection, $sh);
			$this->view->set('collection', $collection);
			$this->view->set('current_query', http_build_query(array('r'=>$r,'q'=>$this->_data['q'])));
		}
	}
	
	private function _handleSearchFields(SearchHandler $sh, $qb_fields, $tablePrefix = null) {
		$q =  !empty($this->_data['q']) ? $this->_data['q'] : array();
		$r =  !empty($this->_data['r']) ? $this->_data['r'] : 'org';
		$this->view->set('q', $q);
		
		$ops = array(
			'IS'				=> '=',
			'IS NOT'			=> '!=',
			'BEGINS WITH'		=> 'ILIKE',
			'CONTAINS'			=> 'ILIKE',
			'DOES NOT CONTAIN'	=> 'NOT ILIKE',
			'BEFORE'			=> '<',
			'AFTER'				=> '>',
			'GREATER THAN'		=> '>',
			'LESS THAN'			=> '<'
		);
		
		$custom_field_ids = array();
		$db_joins = array();
		foreach ($q as $fname => $fdata) {
			if (empty($qb_fields[$fname])) {
				Flash::Instance()->addError('Unrecognised filter: ' . $fname);
				continue;
			}
			$f = $qb_fields[$fname];
			$cc = new ConstraintChain();
			
			$value = !empty($fdata['value']) ? $fdata['value'] : '';
			
			if (empty($fdata['op'])) {
				$fdata['op'] = 'IS';
			}
			if (!empty($f['operators']) && !in_array($fdata['op'], $f['operators'])) {
				$fdata['op'] = $f['operators'][0];
			}
			$op = $fdata['op'];
			switch ($op) {
				case 'CONTAINS':
				case 'DOES NOT CONTAIN':
					$value = "%$value%";
					break;
				case 'BEGINS WITH':
					$value = "$value%";
					break;
				case 'BEFORE':
				case 'AFTER':
					// Format time and check validity
					if ($value === '') {
						continue 2;
					}
					if (!preg_match('/(\d+)\/(\d+)\/(\d+)/', $value, $m)) {
						// Invalid time format
						Flash::Instance()->addError('Unrecognised date format: ' . $value);
						continue 2;
					}
					$format = EGS::getDateFormat();
					if ($format === 'm/d/Y') {
						// US format
						$month = $m[1];
						$day = $m[2];
					} else {
						$month = $m[2];
						$day = $m[1];
					}
					$year = $m[3];
					// Format for DB
					$value = sprintf('%d-%02d-%02d', $year, $month, $day);
					if (!strtotime($value)) {
						Flash::Instance()->addError('Invalid date: ' . $value);
						continue 2;
					}
					break;
			}
			
			switch ($f['type']) {
				case 'db_column':
					if ($r === 'per' && $f['column'] === 'name') {
						$f['column'] = "firstname || ' ' || per.surname"; // Fudge for searching for people by name
					}
					$cc->add(new Constraint((!is_null($tablePrefix) ? ($tablePrefix . '.') : '') . $f['column'], $ops[$op], $value));
					if ($op === 'IS NOT') {
						$cc->add(new Constraint((!is_null($tablePrefix) ? ($tablePrefix . '.') : '') . $f['column'], 'IS', 'NULL'), 'OR');
					}
					$sh->addConstraintChain($cc);
					break;
				case 'db_join':
					$cc->add(new Constraint($f['column'], $ops[$op], $value));
					$sh->addConstraintChain($cc);
					$db_joins[$f['column']] = $f['join'];
					break;
				case 'custom_field':
					// Oh boy, here we go
					$custom_field_ids[$f['field_id']] = $f['custom_field_type'];
					switch ($f['custom_field_type']) {
						case 'n':
							$field = 'cfm' . $f['field_id'] . '.value_numeric';
							$value = (float)$value;
							break;
						case 'c':
							$field = 'cfm' . $f['field_id'] . '.enabled';
							$value = $value === 'FALSE' ? $value : 'TRUE';
							break;
						case 's':
							$field = 'cfm' . $f['field_id'] . '.option';
							$value = (int)$value;
							break;
						case 't':
						default:
							$field = 'cfm' . $f['field_id'] . '.value';
					}
					
					$cc->add(new Constraint($field, $ops[$op], $value));
					$field_cc = new ConstraintChain();
					$field_cc->add(new Constraint("cf{$f['field_id']}.id", '=', $f['field_id']));
					if ($op === 'IS NOT') {
						// Allow negation to mean "records with values that do not match, AND records with NULL values"
						$cc->add(new Constraint($field, 'IS', 'NULL'), 'OR');
						$field_cc->add(new Constraint("cf{$f['field_id']}.id", 'IS', 'NULL'), 'OR');
					}
					$sh->addConstraintChain($cc);
					$sh->addConstraint($field_cc);
					break;
			}
		}
		$sh->setCustomFields($custom_field_ids);
		$sh->setDbJoins($db_joins);
	}
	
	public function save() {
		$name = !empty($this->_data['name']) ? trim($this->_data['name']) : '';
		$form = !empty($this->_data['form']) ? urldecode($this->_data['form']) : '';
		parse_str($form);
		
		$errors = array();
		
		switch ($r) {
			case 'org':
			case 'per':
			case 'opp':
			case 'act':
				break;
			default:
				$errors[] = 'Failed to determine the record type';
		}
		$query = array('q' => $q);
		$search_data = array(
			'name'			=> $name,	
			'record_type'	=> $r,
			'query'			=> urldecode(http_build_query($query))
		);
		if (FALSE !== ($search = DataObject::Factory($search_data, $errors, 'AdvancedSearch'))) {
			if ($search->save()) {
				Flash::Instance()->addMessage('Search saved successfully');
				$this->view->set('model', $search);
			} else {
				Flash::Instance()->addError('Failed to save Search');
			}
		} else {
			Flash::Instance()->addErrors($errors);
		}
		sendTo('search/advanced');
	}
	
	public function delete() {
		$search = new AdvancedSearch();
		if ($search->load($this->_data['id'])) {
			if ($search->canDelete() && $search->delete()) {
				Flash::Instance()->addMessage('Search deleted');
			} else {
				Flash::Instance()->addMessage('Failed to delete the Search');
			}
		} else {
			Flash::Instance()->addError('Failed to load Search');
		}
		sendTo('search/advanced');
	}
	
	public function recall() {
		$id = !empty($this->_data['id']) ? $this->_data['id'] : '';
		$search = new AdvancedSearch();
		if (!$search->load($id)) {
			Flash::Instance()->addError('Failed to load Search');
			sendTo('search/advanced');
			return;
		}
		parse_str($search->query);
		$http_query = array('r' => $search->record_type, 'q' => $q);
		$url = '/search/advanced?' . http_build_query($http_query);
		
		header("Location: $url");
	}
	
}
