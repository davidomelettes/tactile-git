<?php
class BankAccount extends DataObject {

	function __construct() {
		parent::__construct('cbmaster');
		$this->idField='id';
		$this->identifierField='acctref';
		$this->orderby='acctref';
		
 		$this->validateUniquenessOf('acctref');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('GLMaster', 'account_id', 'account');
 		$this->belongsTo('GLCentre', 'centre_id', 'centre'); 

	}


}
?>
