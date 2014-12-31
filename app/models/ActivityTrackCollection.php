<?php

class ActivityTrackCollection extends DataObjectCollection {
	
	public function __construct($model='ActivityTrack') {
		parent::__construct($model);
		$this->_tablename='activity_tracks';
	}
	
}
