<?php
class Activitytype extends DataObject {

	function __construct() {
		parent::__construct('activitytype');
		$this->idField='id';

		$this->orderby='position';
 		$this->validateUniquenessOf('id');
 		$this->belongsTo('Company', 'companyid', 'companyid');

	}


}
?>
