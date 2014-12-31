<?php

class ShoeboxedExtractor {

	protected $_dom;
	protected $_xp;
	
	protected $_cards = array();
	
	public function __construct() {
		
	}
	
	public function _init(SPLFileObject $file) {
		if (empty($this->_dom)) {
			$this->_dom = new DOMDocument('10.0', 'UTF-8');
			$this->_dom->loadXML(file_get_contents($file->getPathname()));
			$this->_xp = new DOMXPath($this->_dom);
		}
	}
	
	public function _parseCards() {
		if (empty($this->_cards)) {
			$this->_cards = array();
			$nodelist = $this->_xp->query('//BusinessCard');
			foreach ($nodelist as $node) {
				$this->_cards[] = $node;
			}
		}
	}
	
	public function countRecords(SPLFileObject $file) {
		$this->_init($file);
		return (int) $this->_xp->evaluate('string(//BusinessCards/@count[1])');
	}
	
	public function iterate($file) {
		$this->_init($file);
		$this->_parseCards();
		list(,$return) = each($this->_cards);
		if (is_null($return)) {
			return false;
		}
		return $return;
	}

	public function extract(DOMNode $node) {
		$person_data = array();
		$org_data = array();
		
		$person_map = array(
			'firstname'		=> 'firstName',
			'surname'		=> 'lastName',
			'jobtitle'		=> 'position'
		);
		foreach ($person_map as $field => $attribute) {
			$person_data[$field] = $node->getAttribute($attribute);
		}
		$val = $node->getAttribute('email');
		if (!empty($val)) {
			$person_data['emails'] = array(
				array(
					'contact' => $val,
					'name' => 'Main'
				)
			);
		}
		$val = $node->getAttribute('workPhone');
		if (!empty($val)) {
			$person_data['phones'] = array(
				array(
					'contact' => $val,
					'name' => 'Main'
				)
			);
		}
		$val = $node->getAttribute('cellPhone');
		if (!empty($val)) {
			$person_data['mobiles'] = array(
				array(
					'contact' => $val,
					'name' => 'Main'
				)
			);
		}
		
		$org_map = array(
			'name'			=> 'company',
			'street1'		=> 'address',
			'street2'		=> 'address2',
			'town'			=> 'city',
			'postcode'		=> 'zip',
			'county'		=> 'state',
			'country'		=> 'country'
		);
		foreach ($org_map as $field => $attribute) {
			$org_data[$field] = $node->getAttribute($attribute);
		}
		$val = $node->getAttribute('fax');
		if (!empty($val)) {
			$org_data['faxes'] = array(
				array(
					'contact' => $val,
					'name' => 'Main'
				)
			);
		}
		$val = $node->getAttribute('website');
		if (!empty($val)) {
			$org_data['websites'] = array(
				array(
					'contact' => $val,
					'name' => 'Main'
				)
			);
		}
		
		return array($org_data, $person_data);
	}
	
}
