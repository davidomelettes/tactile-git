<?php

/**
 * Tactile API
 * 
 * @package Tactile
 */

/**
 * An Opportunity
 * 
 * @package Tactile
 */
class Tactile_Api_Opportunity extends Tactile_Api_Object {

	public $name;
	public $description;
	public $enddate;
	
	public $cost;
	public $probability;
	public $status;
	public $status_id;
	public $type;
	public $source;
	public $source_id;
	
	public $organisation_id;
	public $organisation;
	public $person_id;
	public $person;
	
	public $archived;
	
	public $owner;
	public $assigned;
	public $created;
	public $lastupdated;
	public $alteredby;
	
	public function asJson() {
		$opp = (array) $this;
		
		$json = array();
		$json['Opportunity'] = $opp;
		
		return json_encode($json);
	}
	
}
