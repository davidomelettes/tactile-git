<?php
class Activity extends DataObject {
	protected $defaultDisplayFields=array('name'=>'Name','opportunity'=>'Attached to','company'=>'Company','person'=>'Person','startdate'=>'Start Date','enddate'=>'End Date');
	function __construct() {
		parent::__construct('activities');
		$this->idField='id';
		$this->orderby = 'startdate';
		$this->orderdir = 'desc';
		
		$this->belongsTo('Activitytype', 'type_id', 'type');
		$this->belongsTo('User', 'owner', 'activity_owner');
		$this->belongsTo('User','assigned','activity_assigned');
		$this->belongsTo('User', 'alteredby', 'activity_alteredby');
		$this->belongsTo('Opportunity', 'opportunity_id', 'opportunity');
		$this->belongsTo('Campaign', 'campaign_id', 'campaign');
		$this->belongsTo('Company', 'company_id', 'company');
		$this->belongsTo('Person', 'person_id', 'person');

	}


}
?>
