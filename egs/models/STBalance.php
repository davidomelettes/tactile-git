<?php
class STBalance extends DataObject {

	function __construct() {
		parent::__construct('st_balances');
		$this->idField='id';
		
		
 		$this->belongsTo('WHStore', 'whstore_id', 'whstore');
 		$this->belongsTo('WHLocation', 'whlocation_id', 'whlocation');
 		$this->belongsTo('WHBin', 'whbin_id', 'whbin');
 		$this->belongsTo('STItem', 'stitem_id', 'stitem'); 

	}


}
?>
