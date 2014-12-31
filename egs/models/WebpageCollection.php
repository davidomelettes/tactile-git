<?php
class WebpageCollection extends DataObjectCollection {

		public $field;

		function __construct() {
			parent::__construct('Webpage');
			$this->_tablename="webpagesoverview";
		}



}
?>
