<?php

/**
 * Responsible for representing a collection of Tactile_Person objects
 * 
 * @author gj
 */
class Omelette_PersonCollection extends DataObjectCollection {

	function __construct($do = 'Person') {
		parent::__construct($do);
		
		$this->identifier='surname';
		$this->identifierField='fullname';
	}

	/**
	 * Overridden so as to use a custom query rather than the rather slow overview.
	 * Note the over-riding of the paging variable at the bottom, DOC gets confused with this at the moment.
	 *
	 * @param SearchHandler $sh
	 */
	public function load(SearchHandler $sh) {
		$query = $this->getLoadQuery($sh);
		//$this->query = $query->__toString();
		$this->_load($query->__toString(), $query, $query->countQuery('per.id'));
		
		$db = DB::Instance();
		$address_fields = array('street1', 'street2', 'street3', 'town', 'county', 'postcode', 'country_code');
		foreach ($this as $org) {
			// Use setAdditional() to create the associated DataFields, so __get() doesn't attempt to load an alias instead
			$org->setAdditional('phone');
			$org->phone = '';
			$org->setAdditional('email');
			$org->email = '';
			$org->setAdditional('mobile');
			$org->fax = '';
			foreach ($address_fields as $field) {
				$org->setAdditional($field);
				$org->{$field} = '';
			}
		}
		
		$ids = $this->pluck('id');
		if (count($ids) > 0) {
			// Fetch contact methods
			$sql = "SELECT * FROM person_contact_methods WHERE main AND person_id IN (" . implode(', ', $ids) . ")";
			$contact_methods = $db->getArray($sql);
			if (!empty($contact_methods) && is_array($contact_methods)) {
				foreach ($contact_methods as $cm) {
					$person = $this->getById($cm['person_id']);
					switch ($cm['type']) {
						case 'T':
							$person->phone = $cm['contact'];
							break;
						case 'E':
							$person->email = $cm['contact'];
							break;
						case 'M':
							$person->mobile = $cm['contact'];
							break;
					}
				}
			}
			
			// Fetch addresses
			$sql = "SELECT * FROM person_addresses WHERE main AND person_id IN (" . implode(',', $ids) . ")";
			$addresses = $db->getArray($sql);
			if (!empty($addresses) && is_array($addresses)) {
				foreach ($addresses as $adr) {
					$person = $this->getById($adr['person_id']);
					foreach ($address_fields as $field) {
						$person->{$field} = $adr[$field];
					}
				}
			}
		}
		
		$this->num_pages=ceil($this->num_records/max(1,$sh->perpage));
		$this->cur_page=$sh->page;
		$this->per_page = $sh->perpage;
	}
	
	public function getLoadQuery($sh) {
		$db = DB::Instance();
		
		$query = new QueryBuilder($db);
		
		$fields = array(
			'per.id', 'firstname', 'surname', 'per.description', 'per.title', 'per.suffix',
			'per.jobtitle', 'per.dob', 'reports_to', 'per.language_code', 'per.can_call', 'per.can_email',
			'per.created', 'per.lastupdated', 'per.owner', 'per.assigned_to',
			'organisation'=>'org.name', 'per.organisation_id'
		);
		
		if ($sh->constraints->findByFieldname('town', true) || $sh->constraints->findByFieldname('county', true) || $sh->constraints->findByFieldname('country_code', true)) {
			$fields = array_merge($fields, array('a.street1', 'a.street2', 'a.street3', 'a.town', 'a.county', 'a.postcode', 'a.country_code'));
			$query->left_join('person_addresses a', 'a.person_id = per.id');
		}
		
		if ($sh->constraints->findByFieldname('p.contact', true) || $sh->constraints->findByFieldname('e.contact', true)) {
			$fields = array_merge($fields, array('phone' => 'p.contact', 'fax' => 'f.contact', 'email' => 'e.contact', 'website' => 'w.contact'));
			$query->left_join('person_contact_methods p', "p.person_id = per.id AND p.type = 'T' AND p.main");
			$query->left_join('person_contact_methods f', "f.person_id = per.id AND f.type = 'F' AND f.main");
			$query->left_join('person_contact_methods e', "e.person_id = per.id AND e.type = 'E' AND e.main");
			$query->left_join('person_contact_methods w', "w.person_id = per.id AND w.type = 'W' AND w.main");
		}
		
		$customFields = $sh->getCustomFields();
		if (!empty($customFields)) {
			foreach ($customFields as $field_id => $field_type) {
				$query->left_join("custom_field_map cfm{$field_id}", "cfm{$field_id}.person_id = per.id");
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
		
		$dbJoins = $sh->getDbJoins();
		if (!empty($dbJoins)) {
			foreach ($dbJoins as $column => $join) {
				foreach ($join as $table => $condition) {
					$query->left_join($table, $condition);
				}
			}
		}
		
		$sh->addConstraint(new Constraint('per.usercompanyid', '=', EGS::getCompanyId()));
		
		$cc_users = new ConstraintChain();
		$cc_users->add(new Constraint('u.username', 'IS', 'NULL'));
		$cc_users->add(new Constraint('u.enabled', '=', 'TRUE'), 'OR');
		$sh->addConstraintChain($cc_users);
		
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
			$cc->add(new Constraint('per.owner', '=', EGS::getUsername()), 'OR');
			$cc->add(new Constraint('per.assigned_to', '=', EGS::getUsername()), 'OR');		
			// If we are not the owner,
			// permission is granted if the person is not marked as private
			$cc_private = new ConstraintChain();
			$cc_private->add(new Constraint('per.organisation_id', 'IS', 'NULL'), 'AND');
			$cc_private->add(new Constraint('per.private', '=', FALSE));
			$cc->add($cc_private, 'OR');
			
			$sh->addConstraintChain($cc);
		}
				
		$query->select_simple($fields, true)
			->from('people per')
			->left_join('organisations org', 'org.id=per.organisation_id')
			->left_join('users u', 'u.person_id=per.id');
		if (!isModuleAdmin()) {
			$query->left_join('organisation_roles cr', 'org.id=cr.organisation_id AND cr.read')
			->left_join('hasrole hr', 'cr.roleid=hr.roleid');
		}
		$query->where($sh->constraints)
			->orderby($sh->orderby, $sh->orderdir)
			->limit($sh->perpage, $sh->offset);
		
		return $query;
	}
	
}
