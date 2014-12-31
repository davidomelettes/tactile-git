<?php
class HourType extends DataObject {
protected $defaultDisplayFields=array('name','group_id');
	public function __construct() {
		parent::__construct('hour_types');
		$this->belongsTo('HourTypeGroup','group_id','group');
	}
}
?>