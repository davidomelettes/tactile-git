<?php

class GLCentre extends DataObject {
	public function __construct() {
		parent::__construct('glcentre');
		$this->identifierField = 'centre || \' - \' || description';	
		$this->orderby = 'centre';
		$this->validateUniquenessOf('centre');	
	}
}

?>
