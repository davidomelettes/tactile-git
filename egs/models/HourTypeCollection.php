<?php
class HourTypeCollection extends DataObjectCollection {
	
	public function __construct() {
		parent::__construct(new HourType());
	}
}
?>