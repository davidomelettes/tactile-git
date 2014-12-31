<?php
class HourTypeGroupCollection extends DataObjectCollection {

	public function __construct() {
		parent::__construct(new HourTypeGroup());
	}
}
?>