<?php
class OpportunityNote extends DataObject {

	protected $defaultDisplayFields=array('title'=>'Title','note'=>'Note');

	function __construct() {
		parent::__construct('opportunity_notes');
		$this->idField='id';
		
		
 		$this->belongsTo('Opportunity', 'opportunity_id', 'opportunity');
	}


}
?>
