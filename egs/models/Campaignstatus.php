<?php
class Campaignstatus extends DataObject {

	function __construct() {
		parent::__construct('campaignstatus');
		$this->idField='id';

		$this->view='';
		$this->orderby='position';
 		$this->validateUniquenessOf('id');
 		$this->belongsTo('Company', 'companyid', 'companyid');

	}


}
?>
