<?php
class NewsletterCollection extends DataObjectCollection {

		public $field;

		function __construct() {
			parent::__construct('Newsletter');
			$this->_tablename="newsletteroverview";
		}



}
?>
