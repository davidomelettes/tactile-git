<?php
/**
 *
 * @author de
 */
class Tactile_OpportunityCollection extends DataObjectCollection {
	
	public function __construct($opportunity=null) {
		if($opportunity==null) {
			$opportunity = DataObject::Construct('Opportunity');
		}
		parent::__construct($opportunity);
		$this->_tablename = 'opportunities_overview';
	}
	
	public function load(SearchHandler $sh) {
		$query = $this->getLoadQuery($sh);
		$this->query = $query->__toString();
		parent::_load($query->__toString(), $query, $query->countQuery('opp.id'));
		
		$this->num_pages = ceil($this->num_records / max(1, $sh->perpage));
		$this->cur_page = $sh->page;
		$this->per_page = $sh->perpage;
	}
	
	public function getLoadQuery($sh) {
		$db = DB::Instance();
		
		$query = new QueryBuilder($db);
		
		$fields = array('opp.id', 'opp.name', 'opp.description', 'opp.enddate', 'opp.cost', 'opp.probability',
			'opp.status_id', 'opp.type_id', 'opp.source_id', 'opp.archived','opp.status',
			'opp.owner', 'opp.assigned_to',
			'opp.organisation', 'opp.person', 'opp.organisation_id', 'opp.person_id', 'opp.lastupdated','opp.created','opp.alteredby','opp.archived');
		
		$sh->addConstraint(new Constraint(
				'opp.usercompanyid', 
				'=', 
				EGS::getCompanyId()));
		
		$customFields = $sh->getCustomFields();
		if (!empty($customFields)) {
			foreach ($customFields as $field_id => $field_type) {
				$query->left_join("custom_field_map cfm{$field_id}", "cfm{$field_id}.opportunity_id = opp.id");
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
			$cc->add(new Constraint(
					'hr.username', 
					'=', 
					EGS::getUsername()), 'OR');
			// If not in a company,
			// permission is granted if we are the owner or assignee
			$cc->add(new Constraint('opp.owner', '=', EGS::getUsername()), 'OR');
			$cc->add(new Constraint('opp.assigned_to', '=', EGS::getUsername()), 'OR');		
			// If we are not the owner,
			// permission is granted if the person is not marked as private
			$cc_private = new ConstraintChain();
			$cc_private->add(new Constraint('opp.organisation_id', 'IS', 'NULL'), 'AND');
			$cc->add($cc_private, 'OR');
			
			$sh->addConstraintChain($cc);
		}
		$query->select_simple($fields, true)
			->from('opportunities_overview opp')
			->left_join('organisations org', 'org.id=opp.organisation_id');
		if (!isModuleAdmin()) {
			$query->left_join('organisation_roles cr', 'org.id=cr.organisation_id AND cr.read')
			->left_join('hasrole hr', 'cr.roleid=hr.roleid');
		}
		$query->where($sh->constraints);

		if ($sh->orderby == 'name') {
			$query->orderby('opp.name', $sh->orderdir);
		} else {
			$query->orderby($sh->orderby, $sh->orderdir);
		}
		$query->limit($sh->perpage, $sh->offset);
		
		return $query;
	}
	
}
