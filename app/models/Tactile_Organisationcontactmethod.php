<?php

class Tactile_Organisationcontactmethod extends Organisationcontactmethod {
	
	protected $defaultDisplayFields = array(
		'name'		=> 'Name',
		'contact'	=> 'Contact',
		'main'		=> 'Main'
	);
	
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
	
	public function asJson() {
		$json = array();

		$string_fields = array('contact', 'type', 'name');
		$int_fields = array('id', 'organisation_id');
		$boolean_fields = array('main');
		
		foreach ($string_fields as $field) {
			$value = $this->$field;
			$json[$field] = ((is_null($value) || '' === $value) ? null : (string) $value);
		}
		foreach ($int_fields as $field) {
			$value = $this->$field; 
			$json[$field] = ((is_null($value) || '' === $value) ? null : (int) $value);
		}
		foreach ($boolean_fields as $field) {
			$json[$field] = $this->{'is_'.$field}();
		}

		return json_encode($json);
	}
	
	protected function _touchParent() {
		$org = new Tactile_Organisation();
		$fields = array('lastupdated', 'alteredby');
		$values = array('now()', EGS::getUsername());
		return $org->update($this->organisation_id, $fields, $values);
	}
	
	public function save() {
		if (parent::save()) {
			return $this->_touchParent();
		} else {
			return false;
		}
	}
	
	public function delete() {
		if (parent::delete()) {
			return $this->_touchParent();
		} else {
			return false;
		}
	}
	
}
