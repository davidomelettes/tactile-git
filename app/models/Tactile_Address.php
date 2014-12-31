<?php

abstract class Tactile_Address extends DataObject {

	protected $defaultDisplayFields = array(
		'name'		=> 'Name',
		'street1'	=> 'Street 1',
		'street2'	=> 'Street 2',
		'street3'	=> 'Street 3',
		'town'		=> 'Town / City',
		'county'	=> 'County / State',
		'postcode'	=> 'Postcode / ZIP',
		'country'	=> 'Country',
	);

	abstract protected function _touchparent();
	abstract public function asJSON();
	abstract public function getFkName();

	public function __construct($table)
	{
		parent::__construct($table);
		$this->idField = 'id';
		$this->getField('name')->setDefault('Main');
		$this->belongsTo('Country', 'country_code', 'country');
	}

	public function isMain()
	{
		return ($this->main == 't');
	}

	public function toArray()
	{
		$output = array();
		foreach ($this->defaultDisplayFields as $field => $label) {
			$value = $this->$field;
			$output[$field] = $value;
		}
		return $output;
	}
	
	public function toHTML()
	{
		$template = "<address>%s</address>";
		$output = array();
		foreach ($this->defaultDisplayFields as $field => $label) {
			$value = $this->$field;
			if (!empty($value) && 'name' !== $field) {
				$output[] = '<span class="'.$field.'">'.htmlspecialchars($value).'</span>';
			}
		}
		return sprintf($template, implode('<br />', $output));
	}
	
	public function getMapsURL()
	{
		$output = array();
		
		foreach ($this->defaultDisplayFields as $field => $label) {
			$value = $this->$field;
			if (!empty($value) && 'name' !== $field) {
				$output[] = urlencode($value);
			}
		}
		
		return 'http://maps.google.com/maps?q=' . implode(",", $output);
	}
	
	public function save()
	{
		$country_code = $this->country_code;
		if (empty($country_code)) {
			$this->country_code = EGS::getCountryCode();
		}
		if (parent::save()) {
			return $this->_touchParent();
		} else {
			return false;
		}
	}
	
	public function delete()
	{
		if (parent::delete()) {
			return $this->_touchParent();
		} else {
			return false;
		}
	}
}