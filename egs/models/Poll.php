<?php
class Poll extends DataObject {
	function __construct() {
		parent::__construct('polls');
		$this->belongsTo('Website');
		$this->hasMany('PollOption','options');
	}
}
?>