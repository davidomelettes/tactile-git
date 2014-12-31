<?php
class LoggedCallCollection extends DataObjectCollection {
	function __construct() {
		parent::__construct('LoggedCall');
		$this->_tablename="loggedcallsoverview";
	}
	
}
?>