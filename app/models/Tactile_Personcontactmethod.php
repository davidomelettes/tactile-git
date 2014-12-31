<?php

class Tactile_Personcontactmethod extends Personcontactmethod {
	
	public function asJson() {
		$json = array();

		$string_fields = array('contact', 'type', 'name');
		$int_fields = array('id', 'person_id');
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
	
	public function is_main() {
		return ($this->main == 't');
	}
	
	protected function _touchParent() {
		$person = new Tactile_Person();
		$fields = array('lastupdated', 'alteredby');
		$values = array('now()', EGS::getUsername());
		return $person->update($this->person_id, $fields, $values);
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
