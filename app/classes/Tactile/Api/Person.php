<?php

/**
 * Tactile API
 * 
 * @package Tactile
 */

/**
 * A Person
 * 
 * @package Tactile
 */
class Tactile_Api_Person extends Tactile_Api_Object {
	
	public $name;
	public $description;
	
	public $title;
	public $firstname;
	public $surname;
	public $suffix;
	public $jobtitle;
	public $dob;
	public $language;
	public $language_code;
	
	public $can_call;
	public $can_email;
	
	public $organisation_id;
	public $organisation;
	public $reports_to;
	
	public $phone_id;
	public $phone;
	public $mobile_id;
	public $mobile;
	public $email_id;
	public $email;
	
	public $street1;
	public $street2;
	public $street3;
	public $town;
	public $county;
	public $postcode;
	public $country;
	public $country_code;
	
	public $owner;
	public $assigned_to;
	public $created;
	public $lastupdated;
	
	public function asJson() {
		$person = (array) $this;
		$person['phone'] = array('id' => $this->phone_id, 'contact' => $this->phone);
		$person['mobile'] = array('id' => $this->mobile_id, 'contact' => $this->mobile);
		$person['email'] = array('id' => $this->email_id, 'contact' => $this->email);

		$json = array();
		$json['Person'] = $person;
		
		return json_encode($json);
	}
	
}
