<?php
class Tactile_Activitytype extends DataObject {

	function __construct() {
		parent::__construct('activitytype');
		$this->idField='id';

		$this->orderby='position';
 		$this->validateUniquenessOf('id');
 		$this->belongsTo('Company', 'companyid', 'companyid');
 		
		$this->getField('name')->setFormatter(new LinkingFormatter('/activities/by_type/?q=%s'));
	}


}
?>
