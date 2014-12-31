<?php
class WHLocation extends DataObject {

	function __construct() {
		parent::__construct('wh_locations');
		$this->idField='id';
		$this->identifierField="location ||'-'|| description";		
		
 		$this->validateUniquenessOf('location');
 		$this->belongsTo('WHStore', 'whstore_id', 'whstore'); 

	}


}
?>
