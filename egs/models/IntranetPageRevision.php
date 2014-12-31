<?php
class IntranetPageRevision extends DataObject {

	function __construct() {
		parent::__construct('intranet_page_revisions');
		$this->idField='id';
		
		
 		$this->belongsTo('IntranetPage', 'intranetpage_id', 'page'); 

	}


}
?>
