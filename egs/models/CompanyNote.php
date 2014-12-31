<?php
class CompanyNote extends DataObject {
	protected $defaultDisplayFields=array('title'=>'Title','note'=>'Note','company'=>'Company','owner'=>'Owner','alteredby'=>'Altered By','lastupdated'=>'Updated');
	function __construct() {
		parent::__construct('company_notes');
		$this->belongsTo('Company','company_id','company');
		$this->orderby='created';
		$this->orderdir='desc';
	}
}	
?>