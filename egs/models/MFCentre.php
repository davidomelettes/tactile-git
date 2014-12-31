<?php
class MFCentre extends DataObject {

	function __construct() {
		parent::__construct('mf_centres');
		$this->idField='id';
		
		$this->identifierField='centre_code || \'- \' ||centre';
 		$this->validateUniquenessOf('centre_code');
 		$this->belongsTo('MFDept', 'mfdept_id', 'mfdept'); 

	}


}
?>
