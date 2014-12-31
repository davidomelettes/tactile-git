<?php
class Campaign extends DataObject {
	protected $defaultDisplayFields=array('name'=>'Name','campaign_type'=>'Type','campaign_status'=>'Status', 'startdate'=>'SDate','enddate'=>'EDate');
	function __construct() {
		parent::__construct('campaigns');
		$this->idField='id';
		
		$this->view='';
		
 		$this->validateUniquenessOf('id');
 		$this->belongsTo('Campaigntype', 'campaign_type_id', 'campaign_type');
 		$this->belongsTo('Campaignstatus', 'campaign_status_id', 'campaign_status');

	}


}
?>
