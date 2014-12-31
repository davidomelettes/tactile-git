<?php
class GLMaster extends DataObject {

	function __construct() {
		parent::__construct('glmaster');
		$this->idField='id';
 		  $this->identifierField = 'account || \' - \' || description';
		
		 

	}


}
?>
