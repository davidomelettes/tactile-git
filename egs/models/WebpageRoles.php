<?php
class WebpageRoles extends DataObject {

	function __construct() {
		parent::__construct('webpageroles');
		$this->idField='id';
		
		$this->view='';
		
	}


}
?>
