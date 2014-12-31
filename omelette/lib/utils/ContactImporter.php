<?php
/**
 * Including these as even if no logger is passed in, we'll use a Null-Logger so as to avoid lots of checks
 */
require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Null.php';

/**
 * Responsible for the parsing and importing of Outlook-exported CSV data, or VCards
 * 
 * Iterates over rows, extracts data for Org, Person and their respective ContactMethods and Addresses.
 * Rows are validated and errors returned where appropriate. Errors don't stop the import.
 * @author gj
 */
class ContactImporter {

	/**
	 * The file to be parsed
	 * @var SPLFileObject
	 */
	private $file;

	/**
	 * The object used to extract records from the file
	 *
	 * @var mixed
	 */
	private $_extractor;
	
	/**
	 * The data extracted for companies
	 * @access private
	 * @type Array
	 */
	private $companies_to_save = array();

	/**
	 * The data extracted for people
	 * @access private
	 * @type Array
	 */
	private $people_to_save = array();

	/**
	 * The number of rows with a successful import
	 * @var int
	 */
	private $num_imported = 0;

	/**
	 * The number of rows with errors
	 * @var int
	 */
	private $num_errors = 0;

	/**
	 * The ids of companies successfully imported
	 * @var Array
	 */
	private $organisation_ids = array();

	/**
	 * The ids of people successfully imported
	 *
	 * @var Array
	 */
	private $person_ids = array();
	
	/**
	 * A Zend_Log instance for logging, this is only used if it's passed in
	 *
	 * @var Zend_Log
	 */
	protected $logger;

	/**
	 * A list of read roles to assign to each successfully imported Organisation
	 *
	 * @var array
	 */
	private $_org_roles_read = array();
	
	/**
	 * A list of write roles to assign to each successfully imported Organisation
	 *
	 * @var array
	 */
	private $_org_roles_write = array();
	
	/**
	 * A list of tags to add to each successfully imported item
	 *
	 * @var array
	 */
	private $_tags = array();
	
	protected $_organisationCustomFields;
	protected $_personCustomFields;
	
	/**
	 * Set the datasource for the CSV file (a SPLFileObject) and extracts the headings
	 * 
	 * Assumes first row contains headings
	 * @see SPLFileObject
	 * @param string $filename
	 * @return void
	 */
	public function __construct($filename) {
		$this->cleanFile($filename);
		$this->file = new SPLFileObject($filename);
		$this->setDefaultLogger();
		
		// Load the custom field collections here so as not have to do it multiple times later
		$this->_organisationCustomFields = new CustomfieldCollection();
		$sh = new SearchHandler($this->_organisationCustomFields, false);
		$sh->extract();
		$sh->addConstraint(new Constraint('organisations', '=', 'true'));
		$this->_organisationCustomFields->load($sh);
		$this->_personCustomFields = new CustomfieldCollection();
		$sh = new SearchHandler($this->_personCustomFields, false);
		$sh->extract();
		$sh->addConstraint(new Constraint('people', '=', 'true'));
		$this->_personCustomFields->load($sh);
	}

	public function cleanFile($filename) {
		// Remove null characters
		$sed_cmd = 'sed -i -e "s/\x00//g" ' . escapeshellarg($filename);
		shell_exec($sed_cmd);
		// Normalise newlines
		$sed_cmd = 'sed -i -e "s/\r/\n/g" ' . escapeshellarg($filename);
		shell_exec($sed_cmd);
		$sed_cmd = 'sed -i -e "s/\n\n/\n/g" ' . escapeshellarg($filename);
		shell_exec($sed_cmd);
	}
	
	/**
	 * Give the option of turning on logging
	 *
	 * @param Zend_Log $logger
	 */
	public function setLogger(Zend_Log $logger) {
		$this->logger = $logger;
		$this->logger->info('Custom Logger attached to ContactImporter');
	}

	/**
	 * We want importing to be able to log, but not always so setup a Null writer which can be replaced
	 * when it wants to be
	 *
	 * @return void
	 */
	protected function setDefaultLogger() {
		$this->logger = new Zend_Log(new Zend_Log_Writer_Null());
	}

	/**
	 * Associate this importer with an Extractor class
	 *
	 * @param mixed $extractor Extractor class
	 */
	public function setExtractor($extractor) {
		$this->_extractor = $extractor;
	}
	
	/**
	 * Uses the Importer's Extractor to determine how many records the file contains
	 *
	 * @return int Number of records in $this->file
	 */
	public function countRecords() {
		$records = $this->_extractor->countRecords($this->file);
		
		return $records;
	}
	
	/**
	 * Prepare the data prior to saving
	 * 
	 * Takes the file, loops over each csv row, translates headings from outlook to egs fieldnames and builds
	 * the arrays ready for model-creation and saving
	 * @return void
	 */
	public function prepare() {
		try {
			while (FALSE !== ($data = $this->_extractor->iterate($this->file))) {
				list($this->companies_to_save[], $this->people_to_save[]) = $this->_extractor->extract($data);
			}
		} catch (Exception $e) {
			die('There was an error preparing the import data: ' . $e->getMessage());
		}
		$this->logger->debug(count($this->companies_to_save) . ' companies prepared for saving');
		$this->logger->debug(count($this->people_to_save) . ' people prepared for saving');
	}

	/**
	 * Handles the creation of rows for the custom_field_map table, and the creation of custom_field_options if necessary 
 	*/
	protected function _customfieldMapFromParams(Customfield $cfield, Array $params, Array &$map_data) {
		$db = DB::Instance();
		$current_user = CurrentlyLoggedInUser::Instance();
		
		if (!isset($params['value']) || ($cfield->type !== 'c' && strlen(trim($params['value'])) < 1)) {
			return false;
		}
		
		$value = trim($params['value']);
		switch ($cfield->type) {
			case 'c':
				$map_data['enabled'] = !in_array(strtolower($value), array('', 'false', 'off', 'no'));
				return true;
				break;
			case 's':
				// Does the option exist?
				$sql = "SELECT id FROM custom_field_options WHERE field_id = " . $db->qstr($cfield->id) . " AND value = " . $db->qstr($value);
				$option_id = $db->getOne($sql);
				if (empty($option_id)) {
					// No matching option found, permission to create one?
					if (!empty($params['autocreate']) && $current_user->isAdmin()) {
						// Create one
						$option = new CustomfieldOption();
						$option->field_id = $cfield->id;
						$option->value = $value;
						if ($option->save()) {
							$map_data['option'] = $option->id;
							return true;
						} else {
							return false;
						}
					}
				} else {
					// It exists
					$map_data['option'] = $option_id;
					return true;
				}
				break;
			case 'n':
				$map_data['value'] = (float)$value;
				return true;
				break;
			default:
				$map_data['value'] = $value;
				return true;
		}
		return false;
	}

	/**
	 * Performs an import of the prepared companies and people
	 * - duplicate company names won't be imported
	 *
	 * @param Array &$errors
	 * @return Boolean
	 */
	public function import(&$errors) {
		$saver = new ModelSaver();
		
		// load all existing company names
		$company_model = DataObject::Construct('Organisation');
		$cc = new ConstraintChain();
		
		$db = DB::Instance();
		$current_user = CurrentlyLoggedInUser::Instance();
		$query = 'SELECT name, id FROM organisations WHERE usercompanyid = ' . $db->qstr(EGS::getCompanyId());
		$existing_companies = $db->GetAssoc($query);
		if($existing_companies === false) {
			throw new Exception("Loading organisations failed: " . $db->ErrorMsg() . $query);
		}
		$this->logger->debug('Existing companies found: ' . count($existing_companies));
		
		// foreach company, check whether it needs to be created and if so create it as the correct type
		// and add it to the list
		foreach($this->companies_to_save as $i=>$company_data) {
			$row_errors = array();
			if(empty($company_data)) {
				$this->logger->debug("Skipping company row $i");
				continue;
			}
			if(isset($existing_companies[$company_data['name']])) {
				$this->logger->debug($company_data['name'] . ' already exists');
				$organisation_id = $existing_companies[$company_data['name']];
				if(isset($this->people_to_save[$i]) && $this->people_to_save[$i] !== false) {
					$this->people_to_save[$i]['organisation_id'] = $organisation_id;
				}
			} else {
				$this->logger->debug('Need to add (' . $company_data['name'] . ')');
				
				$company = $saver->save($company_data, 'Organisation', $row_errors);
				if($company !== false) {
					$saver->saveAliases($company_data, $company, $row_errors);
					$this->logger->debug('Company added sucessfully, row ' . $i);
					$existing_companies[$company_data['name']] = $company->id;
					if(isset($this->people_to_save[$i]) && $this->people_to_save[$i] !== false) {
						$this->people_to_save[$i]['organisation_id'] = $company->id;
					}
					
					if(isset($company_data['emails'])) {
						$this->logger->debug('Company has '.count($company_data['emails']).' emails');
						foreach($company_data['emails'] as $email_data) {
							$email_data['organisation_id'] = $company->id;
							$email_data['type'] = 'E';
							$this->logger->debug('Adding email ' . print_r($email_data, true));
							$these_errors = array();
							$email = $saver->save($email_data, 'Organisationcontactmethod', $these_errors);
							$row_errors = array_merge($row_errors, $these_errors);
						}
					}
					
					if(isset($company_data['phones'])) {
						$this->logger->debug('Company has '.count($company_data['phones']).' phone-numbers');
						foreach($company_data['phones'] as $phone_data) {
							$phone_data['organisation_id'] = $company->id;
							$phone_data['type'] = 'T';
							$this->logger->debug('Adding phone ' . print_r($phone_data, true));
							$these_errors = array();
							$phone = $saver->save($phone_data, 'Organisationcontactmethod', $these_errors);
							$row_errors = array_merge($row_errors, $these_errors);
						}
					}
					
					if(isset($company_data['faxes'])) {
						$this->logger->debug('Company has '.count($company_data['faxes']).' fax-numbers');
						foreach($company_data['faxes'] as $fax_data) {
							$fax_data['organisation_id'] = $company->id;
							$fax_data['type'] = 'F';
							$this->logger->debug('Adding fax ' . print_r($fax_data, true));
							$these_errors = array();
							$fax = $saver->save($fax_data, 'Organisationcontactmethod', $these_errors);
							$row_errors = array_merge($row_errors, $these_errors);
						}
					}
					
					if(isset($company_data['websites'])) {
						$this->logger->debug('Company has '.count($company_data['websites']).' websites');
						foreach($company_data['websites'] as $www_data) {
							$www_data['organisation_id'] = $company->id;
							$www_data['type'] = 'W';
							$this->logger->debug('Adding website ' . print_r($www_data, true));
							$these_errors = array();
							$website = $saver->save($www_data, 'Organisationcontactmethod', $these_errors);
							$row_errors = array_merge($row_errors, $these_errors);
						}
					}
					
					if (isset($company_data['addresses'])) {
						$this->logger->debug('Company has '.count($company_data['addresses']).' addresses');
						foreach($company_data['addresses'] as $address_data) {
							$address_data['organisation_id'] = $company->id;
							$this->logger->debug('Adding address ' . print_r($address_data, true));
							$these_errors = array();
							$address = $saver->save($address_data, 'Tactile_Organisationaddress', $these_errors);
							$row_errors = array_merge($row_errors, $these_errors);
						}
					}
					
					if (isset($company_data['_custom'])) {
						foreach($company_data['_custom'] as $field_id => $params) {
							if (FALSE !== ($i = $this->_organisationCustomFields->contains('id', $field_id))) {
								$cfield = $this->_organisationCustomFields->getContents($i);
								
								$map_data = array(
									'field_id' => $cfield->id,
									'organisation_id' => $company->id,
									'hash' => 'org'.$company->id
								);
								if (FALSE !== $this->_customfieldMapFromParams($cfield, $params, $map_data)) {
									$map = $saver->save($map_data, 'CustomfieldMap', $these_errors);
									$row_errors = array_merge($row_errors, $these_errors);
								}
							}
						}
					}
					
					$this->num_imported++;
					$this->organisation_ids[$i] = $company->id;
					
					// Tag'em and bag'em
					if (!empty($this->_tags)) {
						$taggable = new TaggedItem(DataObject::Construct('Organisation'));
						$taggable->addTagsInBulk($this->_tags, array($company->id));
					}
					Omelette_OrganisationRoles::AssignReadAccess(array($company->id), $this->_org_roles_read);
					Omelette_OrganisationRoles::AssignWriteAccess(array($company->id), $this->_org_roles_write);
					
				} else {
					$this->logger->debug('Company failed to save, row ' . $i);
					$this->logger->debug(print_r($company_data, true));
					$this->logger->debug(print_r($row_errors, true));
					$this->num_errors++;
				}
			}
			if(count($row_errors)>0) {
				$errors[$i] = $row_errors;
			}
		}
		
		//  then get all people, look for an attached company, and save them- skip people who are 'false'
		foreach($this->people_to_save as $i=>$person_data) {
			$row_errors = array();
			if($person_data === false) {
				$this->logger->debug("Skipping row $i for people");
				continue;
			}
			
			// try to find this person, if it exists then we should skip
			$person_model = DataObject::Construct('Person');
			$cc = new ConstraintChain();
			$firstname = (isset($person_data['firstname'])) ? $person_data['firstname'] : "";
			$surname = (isset($person_data['surname'])) ? $person_data['surname'] : "";
			$cc->add(new Constraint('firstname', '=', $firstname));
			$cc->add(new Constraint('surname', '=', $surname));
			$existing = $person_model->loadBy($cc);
			if($existing !== false) {
				$this->logger->debug('Person already exists, skipping');
				continue;
			}
			
			$person = $saver->save($person_data, 'Person', $row_errors);
			if($person !== false) {
				if(isset($this->logger)) {
					$this->logger->debug('Person saved successfully, row ' . $i);
				}
				$saver->saveAliases($person_data, $person, $row_errors);
				
				if(isset($person_data['emails'])) {
					$this->logger->debug('Person has '.count($person_data['emails']).' emails');
					foreach($person_data['emails'] as $email_data) {
						$email_data['person_id'] = $person->id;
						$email_data['type'] = 'E';
						$this->logger->debug('Adding email ' . print_r($email_data, true));
						$these_errors = array();
						$email = $saver->save($email_data, 'Personcontactmethod', $these_errors);
						$row_errors = array_merge($row_errors, $these_errors);
					}
				}
				
				if(isset($person_data['phones'])) {
					$this->logger->debug('Person has '.count($person_data['phones']).' phone-numbers');
					foreach($person_data['phones'] as $phone_data) {
						$phone_data['person_id'] = $person->id;
						$phone_data['type'] = 'T';
						$this->logger->debug('Adding phone ' . print_r($phone_data, true));
						$these_errors = array();
						$phone = $saver->save($phone_data, 'Personcontactmethod', $these_errors);
						$row_errors = array_merge($row_errors, $these_errors);
					}
				}
				
				if(isset($person_data['mobiles'])) {
					$this->logger->debug('Person has '.count($person_data['mobiles']).' mobile-phone-numbers');
					foreach($person_data['mobiles'] as $mobile_data) {
						$mobile_data['person_id'] = $person->id;
						$mobile_data['type'] = 'M';
						$this->logger->debug('Adding mobile ' . print_r($mobile_data, true));
						$these_errors = array();
						$mobile = $saver->save($mobile_data, 'Personcontactmethod', $these_errors);
						$row_errors = array_merge($row_errors, $these_errors);
					}
				}
				
				if (isset($person_data['addresses'])) {
					$this->logger->debug('Person has '.count($person_data['addresses']).' addresses');
					foreach($person_data['addresses'] as $address_data) {
						$address_data['person_id'] = $person->id;
						$this->logger->debug('Adding address ' . print_r($address_data, true));
						$these_errors = array();
						$address = $saver->save($address_data, 'Tactile_Personaddress', $these_errors);
						$row_errors = array_merge($row_errors, $these_errors);
					}
				}
				
				if (isset($person_data['_custom'])) {
					foreach($person_data['_custom'] as $field_id => $params) {
						if (FALSE !== ($i = $this->_personCustomFields->contains('id', $field_id))) {
							$cfield = $this->_personCustomFields->getContents($i);
							
							$map_data = array(
								'field_id' => $cfield->id,
								'person_id' => $person->id,
								'hash' => 'per'.$person->id
							);
							if (FALSE !== $this->_customfieldMapFromParams($cfield, $params, $map_data)) {
								$map = $saver->save($map_data, 'CustomfieldMap', $these_errors);
								$row_errors = array_merge($row_errors, $these_errors);
							}
						}
					}
				}
				
				$this->num_imported++;
				$this->person_ids[$i] = $person->id;
				
				// Tag'em
				if (!empty($this->_tags)) {
					$taggable = new TaggedItem(DataObject::Construct('Person'));
					$taggable->addTagsInBulk($this->_tags, array($person->id));
				}
			} else {
				if(isset($this->logger)) {
					$this->logger->debug('Person failed to save, row ' . $i);
				}
				$this->num_errors++;
			}
			if (count($row_errors)>0) {
				if (!isset($errors[$i])) {
					$errors[$i]=array();
				}
				$errors[$i] = array_merge($errors[$i],$row_errors);
			}
		}
		return true;
	}

	
	/**
	 * Returns the number of rows successfully imported
	 * 
	 * @return Integer
	 */
	public function num_records_imported() {
		return $this->num_imported;
	}

	/**
	 * Returns the number of rows that errored
	 * 
	 * @return Integer
	 */
	public function num_records_with_errors() {
		return $this->num_errors;
	}

	/**
	 * Returns an array containing the IDs of the imported companies
	 * 
	 * @return Array
	 */
	public function get_organisation_ids() {
		return $this->organisation_ids;
	}
	
	/**
	 * Returns an array containing the IDs of the imported people
	 *
	 * @return Array
	 */
	public function get_person_ids() {
		return $this->person_ids;
	}
	
	public function setTags($tags) {
		if (is_array($tags)) {
			$this->_tags = $tags;
		} else {
			$this->_tags = array_map('trim', explode(',', $tags));
		}
	}
	
	public function setOrganisationRolesRead($roles) {
		$this->_org_roles_read = $roles;
	}
	
	public function setOrganisationRolesWrite($roles) {
		$this->_org_roles_write = $roles;
	}
}
