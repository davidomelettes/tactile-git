<?php

/**
 * Tactile API
 * 
 * @package Tactile
 */

require_once 'Tactile/Api/Object.php';

/**
 * An Organisation
 *
 * @package Tactile
 */
class Tactile_Api_Organisation extends Tactile_Api_Object {
	
	public $name;
	public $accountnumber;
	public $description;
	
	public $status_id;
	public $status;
	public $source_id;
	public $source;
	public $classification_id;
	public $classification;
	public $rating_id;
	public $rating;
	public $industry_id;
	public $industry;
	public $type_id;
	public $type;
	
	public $parent_id;
	public $parent;
	
	public $phone_id;
	public $phone;
	public $fax_id;
	public $fax;
	public $email_id;
	public $email;
	public $website;
	public $website_id;
	
	public $street1;
	public $street2;
	public $street3;
	public $town;
	public $county;
	public $postcode;
	public $country;
	public $country_code;
	
	public $owner;
	public $assigned;
	public $created;
	public $lastupdated;
	
	public function asJson() {
		$org = (array) $this;
		$org['phone'] = array('id' => $this->phone_id, 'contact' => $this->phone);
		$org['fax'] = array('id' => $this->fax_id, 'contact' => $this->fax);
		$org['email'] = array('id' => $this->email_id, 'contact' => $this->email);
		$org['website'] = array('id' => $this->website_id, 'contact' => $this->website);

		$json = array();
		$json['Organisation'] = $org;
		
		return json_encode($json);
	}
	
}
