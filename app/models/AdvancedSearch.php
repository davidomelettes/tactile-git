<?php

class AdvancedSearch extends DataObject {

	public function __construct($tablename='advanced_searches') {
		parent::__construct($tablename);
	}
	
	public function canDelete() {
		return $this->owner == EGS::getUsername() || isModuleAdmin();
	}
	
}
