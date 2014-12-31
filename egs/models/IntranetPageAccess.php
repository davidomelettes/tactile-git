<?php
class IntranetPageAccess extends DataObject {

	function __construct() {
		parent::__construct('intranet_page_access');
		$this->idField='id';
		
		
 		$this->belongsTo('IntranetPage', 'intranetpage_id', 'page');
 		$this->belongsTo('Role', 'role_id', 'role'); 

	}


}
?>
