<?php

/**
 * Tactile API Activity
 * 
 * @package Tactile
 */

/**
 * An Activity
 * 
 * @package Tactile
 */
class Tactile_Api_Activity extends Tactile_Api_Object {

	public $name;
	public $description;

	public $date;
	public $time;
	public $type;
	public $later;
	
	public $organisation_id;
	public $organisation;
	public $person_id;
	public $person;
	public $opportunity_id;
	public $opportunity;
	
	public $assigned_to;
	public $assigned_by;
	public $completed;
	
	public $location;
	public $end_date;
	public $end_time;

	public $created;
	public $lastupdated;
	public $owner;
	public $alteredby;
	
	public function asJson() {
		$act = (array) $this;
		
		$json = array();
		$json['Activity'] = $act;
		
		return json_encode($json);
	}
	
}
