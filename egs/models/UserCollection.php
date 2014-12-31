<?php
class UserCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct() {
			parent::__construct('User');
			$this->_tablename="useroverview";
			
		$this->orderby='username';
		}
	
		
		
}
?>
