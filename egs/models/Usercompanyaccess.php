<?php
class Usercompanyaccess extends DataObject {

	protected $defaultDisplayFields = array('username'=>'Username','organisation'=>'Organisation','enabled'=>'Enabled');

	function __construct() {
		parent::__construct('user_company_access');
		$this->idField='id';
		
		
 		$this->validateUniquenessOf(array('username', 'organisation_id'));
 		$this->belongsTo('User', 'username', 'user');
 		$this->belongsTo('Organisation', 'organisation_id', 'organisation'); 

	}


}
?>
