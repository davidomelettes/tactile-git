<?php
class Opportunity extends DataObject {
	protected $defaultDisplayFields = array('name'=>'Name','company'=>'Company','person'=>'Person','status'=>'Status','cost'=>'Cost','enddate'=>'End','assigned'=>'Assigned To');
	function __construct() {
		
		parent::__construct('opportunities');
		$this->idField='id';

		$this->view='';
		$numbers=array();
		for($i=0;$i<=100;$i+=5)
			$numbers[$i]=$i;
		$this->setEnum('probability',$numbers);
 		$this->belongsTo('Opportunitystatus', 'status_id', 'status');
 		$this->belongsTo('Campaign', 'campaign_id', 'campaign');
 		$this->belongsTo('Organisation', 'organisation_id', 'organisation');
 		$this->belongsTo('Person', 'person_id', 'person');
 		$this->belongsTo('User', 'owner', 'opportunity_owner');
 		$this->belongsTo('User', 'assigned_to', 'opportunity_assigned');
 		$this->belongsTo('User', 'alteredby', 'opportunity_alteredby');
 		$this->belongsTo('Opportunitysource', 'source_id', 'source');
 		$this->belongsTo('Opportunitytype','type_id','type');
 		$this->hasMany('Activity','activities');
		$this->hasMany('OpportunityNote','notes');
		$this->identifierField='name';
		$this->orderby='name';
		$this->getField('cost')->setFormatter(new PriceFormatter());
	}
	
}
?>
