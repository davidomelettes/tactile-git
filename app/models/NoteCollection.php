<?php

class NoteCollection extends DataObjectCollection {
	
	public function __construct($note='Note') {
		parent::__construct($note);
		$this->_tablename='notes_overview';
	}
	
	public function load(SearchHandler $sh) {
		$db = DB::Instance();
		
		$query = new QueryBuilder($db);
		$fields = array('n.id', 'n.title', 'n.note', 'n.owner', 'n.alteredby', 'n.created',
			'n.lastupdated', 'n.private', 'n.deleted',
			'n.organisation_id', 'n.person_id', 'n.opportunity_id', 'n.activity_id',
			'n.organisation', 'n.person', 'n.opportunity', 'n.activity');
		
		$sh->addConstraint(new Constraint('n.usercompanyid', '=', EGS::getCompanyId()));
		$sh->addConstraint(new Constraint('n.deleted', '=', FALSE));
		
		// Module Admins can see everything
		if (!isModuleAdmin()) {
			// Select only the notes we have permission to see
			$cc = new ConstraintChain();
			// If this note is private, show only if we are the owner
			$cc_private = new ConstraintChain();
			$cc_private->add(new Constraint('n.private', '=', 'TRUE'));
			$cc_private->add(new Constraint('n.owner', '=', EGS::getUsername()), 'AND');
			$cc->add($cc_private);
			
			// If this note is public, show only if we have the relevant role
			$cc_public = new ConstraintChain();
			$cc_public->add(new Constraint('n.private', '=', 'FALSE'));
			
			$cc_public2 = new ConstraintChain();
			$cc_public2->add(new Constraint('hr.username', '=', EGS::getUsername()));
			$cc_public2->add(new Constraint('n.organisation_id', 'IS', 'NULL'), 'OR');
			$cc_public->add($cc_public2);
			$cc->add($cc_public, 'OR');
			
			$sh->addConstraintChain($cc);
		}
		$query->select_simple($fields, true)
			->from('notes_overview n')
			->left_join('organisations org', 'org.id=n.organisation_id');
		if (!isModuleAdmin()) {
			$query->left_join('organisation_roles cr', 'org.id=cr.organisation_id AND cr.read')
				->left_join('hasrole hr', 'cr.roleid=hr.roleid');
		}
		$query->where($sh->constraints)
			->orderby('n.lastupdated', 'desc')
			->limit($sh->perpage, $sh->offset);
			
		$this->query = $query->__toString();
		parent::_load($query->__toString(), $query, $query->countQuery('n.id'));
		
		$this->num_pages=ceil($this->num_records/max(1,$sh->perpage));
		$this->cur_page=$sh->page;
		$this->per_page = $sh->perpage;
	}
	
}
