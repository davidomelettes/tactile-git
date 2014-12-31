<?php
class MFWorkorder extends DataObject {

	function __construct() {
		parent::__construct('mf_workorders');
		$this->idField='id';
		
		
 		$this->validateUniquenessOf('work_order_no');
 		$this->belongsTo('STItem', 'stitem_id', 'stitem'); 

	}


}
?>
