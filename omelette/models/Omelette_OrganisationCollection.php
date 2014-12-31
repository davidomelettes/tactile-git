<?php

class Omelette_OrganisationCollection extends DataObjectCollection {
	
	function __construct($do = 'Organisation') {
		parent::__construct($do);
			
		$this->identifier='name';
		$this->identifierField='name';
	}
	
	/**
	 * @param SearchHandler $sh
	 * @param string $c_query
	 */
	public function load($sh, $c_query = null) {
		$query = $this->getLoadQuery($sh);
		$this->_load($query->__toString(), $query, $query->countQuery('org.id'));
		
		$db = DB::Instance();
		$address_fields = array('street1', 'street2', 'street3', 'town', 'county', 'postcode', 'country_code');
		foreach ($this as $org) {
			// Use setAdditional() to create the associated DataFields, so __get() doesn't attempt to load an alias instead
			$org->setAdditional('phone');
			$org->phone = '';
			$org->setAdditional('email');
			$org->email = '';
			$org->setAdditional('website');
			$org->website = '';
			$org->setAdditional('fax');
			$org->fax = '';
			foreach ($address_fields as $field) {
				$org->setAdditional($field);
				$org->{$field} = '';
			}
		}
		
		$ids = $this->pluck('id');
		if (count($ids) > 0) {
			// Fetch contact methods
			$sql = "SELECT * FROM organisation_contact_methods WHERE main AND organisation_id IN (" . implode(', ', $ids) . ")";
			$contact_methods = $db->getArray($sql);
			if (!empty($contact_methods) && is_array($contact_methods)) {
				foreach ($contact_methods as $cm) {
					$org = $this->getById($cm['organisation_id']);
					switch ($cm['type']) {
						case 'T':
							$org->phone = $cm['contact'];
							break;
						case 'E':
							$org->email = $cm['contact'];
							break;
						case 'W':
							$org->website = $cm['contact'];
							break;
						case 'F':
							$org->fax = $cm['contact'];
							break;
					}
				}
			}
			
			// Fetch addresses
			$sql = "SELECT * FROM organisation_addresses WHERE main AND organisation_id IN (" . implode(',', $ids) . ")";
			$addresses = $db->getArray($sql);
			if (!empty($addresses) && is_array($addresses)) {
				foreach ($addresses as $adr) {
					$org = $this->getById($adr['organisation_id']);
					foreach ($address_fields as $field) {
						$org->{$field} = $adr[$field];
					}
				}
			}
		}
		
		$this->num_pages = ceil($this->num_records / max(1, $sh->perpage));
		$this->cur_page = $sh->page;
		$this->per_page = $sh->perpage;
	}
	
	public function getLoadQuery($sh) {
		$db = DB::Instance();
		$query = new QueryBuilder($db, $this->_templateobject);
		
		$fields = array(
			'org.id', 'org.name', 'org.description', 'org.accountnumber',
			'org.created', 'org.lastupdated', 'org.owner', 'org.assigned_to', 'org.parent_id',
			'org.status_id', 'org.source_id','org.classification_id','org.rating_id', 'org.industry_id', 'org.type_id', 'org.parent_id'
		);
		
		if ($sh->constraints->findByFieldname('town', true) || $sh->constraints->findByFieldname('county', true) || $sh->constraints->findByFieldname('country_code', true)) {
			$fields = array_merge($fields, array('a.street1', 'a.street2', 'a.street3', 'a.town', 'a.county', 'a.postcode', 'a.country_code'));
			$query->left_join('organisation_addresses a', 'a.organisation_id = org.id');
		}
		
		if ($sh->constraints->findByFieldname('p.contact', true) || $sh->constraints->findByFieldname('e.contact', true)) {
			$fields = array_merge($fields, array('phone' => 'p.contact', 'fax' => 'f.contact', 'email' => 'e.contact', 'website' => 'w.contact'));
			$query->left_join('organisation_contact_methods p', "p.organisation_id = org.id AND p.type = 'T' AND p.main");
			$query->left_join('organisation_contact_methods f', "f.organisation_id = org.id AND f.type = 'F' AND f.main");
			$query->left_join('organisation_contact_methods e', "e.organisation_id = org.id AND e.type = 'E' AND e.main");
			$query->left_join('organisation_contact_methods w', "w.organisation_id = org.id AND w.type = 'W' AND w.main");
		}
		
		$customFields = $sh->getCustomFields();
		if (!empty($customFields)) {
			foreach ($customFields as $field_id => $field_type) {
				$query->left_join("custom_field_map cfm{$field_id}", "cfm{$field_id}.organisation_id = org.id");
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
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('org.usercompanyid','=',EGS::getCompanyId()));
		if(!isModuleAdmin()) {
			$cc->add(new Constraint('hr.username','=',EGS::getUsername()));
		}
		
		$sh->addConstraintChain($cc);
		
		$query->select_simple($fields, true)
			->from('organisations org');
		if(!isModuleAdmin()) {
			$query->left_join('organisation_roles cr','org.id=cr.organisation_id AND cr.read')
				->left_join('hasrole hr','cr.roleid=hr.roleid');
			$c = new Constraint('hr.username', '=', EGS::getUsername());
			$sh->addConstraint($c);
		}
		$query->where($sh->constraints)
			->limit($sh->perpage, $sh->offset);
		
		if ($sh->orderby == 'name') {
			$query->orderby('org.name', $sh->orderdir);
		} else {
			$query->orderby($sh->orderby, $sh->orderdir);
		}
		
		return $query;
	}
	
}
