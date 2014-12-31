<?php

/**
 * A flimsy-yet-faster version of DataObject for use in Timelines
 */
class TimelineObject implements TimelineItem {
	
	private $_data = array();
	private $_related = array();
	
	public function __construct($data=null) {
		if (!is_null($data)) {
			$this->setData($data);
		}
	}
	
	public function setData($data) {
		$this->_data = $data;
		return $this;
	}
	
	public function __get($key) {
		return (isset($this->_data[$key]) ? $this->_data[$key] : null);
	}
	
	public function __set($key, $value) {
		$this->_data[$key] = $value;
	}
	
	public function getFormatted($key) {
		return '';
	}
	
	public function getTimelineType() {
		return ucwords(str_replace('s3', '', str_replace('_', ' ', $this->type)));
	}
	
	public function getTimelineDate() {
		$formatter = new TimelineTimestampFormatter();
		switch ($this->getTimelineType()) {
			default:
				$val = $this->getTimelineTime();
		}
		return $formatter->format($val);
	}
	
	public function getTimelineTime() {
		switch ($this->getTimelineType()) {
			default:
				return $this->when;
		}
	}
	
	public function getTimelineSubject() {
		switch ($this->getTimelineType()) {
			default:
				return $this->title;
		}
	}
	
	public function getTimelineBody($formatted=true) {
		$formatter = new URLParsingFormatter();
		switch ($this->getTimelineType()) {
			default:
				$string = $this->body;
		}
		
		return ($formatted ? $formatter->format($string) : $string);
	}
	
	public function getTimelineURL() {
		$action = 'view';
		switch ($this->getTimelineType()) {
			case 'Completed Activity':
			case 'New Activity':
			case 'Overdue Activity':
				$controller = 'activities';
				break;
			case 'Opportunity':
				$controller = 'opportunities';
				break;
			case 'File':
				$controller = strtolower($this->getTimelineType()) . 's';
				$action = 'get';
				break;
			default:
				$controller = strtolower($this->getTimelineType()) . 's';
		}
		
		return '/' . $controller . '/' . $action . '/' . $this->id;
	}
	
	public function getTimelineWhenString($html=true) {
		$verbed = 'Created by';
		$who = str_replace('//'.Omelette::getUserSpace(), '', $this->owner);
		$when = $this->when;
		$extra = '';
		
		$time_formatter = new PrettyTimestampFormatter();
		
		switch ($this->getTimelineType()) {
			case 'Note':
				if ($this->created != $this->lastupdated) {
					$updater = str_replace('//'.Omelette::getUserSpace(), '', $this->alteredby);
					$extra = ', updated by ' .
						($html ? '<span class="who' . ($this->alteredby == EGS::getUsername() ? ' me' : '') . '">' . $updater . '</span>' : $updater) . ' ' .
						$time_formatter->format($this->lastupdated);
				}
				break;
			case 'Email':
				$verbed = 'Received by';
				break;
			case 'File':
				$verbed = 'Uploaded by';
				break;
			case 'New Activity':
				break;
			case 'Overdue Activity':
				$verbed = 'Was due with';
				$who = str_replace('//'.Omelette::getUserSpace(), '', $this->assigned_to);
				break;
			case 'Completed Activity':
				$verbed = 'Completed by';
				$who = str_replace('//'.Omelette::getUserSpace(), '', $this->assigned_to);
				break;
		}
		if ($html) {
			$who = '<span class="who' . ($this->owner == EGS::getUsername() ? ' me' : '') . '">' . $who . '</span>'; 
		}
		
		return $verbed . ' ' . $who . ' ' . $time_formatter->format($when) . $extra;
	}
	
}
