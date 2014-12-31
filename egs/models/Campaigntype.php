<?php
class Campaigntype extends DataObject {

	function __construct() {
		parent::__construct('campaigntype');
		$this->idField='id';
		$this->orderby='position';
	}


}
?>
