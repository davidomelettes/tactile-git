<?php

class EmailCollection extends DataObjectCollection {

	public function __construct($note='Email') {
		parent::__construct($note);
		$this->_tablename='emails_overview';
	}
	
	public function load(SearchHandler $sh) {
		$db = DB::Instance();
		
		$query = new QueryBuilder($db);
		
		$fields = array('e.id', 'e.email_from', 'e.email_to', 'e.subject', 'e.body', 'e.received',
			'e.created', 'e.organisation_id', 'e.person_id', 'e.opportunity_id',
			'e.owner', 'e.organisation', 'e.person', 'e.opportunity', 'e.direction');
		
		$sh->addConstraint(new Constraint('e.usercompanyid', '=', EGS::getCompanyId()));
		
		// Module Admins can see everything
		if (!isModuleAdmin()) {
			// Select only people whom we have permission to see
			$cc = new ConstraintChain();
			// If this person belongs to a company,
			// permission is granted if there is a relevant entry in the hasroles table
			$cc->add(new Constraint('hr.username', '=', EGS::getUsername()), 'OR');
			// If not in a company,
			// permission is granted if we are the owner or assignee
			$cc->add(new Constraint('e.owner', '=', EGS::getUsername()), 'OR');
			// If we are not the owner,
			// permission is granted if the person is not marked as private
			$cc_private = new ConstraintChain();
			$cc_private->add(new Constraint('e.organisation_id', 'IS', 'NULL'), 'AND');
			$cc->add($cc_private, 'OR');
			
			$sh->addConstraintChain($cc);
		}
		$query->select_simple($fields, true)
			->from('emails_overview e')
			->left_join('organisations org', 'org.id=e.organisation_id');
		if (!isModuleAdmin()) {
			$query->left_join('organisation_roles cr', 'org.id=cr.organisation_id AND cr.read')
			->left_join('hasrole hr', 'cr.roleid=hr.roleid');
		}
		$query->where($sh->constraints)
			->orderby('e.received', 'desc')
			->limit($sh->perpage, $sh->offset);
			
		$this->query = $query->__toString();
		parent::_load($query->__toString(), $query, $query->countQuery('e.id'));
		
		$this->num_pages=ceil($this->num_records/max(1,$sh->perpage));
		$this->cur_page=$sh->page;
		$this->per_page = $sh->perpage;
	}
	
}
