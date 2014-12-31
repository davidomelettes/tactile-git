<?php
class GLOpening extends DataObject {

	function __construct() {
		parent::__construct('glopening');
		$this->idField='id';
		$this->identifierField='account';
		$this->orderby='account';
		
 		$this->validateUniquenessOf('year');
 		$this->validateUniquenessOf('account_id');
 		$this->validateUniquenessOf('centre_id');
 		$this->belongsTo('GLMaster', 'account_id', 'account');
 		$this->belongsTo('GLCentre', 'centre_id', 'centre'); 

	}


}
?>
