<?php

class Tactile_Personaddress extends Tactile_Address
{
	public function __construct()
	{
		parent::__construct('person_addresses');
		
 		$this->belongsTo('Person', 'person_id', 'person'); 
		$this->assignAutoHandler('main', new AddressHandler('person_id'));
	}
	
	protected function _touchparent()
	{
		$org = new Tactile_Person();
		$fields = array('lastupdated', 'alteredby');
		$values = array('now()', EGS::getUsername());
		return $org->update($this->person_id, $fields, $values);
	}
	
	public function asJSON()
	{
		$output = array();
		foreach ($this->defaultDisplayFields as $field => $label) {
			$output[$field] = $this->$field;
		}
		$output['id'] = $this->id;
		$output['person_id'] = $this->person_id;
		$output['main'] = $this->isMain();
		$output['map_url'] = $this->getMapsURL();
		return json_encode($output);
	}
	
	public function canEdit()
	{
		$parent = new Tactile_Person();
		$parent->load($this->person_id);
		return $parent->canEdit();
	}
	
	public function canDelete()
	{
		$parent = new Tactile_Person();
		$parent->load($this->person_id);
		return $parent->canDelete();
	}
	
	public function getFkName()
	{
		return 'person_id';
	}
}
