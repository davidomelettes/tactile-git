<?php
class IntranetSectionAccess extends DataObject {

	function __construct() {
		parent::__construct('intranet_section_access');
		$this->idField='id';
		
		$this->view='';
		
 		$this->belongsTo('Role', 'role_id', 'role');
 		$this->belongsTo('Section', 'section_id', 'section'); 

	}


}
?>
