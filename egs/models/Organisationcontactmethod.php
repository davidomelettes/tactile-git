<?php

class Organisationcontactmethod extends DataObject {
	protected $defaultDisplayFields=array('name'=>'Name','contact'=>'Contact','main'=>'Main','billing'=>'Billing','shipping'=>'Shipping','payment'=>'Payment','technical'=>'Technical');
	function __construct() {
		parent::__construct('organisation_contact_methods');
		$this->idField='id';
		
 		$this->belongsTo('Organisation', 'organisation_id', 'organisation'); 
		$this->assignAutoHandler('main',new ContactMethodHandler('organisation_id'));
		$this->getField('name')->setDefault('Main');
	}
	
	function __toString() {
		$value=$this->contact;
		return (!empty($value)?$value:'');
	}

}
