<?php
class Contactcategory extends DataObject {

	function __construct() {
		parent::__construct('contact_categories');
		$this->idField='id';
	}


}
?>
