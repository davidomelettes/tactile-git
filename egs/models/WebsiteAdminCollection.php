<?php
class WebsiteAdminCollection extends DataObjectCollection {

		public $field;

		function __construct() {
			parent::__construct('WebsiteAdmin');
			$this->_tablename="website_admins";
		}



}
?>
