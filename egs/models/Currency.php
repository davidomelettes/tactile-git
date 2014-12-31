<?php
class Currency extends DataObject {
	protected $defaultDisplayFields=array('currency'=>'Currency','description'=>'Description','symbol'=>'Symbol',
	'decdesc'=>'decdesc','rate'=>'rate','writeoff'=>'Writeoff','revalue'=>'revalue','glcentre'=>'center','datectrl'=>'date','method'=>'method');
	function __construct() {
		parent::__construct('cumaster');
		$this->idField='id';
		
		$this->identifierField='currency';
		$this->orderby='currency';
 		$this->validateUniquenessOf('currency');
 		$this->belongsTo('GLAccount', 'writeoff_glmaster_id', 'writeoff');
 		$this->belongsTo('GLAccount', 'revalue_glmaster_id', 'revalue');
 		$this->belongsTo('GLCentre', 'glcentre_id', 'glcentre'); 
		$this->setEnum('method',array('D'=>'Divide','M'=>'Multiply'));
	}


}
?>
