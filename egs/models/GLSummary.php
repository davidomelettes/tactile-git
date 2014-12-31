<?php
class GLSummary extends DataObject {
	
	function __construct() {
		parent::__construct('glsummary');
		$this->idField='id';
		$this->identifierField='summary';
		$this->orderby='summary';
		$this->validateUniquenessOf('summary');
	}

	public function getIdentifier()
	{
		return 'summary || \' - \' || description';
	}


}
?>
