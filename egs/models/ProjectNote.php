<?php
class ProjectNote extends DataObject {
	protected $defaultDisplayFields=array('title'=>'Title','note'=>'Note','owner'=>'Owner','alteredby'=>'Altered By','lastupdated'=>'Updated');
	function __construct() {
		parent::__construct('project_notes');
		$this->belongsTo('Company','company_id','company');
		$this->orderby='created';
		$this->orderdir='desc';
	}
}	
?>
