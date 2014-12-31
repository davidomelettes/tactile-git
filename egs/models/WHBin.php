<?php
class WHBin extends DataObject {

	function __construct() {
		parent::__construct('wh_bins');
		$this->idField='id';
		$this->identifierField="bin_code ||'-'|| description";		
		
 		$this->validateUniquenessOf('bin_code');
 		$this->belongsTo('WHLocation', 'whlocation_id', 'whlocation'); 

	}


}
?>
