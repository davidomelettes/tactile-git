<?php

class AdvancedSearchCollection extends DataObjectCollection {
	
	public function __construct($model='AdvancedSearch') {
		parent::__construct($model);
		$this->_tablename='advanced_searches';
	}
	
}
