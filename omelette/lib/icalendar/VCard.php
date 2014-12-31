<?php

class VCard {
	
	protected $_newLine = "\r\n";
	protected $_person = null;
	protected $_organisation = null;
	protected $_numbers = array();
	protected $_addresses = array();
	
	public function addPerson($person) {
		if (!$person instanceof Person) {
			throw new Exception('Added Person was not an instance of Person!');
		}
		$this->_person = $person;
	}
	
	public function addOrganisation($org) {
		if (!$org instanceof Organisation) {
			throw new Exception('Added Organisation was not an instance of Organisation!');
		}
		$this->_organisation = $org;
	}
	
	public function addContactMethod($method)
	{
		if ($method instanceof Tactile_Personcontactmethod) {
			$num = $method;
		} else if ($method instanceof Tactile_Organisationcontactmethod) {
			$num = $method;
		} else {
			$num = new Tactile_Personcontactmethod();
			$num->type = 'T';
			$num->contact = $method;
		}
		$this->_contactmethods[] = $num;
	}
	
	public function addAddress($address) 
	{
		if ($address instanceof Tactile_Address) {
			$adr = $address;
		} else {
			$adr = new Tactile_Address();
			foreach ($address as $field => $value) {
				$adr->$field = $value;
			}
		}
		$this->_addresses[] = $adr;
	}
	
	public function toString($title=null) {
		$n = $this->_newLine;
		$output = "BEGIN:VCARD" . $n .
			"VERSION:3.0" . $n;
		
		$addresses = array();
		$contact_methods = array();
		if (!empty($this->_person)) {
			$name = $this->_person->surname . ";" . $this->_person->firstname;
			$output .= "N:$name" . $n .
				"FN:" . $this->_person->name . $n;
			
			if (!empty($this->_person->id)) {
				$collection = new PersoncontactmethodCollection();
				$sh = new SearchHandler($collection, false);
				$sh->addConstraint(new Constraint('person_id', '=' , $this->_person->id));
				$sh->setOrderby('main desc, name');
				$collection->load($sh);
				foreach ($collection as $method) {
					$this->_contactmethods[] = $method;
				}
			}
			foreach ($this->_contactmethods as $method) {
				switch ($method->type) {
					case 'E':
						$output .= "EMAIL:".$method->contact.$n;
						break;
					case 'T':
					case 'F':
					case 'M':
						$output .= "TEL:".$method->contact.$n;
						break;
				}
			}
			
			if (!empty($this->_person->id)) {
				$collection = new PersonaddressCollection();
				$sh = new SearchHandler($collection, false);
				$sh->addConstraint(new Constraint('person_id', '=' , $this->_person->id));
				$sh->setOrderby('main desc, name');
				$collection->load($sh);
				foreach ($collection as $address) {
					$this->_addresses[] = $address;
				}
			}
			foreach ($this->_addresses as $address) {
				$adr = implode(';', $address->toArray());
				$output .= "ADR".($address->isMain() ? ';type=pref' : '').":".$adr.$n;
			}
		}
		if (!empty($this->_organisation)) {
			$output .= "ORG:" . $this->_organisation->name . $n;
		}
		
		$output .= "END:VCARD";
		
		return $output;
	}
	
}
