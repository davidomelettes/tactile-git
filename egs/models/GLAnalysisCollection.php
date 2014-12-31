<?php
class GLAnalysisCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('GLAnalysis');
			$this->_tablename="glanalysisoverview";
		}
	
		
		
}
?>
