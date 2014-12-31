<?php

interface TimelineItem {
	
	public function getTimelineType();
	public function getTimelineDate();
	public function getTimelineTime();
	public function getTimelineSubject();
	public function getTimelineBody();
	public function getTimelineURL();
	
}