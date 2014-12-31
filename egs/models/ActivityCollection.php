<?php
class ActivityCollection extends DataObjectCollection {

		public $field;

		function __construct() {
			parent::__construct('Activity');
			$this->_tablename="activitiesoverview";
			$this->identifierField='name';
		}



}
?>
