<?php
class GLAnalysis extends DataObject {
	protected $defaultDisplayFields=array('analysis'=>'Analysis','description'=>'Description','summary'=>'Summary');
	function __construct() {
		parent::__construct('glanalysis');
		$this->idField='id';
		$this->identifierField='analysis || \'-\' || description';
		$this->orderby='analysis';
		
 		$this->belongsTo('GLSummary', 'glsummary_id', 'summary'); 
		$this->validateUniquenessOf('analysis');
	}


}
?>
