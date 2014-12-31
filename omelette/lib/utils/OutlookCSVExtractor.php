<?php

/**
 * Responsible for taking a row of csv data as exported from Outlook and returning arrays necessary for
 * creating EGS models. The order of the columns doesn't matter, just the headings.
 *  The following headings will be unserstood:
 * - Company (the name of the company)
 * - Account Number*
 * - Web Page
 * - Description*
 * - Business Phone
 * - Business Fax
 * - Business Email*
 * - Business Street 
 * - Business Street 2 
 * - Business Street 3
 * - Business City 
 * - Business State 
 * - Business Postal Code 
 * - Business Country
 * 
 * - Title
 * - First Name
 * - Middle Name
 * - Last Name
 * - Job Title
 * - Birthday
 * - Person Description*
 * - Home Phone
 * - Mobile Phone
 * - E-mail Address
 * 
 * *s indicate non-outlook fields that it knows what to do with
 * @author gj
 */
class OutlookCSVExtractor {

	/**
	 * An array containing the csv headings
	 * @access private
	 * @type Array
	 */
	private $headings = array();
	
	/**
	 * An assoc array of name=>code for countries in Tactile
	 *
	 * @var Array
	 */
	private $countries = array();
	
	/**
	 * An assoc array of type=>array(headings) for heading mappings to use
	 *
	 * @var Array
	 */
	private $headingMappings = array();

	public function __construct($csv_mappings=null) {
		$country = new Country();
		$this->countries = array_flip($country->getAll());
		
		$this->setMappings($csv_mappings);
	}
	
	public function cleanValue($dirty) {
		if (is_array($dirty)) {
			foreach ($dirty as $i => &$dirt) {
				$dirt = $this->cleanValue($dirt);
			}
			$clean = $dirty;
			
		} else {
			$dirty = trim($dirty);
			
			if (mb_detect_encoding($dirty, null, true) === 'UTF-8') {
				return $dirty;
			}
			
			$encoding_order = array(
				'MACINTOSH',
				'ISO-8859-1',
				'windows-1252',
				'ASCII',
				'UTF-8',
			);
			
			foreach ($encoding_order as $encoding) {
				if (FALSE !== ($converted = @iconv($encoding, 'UTF-8', $dirty))) {
					$clean = $converted;
					break;
				}
			}
			if (!isset($clean)) {
				// Last resort, will be messy but should prevent doom
				$clean = utf8_encode($dirty);
			}
		}
		
		return $clean;
	}

	/**
	 * Maps group_field (e.g. person_firstname) to csv heading index (e.g. 0)
	 *
	 * @param unknown_type $csv_mappings
	 */
	public function setMappings($csv_mappings=null) {
		if (!is_null($csv_mappings)) {
			$this->headingMappings = $csv_mappings;
		}
	}
	
	public function getMappings($headings=null) {
		if (empty($this->headingMappings)) {
			if (!empty($this->headings)) {
				return $this->setDefaultMappingsFromHeadings();
			} elseif (!is_null($headings)) {
				$this->headings = $headings;
				return $this->setDefaultMappingsFromHeadings();
			}
			return false;
		}
		return $this->headingMappings;
	}
	
	/**
	 * Attempts to create a heading map using Outlook standards
	 *
	 * @return Array
	 */
	public function setDefaultMappingsFromHeadings() {
		if (!empty($this->headings)) {
			$default_csv_mappings = array(
				'person' => array(
					'Title'					=> 'title',
					'First Name'			=> 'firstname',
					'Last Name'				=> 'surname',
					'Job Title'				=> 'jobtitle',
					'Person Description'	=> 'description',
				),
				'personaddress' => array(
					'Street'				=> 'street1',
					'Street 2'				=> 'street2',
					'Street 3'				=> 'street3',
					'City'					=> 'town',
					'State'					=> 'county',
					'Postal Code'			=> 'postcode',
					'Country'				=> 'country_code'
				),
				'personcontact' => array(
					'Home Phone'			=> 'phones',
					'Mobile Phone'			=> 'mobiles',
					'Home Email'			=> 'emails'
				),
				'organisation' => array(
					'Company'				=> 'name',
					'Account Number'		=> 'accountnumber',
					'Description'			=> 'description'
				),
				'organisationaddress' => array(
					'Business Street'		=> 'street1',
					'Business Street 2'		=> 'street2',
					'Business Street 3'		=> 'street3',
					'Business City'			=> 'town',
					'Business State'		=> 'county',
					'Business Postal Code'	=> 'postcode',
					'Business Country'		=> 'country_code'
				),
				'organisationcontact' => array(
					'Business Phone'		=> 'phones',
					'Business Fax'			=> 'faxes',
					'Business Email'		=> 'emails',
					'Web Page'				=> 'websites',
				)
			);
			
			$csv_mappings = array();
			foreach ($this->headings as $index => $csv_heading) {
				foreach ($default_csv_mappings as $group => $map) {
					foreach ($map as $heading => $field) {
						if ($heading === $csv_heading) {
							$csv_mappings[$group][$field] = $index;
						}
					}
				}
			}
			$this->setMappings($csv_mappings);
			return $this->getMappings();
		} else {
			return false;
		}
	}
	
	/**
	 * Counts the number of lines in the file
	 *
	 * @param SPLFileObject $file
	 * @return int
	 */
	public function countRecords($file) {
		$wc_cmd = 'wc -l ' . escapeshellarg($file->getPathname());
		$count = array_shift(explode(' ', shell_exec($wc_cmd)));
		
		return $count;
	}
	
	/**
	 * Retrives the next row from the specified file
	 *
	 * @param SPLFileObject $file
	 * @return array
	 */
	public function iterate(&$file) {
		if (!$file->eof()) {
			if (empty($this->headings)) {
				$headings = array();
				$unique_headings = array();
				do {
					// Handle blank rows at start of file
					if (FALSE === ($headings = $file->fgetcsv())) {
						throw new Exception("Couldn't extract headings from CSV file!");
					}
					$unique_headings = array_unique($headings);
				} while (!$file->eof() && (count($unique_headings) === 0 || (count($unique_headings) === 1 && $unique_headings[0] === '')));
				if (count($unique_headings) === 0 || (count($unique_headings) === 1 && $unique_headings[0] === '')) {
					throw new Exception("Read entire file, failed to find headings");
				}
				$this->headings = $headings;
				
				$mappings = $this->getMappings();
				if (empty($mappings)) {
					$this->setDefaultMappingsFromHeadings();
				}
			}
			if (FALSE !== ($line = $file->fgetcsv())) {
				return $line;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Take an array of Outlook-style headings and an array representing a CSV row and return
	 * Assoc arrays for a person and an organisation, along with their respective addresses and contact methods
	 *
	 * @param Array $headings
	 * @param Array $row
	 * @return Array
	 */
	public function extract($row, $headings=null) {
		if (!isset($row)) {
			return array(FALSE, FALSE);
		}
		
		$row = $this->cleanValue($row);
		
		if (isset($headings)) {
			$this->headings = $headings;
			$this->setDefaultMappingsFromHeadings(); 
		}
		
		$row = $this->zip($row, $this->getMappings());
		
		$org_data = $person_data = array();
		$org_data = $this->ExtractOrganisation($row);
		$person_data = $this->ExtractPerson($row);
		
		return array($org_data, $person_data);
	}

	/**
	 * Returns an assoc array taking the values from $data and the keys from $headings
	 * 
	 * @param Array $row An array of values (non-associative)
	 * @param Array $headings An map of headings (group_field => index)
	 * @return Array (group_field => value)
	 */
	public function zip(Array $row, Array $heading_map) {
		$zipped = array();
		foreach ($heading_map as $group => $map) {
			if (is_array($map)) {
				foreach ($map as $field => $index) {
					if (is_array($index)) {
						if (isset($index['index']) && strlen($index['index']) > 0) {
							if (!isset($row[$index['index']])) {
								//throw new Exception("Missing index {$index['index']} in row: " . print_r($row,1));
								// Junk line?
								continue;
							}
							$zipped[$group.'_'.$field] = array('value' => $row[$index['index']]);
							if (!empty($index['autocreate'])) {
								$zipped[$group.'_'.$field]['autocreate'] = "on";
							}
						}
					} elseif (!empty($row[$index])) {
						$zipped[$group.'_'.$field] = $row[$index];
					}
				}
			}
		}
		return $zipped;
	}

	/**
	 * Return an array, with EGS fieldnames necessary for creating a company
	 * 
	 * @param Array $row The row of data
	 * @return Array|Boolean
	 */
	public function ExtractOrganisation(Array $row) {
		$org_data = array();
		
		$org_data = $this->OutlookToEGS('organisation', $row);
		if (isset($org_data['name']) && !empty($org_data['name'])) {
			$addresses = $this->ExtractOrganisationAddresses($row);
			if ($addresses !== false) {
				$org_data['addresses'] = array();
				foreach ($addresses as $address) {
					$org_data['addresses'][] = $address;
				}
			}
			$cms = $this->ExtractOrganisationContactMethods($row);
			if ($cms) {
				foreach ($cms as $alias_name => $data) {
					$org_data[$alias_name] = $data;
				}
			}
			
			$custom = $this->extractOrganisationCustomFields($row);
			if (FALSE !== $custom) {
				$org_data['_custom'] = $custom;
			}
			
			return $org_data;
			
		} else {
			return false;
		}
	}

	/**
	 * Pulls out the phone, email and fax for a Company from an outlook csv row
	 * 
	 * @param Array $row The row containing outlook-csv data
	 * @return Array
	 */
	public function ExtractOrganisationContactMethods(Array $row) {
		if (FALSE !== ($methods = $this->OutlookToEGS('organisationcontact', $row))) {
			$contact_data = array();
			foreach ($methods as $type => $contact) {
				$contact_data[$type][] = array('contact' => $contact);
			}
			return $contact_data;
		} else {
			return false;
		}
	}

	/**
	 * Returns an array with the EGS fieldnames appropriate to an address
	 * 
	 * @param Array $data The row of data
	 * @return Array|Boolean
	 */
	public function ExtractOrganisationAddresses(Array $row) {
		$filters = array('country_code' => array($this, 'filterCountry'));
		if (FALSE !== ($address = $this->OutlookToEGS('organisationaddress', $row, $filters))) {
			$address_data = array();
			$address_data[] = $address;
			return $address_data;
		} else {
			return false;
		}
	}
	
	private function filterCountry($country) {
		$code = null;
		if (isset($this->countries[$country])) {
			$code = $this->countries[$country];
		} else {
			// Default to account's country
			$code = EGS::getCountryCode();
		}
		return $code;
	}

	/**
	 * Return an array, with EGS fieldnames necessary for creating a person
	 * 
	 * @param Array $row The row of data
	 * @return Array|Boolean
	 */
	public function ExtractPerson(Array $row) {
		$filters = array('dob'=>array($this, 'filterBirthday'));
		$person_data = $this->OutlookToEGS('person', $row, $filters);
		if (empty($person_data['firstname']) && empty($person_data['surname'])) {
			return false;
		}
		$addresses = $this->ExtractPersonAddresses($row);
		if ($addresses !== false) {
			$person_data['addresses'] = array();
			foreach ($addresses as $address) {
				$person_data['addresses'][] = $address;
			}
		}
		$pcms = $this->ExtractPersonContactMethods($row);
		if ($pcms) {
			foreach($pcms as $alias_name=>$data) {
				$person_data[$alias_name] = $data;
			}
		}
		
		$custom = $this->extractPersonCustomFields($row);
		if (FALSE !== $custom) {
			$person_data['_custom'] = $custom;
		}
		
		return $person_data;
	}

	/**
	 * Method for filtering out 0/0/00 (or similar) which Outlook seems to sometimes put in for null DOBs
	 * 
	 * @param String $dob
	 * @return String|Null
	 */
	private function filterBirthday($dob) {
		if (empty($dob) || preg_replace('#[^1-9]#', '', $dob) == '') {
			return null;
		}
		return $dob;
	}
	
	/**
	 * Returns an array with the EGS fieldnames appropriate to an address
	 * 
	 * @param Array $data The row of data
	 * @return Array|Boolean
	 */
	public function ExtractPersonAddresses(Array $row) {
		$filters = array('country_code' => array($this, 'filterCountry'));
		if (FALSE !== ($address = $this->OutlookToEGS('personaddress', $row, $filters))) {
			$address_data = array();
			$address_data[] = $address;
			return $address_data;
		} else {
			return false;
		}
	}

	/**
	 * Pulls out the 'main' phone, mobile and email for a Person from an outlook csv row
	 * 
	 * @param Array $row The row containing outlook-csv data
	 * @return Array
	 */
	public function ExtractPersonContactMethods(Array $row) {
		if (FALSE !== ($methods = $this->OutlookToEGS('personcontact', $row))) {
			$contact_data = array();
			foreach ($methods as $type => $contact) {
				$contact_data[$type][] = array('contact' => $contact);
			}
			return $contact_data;
		} else {
			return false;
		}
	}

	/**
	 * Takes an array of outlook_field=>egs_field pairs, and an array of outlook_field=>value pairs and returns
	 * the pairs in $fields as egs_field=>value pairs
	 * 
	 * @param Array $fields An array of outlook_field=>egs_field pairs
	 * @param Array $row An array of outlookfield=>value pairs
	 * @param Array optional $filters An array of fieldname=>callback that will be checked and applied where appropriate
	 * @return Array|Boolean
	 */
	public function OutlookToEGS($group, $row, $filters = array()) {
		$translated_data = array();
		foreach ($row as $group_field => $value) {
			$split = split('_', $group_field, 2);
			$field = $split[1];
			if ($split[0] === $group) {
				if (is_string($field) && isset($filters[$field])) {
					$translated_data[$field] = call_user_func($filters[$field], $value);
				} else {
					$translated_data[$field] = $value;
				}
			}
		}
		if (count($translated_data) > 0) {
			return $translated_data;
		} else {
			return false;
		}
	}
	
	public function extractOrganisationCustomFields(Array $row) {
		$data = array();
		foreach ($row as $group_field => $params) {
			$split = split('_', $group_field, 2);
			if ($split[0] !== 'organisationcustom' || !is_array($params)) {
				continue;
			}
			$field_id = $split[1];
			
			$data[$field_id] = array('value' => $params['value']);
			if (!empty($params['autocreate'])) {
				$data[$field_id]['autocreate'] = $params['autocreate'];
			}
		}
		
		return (empty($data) ? false : $data);
	}
	
	public function extractPersonCustomFields(Array $row) {
		$data = array();
		foreach ($row as $group_field => $params) {
			$split = split('_', $group_field, 2);
			if ($split[0] !== 'personcustom' || !is_array($params)) {
				continue;
			}
			$field_id = $split[1];
			
			$data[$field_id] = array('value' => $params['value']);
			if (!empty($params['autocreate'])) {
				$data[$field_id]['autocreate'] = $params['autocreate'];
			}
		}
		
		return (empty($data) ? false : $data);
	}

}
