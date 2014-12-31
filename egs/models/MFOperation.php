<?php
class MFOperation extends DataObject {

	function __construct() {
		parent::__construct('mf_operations');
		$this->idField='id';
		
		
 		$this->validateUniquenessOf(array('stitem_id', 'op_no'));
 		$this->belongsTo('STItem', 'stitem_id', 'stitem');
 		$this->belongsTo('MFCentre', 'mfcentre_id', 'mfcentre');
 		$this->belongsTo('MFResource', 'mfresource_id', 'mfresource'); 

	}


}
?>
