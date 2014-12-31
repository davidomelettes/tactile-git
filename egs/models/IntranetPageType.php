<?php
class IntranetPageType extends DataObject {

	function __construct() {
		parent::__construct('intranet_page_types');
		$this->idField='id';
		
		
 		$this->belongsTo('IntranetLayout', 'layout_id', 'layout'); 

	}


}
?>
