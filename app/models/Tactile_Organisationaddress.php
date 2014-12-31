<?php

class Tactile_Organisationaddress extends Tactile_Address
{
	public function __construct()
	{
		parent::__construct('organisation_addresses');
		
 		$this->belongsTo('Organisation', 'organisation_id', 'organisation'); 
		$this->assignAutoHandler('main', new AddressHandler('organisation_id'));
	}
	
	protected function _touchparent()
	{
		$org = new Tactile_Organisation();
		$fields = array('lastupdated', 'alteredby');
		$values = array('now()', EGS::getUsername());
		return $org->update($this->organisation_id, $fields, $values);
	}
	
	public function asJSON()
	{
		$output = array();
		foreach ($this->defaultDisplayFields as $field => $label) {
			$output[$field] = $this->$field;
		}
		$output['id'] = $this->id;
		$output['organisation_id'] = $this->organisation_id;
		$output['main'] = $this->isMain();
		$output['map_url'] = $this->getMapsURL();
		return json_encode($output);
	}
	
	public function canEdit()
	{
		$parent = new Tactile_Organisation();
		$parent->load($this->organisation_id);
		return $parent->canEdit();
	}
	
	public function canDelete()
	{
		$parent = new Tactile_Organisation();
		$parent->load($this->organisation_id);
		return $parent->canDelete();
	}
	
	public function getFkName()
	{
		return 'organisation_id';
	}
}
