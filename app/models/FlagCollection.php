<?php

class FlagCollection extends DataObjectCollection {
	public function __construct($flag='Flag') {
		parent::__construct($flag);
		$this->_tablename='flags_overview';
	}
}
