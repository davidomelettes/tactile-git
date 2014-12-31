<?php
class Country extends DataObject {

	function __construct() {
		parent::__construct('countries');
		$this->idField='code';


	}


}
?>
