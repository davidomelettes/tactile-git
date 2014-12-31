<?php
class CurrencyRate extends DataObject {

	function __construct() {
		parent::__construct('curate');
		$this->idField='id';
		
		
 		$this->validateUniquenessOf(array('date', 'rate'));
 		$this->belongsTo('Currency', 'currency_id', 'currency'); 

	}


}
?>
