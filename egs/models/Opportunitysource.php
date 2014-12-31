<?php
class Opportunitysource extends DataObject {

	function __construct() {
		parent::__construct('opportunitysource');
		$this->idField='id';

		$this->orderby='position';
 		$this->validateUniquenessOf('id');
	}


}
?>
