<?php

/**
 * Collection of VCard objects, as a single VCF file may contain more than one card
 */
class VCardExtractor {

	/**
	 * Array of VCard objects
	 *
	 * @var array
	 */
	var $cards = array();

	/**
	 * Doesn't do a whole lot
	 */
	public function __construct() {

	}
	
	/**
	 * Counts the occurrences of the vCard start tag
	 *
	 * @param SPLFileObject $file
	 * @return int Number of records
	 */
	public function countRecords(&$file) {
		$count = 0;
		while ($line = $file->fgets()) {
			if (FALSE !== (stripos($line, 'BEGIN:VCARD'))) {
				$count++;
			}
		}
		$file->rewind();
		return $count;
	}

	/**
	 * Extract the next vCard from the file
	 *
	 * @param SPLFileObject $file
	 */
	public function iterate($file) {
		$lines = '';
		if (!$file->eof()) {
			while (FALSE != ($line = $file->fgets())) {
				$lines .= $line;
				if (FALSE !== stripos($line, 'END:VCARD')) {
					break;
				}
			}
			
			$vcard = new VCard();
			if (FALSE !== $vcard->buildVCard($lines)) {
				return $vcard;				
			} else {
				return null;
			}
			
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Extract contact information from a vCard
	 *
	 * @param array $headings
	 * @param VCard $vcard
	 * @return array (company_data, person_data)
	 */
	public function extract($vcard) {
		if (!isset($vcard) || !$vcard instanceof VCard) {
			return array(FALSE, FALSE);
		}
		if ('company' == $vcard->getType()) {
			$company_data = $vcard->extractCompany();
			$person_data = FALSE;
		} else {
			if ($company = $vcard->getP('organisation')) {
				$company_data = $vcard->extractCompany();
				$company_data['created'] = date('c');
			} else {
				$company_data = FALSE;
			}
			$person_data = $vcard->extractPerson();
		}
		return array($company_data, $person_data);
	}
	
}


/**
 * A single VCard contact
 */
class VCard {

	/**
	 * Associative array of all properties extracted from the VCard
	 *
	 * @var array
	 */
	private $_p = array();

	/**
	 * An assoc array of name=>code for countries in Tactile
	 *
	 * @var array
	 */
	private $_countries = array();
	private $_country_default;

	private $_type;
	private $_vcard_version;

	private $_title;
	private $_firstName;
	private $_middleName;
	private $_lastName;
	private $_suffix;

	private $_jobTitle;
	private $_organisation;

	private $_companyPhone;
	private $_companyMobile;
	private $_companyFax;
	private $_companyEmail;
	
	private $_personPhone;
	private $_personMobile;
	private $_personFax;
	private $_personEmail;

	private $_companyStreet1;
	private $_companyStreet2;
	private $_companyStreet3;
	private $_companyCity;
	private $_companyCounty;
	private $_companyPostcode;
	private $_companyCountry;

	private $_personStreet1;
	private $_personStreet2;
	private $_personStreet3;
	private $_personCity;
	private $_personCounty;
	private $_personPostcode;
	private $_personCountry;
	
	private $_description;

	private $_dob;


	/**
	 * Returns the first property value which has the required types
	 *
	 * @param string $p_name Name of property ('tel', 'adr', etc.)
	 * @param mixed $type String or array of strings containing required types
	 * @return mixed Usually a string, but could be an array
	 */
	private function _getPropertyValueWithType($p_name, $type, $untype=null) {
		if (!is_array($type)) {
			$type = array($type);
		}
		if (isset($untype) && !is_array($untype)) {
			$untype = array($untype);
		} elseif (!isset($untype)) {
			$untype = array();
		}

		if (isset($this->_p[$p_name])) {
			if (is_array($this->_p[$p_name])) {
				foreach ($this->_p[$p_name] as $p) {
					if (isset($p['types'])) {
						$matches_all = true;
						foreach ($type as $t) {
							if (!in_array($t, $p['types'])) {
								$matches_all = false;
							}
						}
						foreach ($untype as $t) {
							if (in_array($t, $p['types'])) {
								$matches_all = false;
							}
						}
						if ($matches_all) {
							return $p['value'];
						}
					}
				}
			}
			return null;
		} else {
			return null;
		}
	}


	/**
	 * Returns the first value in an array, useful for badly-formed VCards
	 *
	 * @param mixed $array Array or string
	 * @return string Returns the first element of an array or the string
	 */
	private function _arrToStr($array) {
		if (is_array($array)) {
			$keys = array_keys($array);
			return $array[$keys[0]];
		} else {
			return $array;
		}
	}

	
	/**
	 * Sets a person or company address
	 *
	 * @param string $type 'person' or 'company'
	 * @param array $data Key => Value pairs
	 */
	private function _assignAddress($type, $data) {
		switch ($type) {
			case 'company':
				if (!empty($data[0])) {
					$this->_companyStreet1 = (isset($data[0])) ? $this->_arrToStr($data[0]) : null;
					$this->_companyStreet2 = (isset($data[1])) ? $this->_arrToStr($data[1]) : null;
					$this->_companyStreet3 = (isset($data[2])) ? $this->_arrToStr($data[2]) : null;
				} elseif (!empty($data[1])) {
					$this->_companyStreet1 = (isset($data[1])) ? $this->_arrToStr($data[1]) : null;
					$this->_companyStreet2 = (isset($data[2])) ? $this->_arrToStr($data[2]) : null;
				} else {
					$this->_companyStreet1 = (isset($data[2])) ? $this->_arrToStr($data[2]) : null;
				}

				$this->_companyCity = (isset($data[3])) ? $this->_arrToStr($data[3]) : null;
				$this->_companyCounty = (isset($data[4])) ? $this->_arrToStr($data[4]) : null;
				$this->_companyPostcode = (isset($data[5])) ? $this->_arrToStr($data[5]) : null;
				$this->_companyCountry = (isset($data[6])) ? $this->_arrToStr($data[6]) : null;
				break;
			default:
				if (!empty($data[0])) {
					$this->_personStreet1 = (isset($data[0])) ? $this->_arrToStr($data[0]) : null;
					$this->_personStreet2 = (isset($data[1])) ? $this->_arrToStr($data[1]) : null;
					$this->_personStreet3 = (isset($data[2])) ? $this->_arrToStr($data[2]) : null;
				} elseif (!empty($data[1])) {
					$this->_personStreet1 = (isset($data[1])) ? $this->_arrToStr($data[1]) : null;
					$this->_personStreet2 = (isset($data[2])) ? $this->_arrToStr($data[2]) : null;
				} else {
					$this->_personStreet1 = (isset($data[2])) ? $this->_arrToStr($data[2]) : null;
				}

				$this->_personCity = (isset($data[3])) ? $this->_arrToStr($data[3]) : null;
				$this->_personCounty = (isset($data[4])) ? $this->_arrToStr($data[4]) : null;
				$this->_personPostcode = (isset($data[5])) ? $this->_arrToStr($data[5]) : null;
				$this->_personCountry = (isset($data[6])) ? $this->_arrToStr($data[6]) : null;
				break;
		}
	}
	
	
	/**
	 * Parses a single VCard and assigns a set of contact properties
	 * Works with versions 2.1 and 3.0
	 *
	 * @param string $card
	 */
	public function __construct() {
		// Prepare a list of countries
		$country_model = new Country();
		$countries = $country_model->getAll();
		if (!is_array($countries)) {
			throw new Exception('Failed to fetch Country List!');
		}
		$this->_countries = array_flip($countries);
		$this->_country_default = EGS::getCountryCode();
	}
	
	private function _cleanValue($dirty) {
		if (is_array($dirty)) {
			foreach ($dirty as $i => &$dirt) {
				$dirt = $this->_cleanValue($dirt);
			}
			$clean = $dirty;
			
		} else {
			$dirty = trim($dirty);
			$dirty = preg_replace('/\\\\n/', "\n", $dirty);
			$dirty = stripslashes($dirty);
			
			$encoding_order = array(
				'ASCII',
				'UTF-8',
				'ISO-8859-1',
				'windows-1252'
			);
			$encoding = mb_detect_encoding($dirty, $encoding_order);
			switch ($encoding) {
				case 'ASCII':
				case 'UTF-8':
					$clean = $dirty;
					break;
				case 'windows-1252':
					$clean = @iconv("windows-1251", "UTF-8", $dirty);
					break;
				case 'ISO-8859-1':
				default:
					$clean = utf8_encode($dirty);
			}
		}
		
		return $clean;
	}
	
	public function buildVCard($card) {
		$card = utf8_encode($card);
		
		// Which version of VCard are we using?
		if (!preg_match('/VERSION:(.*)/i', $card, $matches)) {
			$matches[1] = '';
		}
		
		switch ($matches[1]) {
			case '3':
			case '3.0':
			case 3:
			case 3.0:
				$this->_vcard_version = '3';

				// Extract all valid properties
				if (!$num = preg_match_all('/(^[^:\n]+):(.+)$/im', $card, $matches)) {
					return FALSE;
					#throw new Exception("Couldn't detect any properties!");
				}

				$keys = $matches[1];
				$values = $matches[2];

				for ($n=0; $n<count($keys); $n++) {

					// Property values may be array-like
					$split_value = preg_split('/;/', $values[$n]);
					if (count($split_value) > 1) {
						$values[$n] = $split_value;
					}

					// Property keys may be typed
					$keys[$n] = preg_replace('/item\d+\./i', '', $keys[$n]);
					$split_key = preg_split('/;/', $keys[$n]);
					if (count($split_key) > 1) {
						$key = strtolower(array_shift($split_key));
						$property = array('types' => array(), 'value' => null);
						$property['value'] = $this->_cleanValue($values[$n]);
						foreach ($split_key as $type) {
							if (preg_match('/type=(.+)/i', $type, $matches)) {
								$property['types'][] = strtolower($matches[1]);
							}
						}

						if (!isset($this->_p[$key])) {
							$this->_p[$key] = array();
						}
						if (is_array($this->_p[$key])) {
							$this->_p[$key][] = $property;
						}
					} else {
						$keys[$n] = strtolower($keys[$n]);
						$this->_p[$keys[$n]][]['value'] = $this->_cleanValue($values[$n]);
						$this->_p[$keys[$n]][]['types'] = array();
					}
				}
				break;
					
			// Assume version 2.1
			default:
				$this->_vcard_version = '2.1';

				// Extract all valid properties
				if (!$num = preg_match_all('/(^[^:\n]+):(.+)$/im', $card, $matches)) {
					#die("Couldn't detect any properties!");
					return FALSE;
				}

				$keys = $matches[1];
				$values = $matches[2];

				for ($n=0; $n<count($keys); $n++) {

					// Property values may be array-like
					$split_value = preg_split('/;/', $values[$n]);
					if (count($split_value) > 1) {
						$values[$n] = $split_value;
					}

					// Property keys may be typed
					$split_key = preg_split('/;/', $keys[$n]);
					if (count($split_key) > 1) {
						$key = strtolower(array_shift($split_key));
						$property = array('types' => array(), 'value' => null);
						$property['value'] = $this->_cleanValue($values[$n]);
						foreach ($split_key as $type) {
							$property['types'][] = strtolower($type);
						}

						$this->_p[$key][] = $property;
					} else {
						$keys[$n] = strtolower($keys[$n]);
						$this->_p[$keys[$n]][]['value'] = $this->_cleanValue($values[$n]);
						$this->_p[$keys[$n]][]['types'] = array();
					}
				}
		}

		// Decode Quoted-Printable values
		foreach ($this->_p as $property => &$instances) {
			foreach ($instances as $index => &$instance) {
				if (isset($instance['types']) && in_array('encoding=quoted-printable', $instance['types'])) {
					if (is_array($instance['value'])) {
						foreach ($instance['value'] as &$segment) {
							$segment = quoted_printable_decode($segment);
						}
					} else {
						$instance['value'] = quoted_printable_decode($instance['value']);
					}
				}
			}
		}
		
		// Make single-value properties easier to access
		$singles = array('x-abuid', 'x-abshowas', 'n', 'fn', 'title', 'org', 'note', 'bday');
		foreach ($singles as $single) {
			if (isset($this->_p[$single][0]['value'])) {
				 $this->_p[$single] = $this->_p[$single][0]['value'];
			}
		}
		
		// Decide contact type
		if (isset($this->_p['x-abuid'])) {
			// Mac AddressBook card
			if (isset($this->_p['x-abshowas']) && (FALSE !== stripos($this->_p['x-abshowas'], 'company'))) {
				$this->_type = 'company';
			} else {
				$this->_type = 'person';
			}
		} else {
			// Decide based on presence of surname
			if (isset($this->_p['n'][0]) && !empty($this->_p['n'][0])) {
				$this->_type = 'person';
			} else {
				$this->_type = 'company';
				$this->_organisation = (isset($this->_p['fn'])) ? $this->_arrToStr($this->_p['fn']) : null;
			}
		}
		
		// Parse Name
		if (isset($this->_p['n'])) {
			if (is_array($this->_p['n']) && !empty($this->_p['n'])) {
				$this->_lastName = (isset($this->_p['n'][0])) ? $this->_arrToStr($this->_p['n'][0]) : null;
				$this->_firstName = (isset($this->_p['n'][1])) ? $this->_arrToStr($this->_p['n'][1]) : null;
				$this->_middleName = (isset($this->_p['n'][2])) ? $this->_arrToStr($this->_p['n'][2]) : null;
				$this->_title = (isset($this->_p['n'][3])) ? $this->_arrToStr($this->_p['n'][3]) : null;
				$this->_suffix = (isset($this->_p['n'][4])) ? $this->_arrToStr($this->_p['n'][4]) : null;
			}
		}

		// Parse job information
		if (isset($this->_p['title'])) {
			$this->_jobTitle = $this->_arrToStr($this->_p['title']);
		}
		if (isset($this->_p['org'])) {
			$this->_organisation = $this->_arrToStr($this->_p['org']);
		}

		// Parse contact details
		if (isset($this->_p['tel'])) {
			// Mobile
			if ($person_tel = $this->_getPropertyValueWithType('tel', 'cell')) {
				$this->_personMobile = $this->_arrToStr($person_tel);
			}
			
			// Phone
			if ($person_tel = $this->_getPropertyValueWithType('tel', 'home', array('cell', 'fax'))) {
				$this->_personPhone = $this->_arrToStr($person_tel);
			}
			if ($company_tel = $this->_getPropertyValueWithType('tel', 'work', array('cell', 'fax'))) {
				$this->_companyPhone = $this->_arrToStr($company_tel);
			}
			
			// Fax
			if ($company_tel = $this->_getPropertyValueWithType('tel', 'fax')) {
				$this->_companyFax = $this->_arrToStr($company_tel);
			}
			if ($person_tel = $this->_getPropertyValueWithType('tel', array('fax', 'home'))) {
				$this->_personFax = $this->_arrToStr($person_tel);
			}
		}
		
		// Email addresses
		if (isset($this->_p['email'])) {
			if ($person_email = $this->_getPropertyValueWithType('email', 'internet')) {
				$this->_personEmail = $this->_arrToStr($person_email);
			}
			if ($person_email = $this->_getPropertyValueWithType('email', 'home')) {
				$this->_personEmail = $this->_arrToStr($person_email);
			}
			if ($company_email = $this->_getPropertyValueWithType('email', 'work')) {
				$this->_companyEmail = $this->_arrToStr($company_email);
			}
		}

		// Description
		if (isset($this->_p['note'])) {
			$this->_description = $this->_arrToStr($this->_p['note']);
		}

		// Extra Info
		if (isset($this->_p['bday'])) {
			$this->_dob = $this->_arrToStr($this->_p['bday']);
		}

		// Address Details
		if (isset($this->_p['adr'])) {
			if ($person_adr = $this->_getPropertyValueWithType('adr', 'home')) {
				$this->_assignAddress('person', $person_adr);
			}
			if ($company_adr = $this->_getPropertyValueWithType('adr', 'work')) {
				$this->_assignAddress('company', $company_adr);
			}
		}
	}


	/**
	 * Extracts person information if correct type
	 *
	 * @return array
	 */
	public function extractPerson() {
		$person_data = array(
			'title'					=> $this->_title,
			'firstname'				=> $this->_firstName, 
			'middlename'			=> $this->_middleName, 
			'surname'				=> $this->_lastName,
			'jobtitle'				=> $this->_jobTitle,
			'dob'					=> $this->_dob, 
			'description'			=> $this->_description
		);
		
		if (empty($person_data['firstname']) && empty($person_data['surname'])) {
			return false;
		}
		
		if ($address = $this->extractPersonAddress()) {
			$person_data['addresses'][] = $address;
		}
		
		if ($methods = $this->extractPersonMainContactMethods()) {
			foreach ($methods as $method => $value) {
				$person_data[$method] = $value;
			}
		}
		
		return $person_data;
	}


	/**
	 * Extracts company information if correct type
	 *
	 * @return array
	 */
	public function extractCompany() {
		$company_data = array(
			'name'				=> $this->_organisation,
			'description'		=> $this->_description
		);
		
		if ($address = $this->extractCompanyAddress()) {
			$company_data['addresses'][] = $address;
		}

		if ($methods = $this->extractCompanyMainContactMethods()) {
			foreach ($methods as $method => $value) {
				$company_data[$method] = $value;
			}
		}
		
		return $company_data;
	}


	/**
	 * Extracts address information
	 *
	 * @return array
	 */
	public function extractCompanyAddress() {
		$address_data = array(
			'street1'				=> $this->_companyStreet1, 
			'street2'				=> $this->_companyStreet2, 
			'street3'				=> $this->_companyStreet3, 
			'town'					=> $this->_companyCity, 
			'county'				=> $this->_companyCounty, 
			'postcode'				=> $this->_companyPostcode,
			'country_code'			=> $this->_companyCountry
		);
			
		// Load country code
		if (isset($this->_countries[$address_data['country_code']])) {
			$address_data['country_code'] = $this->_countries[$address_data['country_code']];
		} else {
			$address_data['country_code'] = $this->_country_default;
		}
			
		return $address_data;
	}

	
	/**
	 * Extracts address information
	 *
	 * @return array
	 */
	public function extractPersonAddress() {
		$address_data = array(
			'street1'				=> $this->_personStreet1, 
			'street2'				=> $this->_personStreet2, 
			'street3'				=> $this->_personStreet3, 
			'town'					=> $this->_personCity, 
			'county'				=> $this->_personCounty, 
			'postcode'				=> $this->_personPostcode,
			'country_code'			=> $this->_personCountry
		);
			
		// Load country code
		if (isset($this->_countries[$address_data['country_code']])) {
			$address_data['country_code'] = $this->_countries[$address_data['country_code']];
		} else {
			$address_data['country_code'] = $this->_country_default;
		}
			
		return $address_data;
	}
	

	/**
	 * Extracts Company contact information if correct type
	 *
	 * @return array
	 */
	public function extractCompanyMainContactMethods() {
		$contact_data = array(
			'phone'					=> array('contact' => $this->_companyPhone),
			'mobile'				=> array('contact' => $this->_companyMobile), 
			'fax'					=> array('contact' => $this->_companyFax), 
			'email'					=> array('contact' => $this->_companyEmail) 
		);
		return $contact_data;
	}


	/**
	 * Extracts Person contact information if correct type
	 *
	 * @return array
	 */
	public function extractPersonMainContactMethods() {
		$contact_data = array(
			'phone'					=> array('contact' => $this->_personPhone),
			'mobile'				=> array('contact' => $this->_personMobile), 
			'fax'					=> array('contact' => $this->_personFax), 
			'email'					=> array('contact' => $this->_personEmail) 
		);
		return $contact_data;
	}


	/**
	 * Returns what type of VCard this is
	 *
	 * @return string ('person' or 'company')
	 */
	public function getType() {
		return $this->_type;
	}

	
	/**
	 * Getter
	 *
	 * @param string $name
	 * @return string
	 */
	public function getP($name) {
		if (FALSE === strpos($name, '_')) {
			$name = '_' . $name;
		}
		if (isset($this->$name)) {
			return $this->$name;
		} else {
			return null;
		}
	}
	
}
