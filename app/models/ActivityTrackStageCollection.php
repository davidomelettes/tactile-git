<?php

class ActivityTrackStageCollection extends DataObjectCollection {
	
	public function __construct($model='ActivityTrackStage') {
		parent::__construct($model);
		$this->_tablename='activity_track_stages';
	}
	
}
