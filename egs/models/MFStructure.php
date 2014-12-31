<?php
class MFStructure extends DataObject {

	function __construct() {
		parent::__construct('mf_structures');
		$this->idField='id';
		
		
 		$this->validateUniquenessOf(array('stitem_id', 'line_no'));
 		$this->belongsTo('STItem', 'stitem_id', 'stitem');
 		$this->belongsTo('STItem', 'ststructure_id', 'ststructure'); 

	}


}
?>
