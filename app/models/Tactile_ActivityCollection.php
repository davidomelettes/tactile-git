<?php
/**
 *
 * @author gj
 */
class Tactile_ActivityCollection extends DataObjectCollection {
	
	public function __construct($activity=null) {
		if($activity==null) {
			$activity = DataObject::Construct('Activity');
		}
		parent::__construct($activity);
		$this->_tablename = 'tactile_activities_overview';
	}
	
	public function load(SearchHandler $sh) {
		$query = $this->getLoadQuery($sh);
		$this->query = $query->__toString();
		parent::_load($query->__toString(), $query, $query->countQuery('act.id'));
		
		$this->num_pages = ceil($this->num_records / max(1, $sh->perpage));
		$this->cur_page = $sh->page;
		$this->per_page = $sh->perpage;
	}
	
	public function getLoadQuery($sh) {
		$db = DB::Instance();
		
		$query = new QueryBuilder($db);
		
		$fields = array('act.id', 'act.name', 'act.description', 'act.location', 'act.class', 'act.type_id',
			'act.opportunity_id', 'act.organisation_id', 'act.person_id', 'act.date','act.time',
			'act.later', 'act.end_date', 'act.end_time', 'act.completed', 'act.assigned_to', 'act.assigned_by',
			'act.owner', 'act.alteredby', 'act.created', 'act.lastupdated', 'act.type',
			'act.organisation', 'act.person', 'act.opportunity', 'act.overdue', 'act.due');
		
		$sh->addConstraint(new Constraint('act.usercompanyid', '=', EGS::getCompanyId()));
		
		$customFields = $sh->getCustomFields();
		if (!empty($customFields)) {
			foreach ($customFields as $field_id => $field_type) {
				$query->left_join("custom_field_map cfm{$field_id}", "cfm{$field_id}.activity_id = act.id");
				$query->left_join("custom_fields cf{$field_id}", "cf{$field_id}.id = cfm{$field_id}.field_id");
				switch ($field_type) {
					case 's':
						$fields[] = "cfm{$field_id}.option AS cfm{$field_id}";
						break;
					case 'c':
						$fields[] = "cfm{$field_id}.enabled AS cfm{$field_id}";
						break;
					case 'n':
						$fields[] = "cfm{$field_id}.value_numeric AS cfm{$field_id}";
						break;
					default:
						$fields[] = "cfm{$field_id}.value AS cfm{$field_id}";
				}
			}
		}
		
		// Module Admins can see everything
		if (!isModuleAdmin()) {
			// Select only people whom we have permission to see
			$cc = new ConstraintChain();
			// If this person belongs to a company,
			// permission is granted if there is a relevant entry in the hasroles table
			$cc->add(new Constraint('hr.username', '=', EGS::getUsername()), 'OR');
			// If not in a company,
			// permission is granted if we are the owner or assignee
			$cc->add(new Constraint('act.owner', '=', EGS::getUsername()), 'OR');
			$cc->add(new Constraint('act.assigned_to', '=', EGS::getUsername()), 'OR');		
			// If we are not the owner,
			// permission is granted if the person is not marked as private
			$cc_private = new ConstraintChain();
			$cc_private->add(new Constraint('act.organisation_id', 'IS', 'NULL'), 'AND');
			$cc->add($cc_private, 'OR');
			
			$sh->addConstraintChain($cc);
		}
		$query->select_simple($fields, true)
			->from('tactile_activities_overview act')
			->left_join('organisations org', 'org.id=act.organisation_id');
		if (!isModuleAdmin()) {
			$query->left_join('organisation_roles cr', 'org.id=cr.organisation_id AND cr.read')
			->left_join('hasrole hr', 'cr.roleid=hr.roleid');
		}
		
		if ($sh->orderby == 'date') {
			$query->where($sh->constraints)
				->orderby('act.date', $sh->orderdir)
				->limit($sh->perpage, $sh->offset);
		} else if ($sh->orderby == 'assigned_to') {
			$query->where($sh->constraints)
				->orderby('act.assigned_to', $sh->orderdir)
				->limit($sh->perpage, $sh->offset);
		} else if ($sh->orderby == 'act.owner') {
			$query->where($sh->constraints)
				->orderby('act.owner', $sh->orderdir)
				->limit($sh->perpage, $sh->offset);
		}  else {
			$query->where($sh->constraints)
				->orderby($sh->orderby, $sh->orderdir)
				->limit($sh->perpage, $sh->offset);
		}
		
			
		return $query;
	}
	
}
