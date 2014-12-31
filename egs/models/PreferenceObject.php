<?php
class PreferenceObject extends DataObject {

	public function __construct() {
		parent::__construct('userpreferences');
		$this->idField='id';
	}
	
}
?>