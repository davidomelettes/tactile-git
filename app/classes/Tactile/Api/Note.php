<?php

/**
 * Tactile API
 * 
 * @package Tactile
 */

/**
 * A Note
 *
 * @package Tactile
 */
class Tactile_Api_Note {
	
	public $title;
	public $note;
	
	public $organisation_id;
	public $organisation;
	public $person_id;
	public $person;
	public $opportunity_id;
	public $opportunity;
	public $activity_id;
	public $activity;
	
	public $owner;
	public $private;
	public $created;
	public $lastupdated;
	
	public function asJson() {
		$note = (array) $this;

		$json = array();
		$json['Note'] = $note;
		
		return json_encode($json);
	}
}
