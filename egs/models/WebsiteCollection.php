<?php
class WebsiteCollection extends DataObjectCollection {

		public $field;

		function __construct() {
			parent::__construct('Website');
			$this->_tablename="websitesoverview";
		}



}
?>
