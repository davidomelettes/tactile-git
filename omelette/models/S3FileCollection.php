<?php

class S3FileCollection extends DataObjectCollection {

	function __construct($do='S3File') {
		parent::__construct($do);
		$this->_tablename='s3_files_overview';
	
	}
	
	public function load(SearchHandler $sh) {
		$db = DB::Instance();
		
		$query = new QueryBuilder($db);
		
		$fields = array('f.id', 'f.bucket', 'f.object', 'f.filename', 'f.content_type', 'f.size',
			'f.extension', 'f.comment', 'f.owner', 'f.created',
			'f.organisation_id', 'f.person_id', 'f.opportunity_id', 'f.activity_id', 'f.email_id', 'f.changeset_id',
			'f.organisation', 'f.person', 'f.opportunity', 'f.activity', 'f.email');
		
		$sh->addConstraint(new Constraint('f.usercompanyid', '=', EGS::getCompanyId()));
		
		// Module Admins can see everything
		if (!isModuleAdmin()) {
			// Select only people whom we have permission to see
			$cc = new ConstraintChain();
			// If this person belongs to a company,
			// permission is granted if there is a relevant entry in the hasroles table
			$cc->add(new Constraint('hr.username', '=', EGS::getUsername()), 'OR');
			// If not in a company,
			// permission is granted if we are the owner or assignee
			$cc->add(new Constraint('f.owner', '=', EGS::getUsername()), 'OR');
			// If we are not the owner,
			// permission is granted if the person is not marked as private
			$cc_private = new ConstraintChain();
			$cc_private->add(new Constraint('f.organisation_id', 'IS', 'NULL'), 'AND');
			$cc->add($cc_private, 'OR');
			
			$sh->addConstraintChain($cc);
		}
		$query->select_simple($fields, true)
			->from('s3_files_overview f')
			->left_join('organisations org', 'org.id=f.organisation_id');
		if (!isModuleAdmin()) {
			$query->left_join('organisation_roles cr', 'org.id=cr.organisation_id AND cr.read')
			->left_join('hasrole hr', 'cr.roleid=hr.roleid');
		}
		$query->where($sh->constraints)
			->orderby('f.created', 'desc')
			->limit($sh->perpage, $sh->offset);
			
		$this->query = $query->__toString();
		parent::_load($query->__toString(), $query, $query->countQuery('f.id'));
		
		$this->num_pages=ceil($this->num_records/max(1,$sh->perpage));
		$this->cur_page=$sh->page;
		$this->per_page = $sh->perpage;
	}
	
}
