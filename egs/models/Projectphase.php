<?php
class Projectphase extends DataObject {
	protected $defaultDisplayFields=array('name','position');
	function __construct() {
		parent::__construct('project_phases');
		$this->idField='id';
		
		$this->identifierField='name';
		
 		$this->validateUniquenessOf('id');
 		$this->belongsTo('Project', 'project_id', 'project');
 		$this->belongsTo('Project', 'project_id', 'project'); 

	}


}
?>
