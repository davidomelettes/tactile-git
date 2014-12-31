<?php
class WebsitefileCollection extends DataObjectCollection {

		public $field;

		function __construct() {
			parent::__construct('Websitefile');
			$this->_tablename="website_filesoverview";
		}



}
?>
