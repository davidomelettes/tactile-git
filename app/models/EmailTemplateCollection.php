<?php

class EmailTemplateCollection extends DataObjectCollection {
	
	public function __construct($model='EmailTemplate') {
		parent::__construct($model);
		$this->_tablename='email_templates';
	}
	
}
