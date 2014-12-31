<?php
class PollOptionCollection extends DataObjectCollection {

	function __construct($option='PollOption') {
		parent::__construct($option);
		$this->_tablename='poll_options_overview';
	}
}
?>