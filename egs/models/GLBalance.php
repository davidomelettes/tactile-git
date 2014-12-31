<?php
class GLBalance extends DataObject {

	function __construct() {
		$this->defaultDisplayFields = array('account'=>'Account','centre'=>'Centre', 'periods'=>'Period', 'value'=>'Value');
		parent::__construct('glbalance');
		$this->idField='id';
		$this->identifierField='year';
		$this->orderby='year';
	

 		$this->belongsTo('GLMaster', 'account_id', 'account');
 		$this->belongsTo('GLCentre', 'centre_id', 'centre');
 		$this->belongsTo('Periods', 'periods_id', 'periods'); 
		
		
 		$this->validateUniquenessOf(array('periods_id','centre_id', 'account_id', 'year'));
	}


}
?>
