<?php
class CBRelation extends DataObject {

	function __construct() {
		parent::__construct('cb_relations');
		$this->idField='id';
		
		
 		$this->belongsTo('Company', 'company_id', 'company');
 		$this->belongsTo('Sypaytype', 'payment_method_id', 'payment');
 		$this->belongsTo('Cumaster', 'currency_id', 'currency'); 

	}


}
?>
