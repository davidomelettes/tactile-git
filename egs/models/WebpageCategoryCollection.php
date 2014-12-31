<?php
class WebpageCategoryCollection extends DataObjectCollection {

		public $field;

		function __construct() {
			parent::__construct('WebpageCategory');
			$this->_tablename="webpage_categories";
		}



}
?>
