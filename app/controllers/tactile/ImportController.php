<?php

require_once 'Zend/Gdata/AuthSub.php';
require_once 'Gdata/Contacts.php';
require_once 'Gdata/Contacts/Query.php';
require_once 'Gdata/Contacts/Feed.php';

require_once 'Gdata/Groups.php';
require_once 'Gdata/Groups/Query.php';
require_once 'Gdata/Groups/Feed.php';
/**
 * Responsible for the managing of uploads and their corresponding forms
 * Forms should have a file-upload and a way of setting the access for the to-be-imported records (companies only)
 * @todo: vcard
 */
class ImportController extends Controller {

	public function __construct($module = null, $view) {
		parent::__construct($module, $view);
		
		$permission_import_enabled = Tactile_AccountMagic::getAsBoolean('permission_import_enabled', 't', 't');
		if (!isModuleAdmin() && !$permission_import_enabled) {
			Flash::Instance()->addError('Contact importing is disabled for non-admin users on your account');
			sendTo();
		}
	}

	/**
	 * Displays the menu of import options available
	 */
	function index() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		$this->view->set('fb_accountname', Tactile_AccountMagic::getValue('freshbooks_account'));
		
		$db = DB::Instance();
		
		if (!isModuleAdmin()) {
			$rolesQuery = 'SELECT roleid FROM hasrole WHERE username = ' . $db->qstr(EGS::getUsername());
			$roles = $tags = $db->GetCol($rolesQuery);
			foreach ($roles as &$roleid) {
				$roleid = $db->qstr($roleid);
			}
			$query = 'SELECT t.name
				FROM tags t
				JOIN tag_map tm ON (t.id = tm.tag_id)
				LEFT JOIN organisations org ON (org.id = tm.organisation_id)
					LEFT JOIN organisation_roles cr ON org.id = cr.organisation_id AND cr.read AND cr.roleid IN ('.implode(', ', $roles).')
				LEFT JOIN people p ON (p.id = tm.person_id)
					LEFT JOIN organisation_roles pcr ON p.organisation_id = pcr.organisation_id AND pcr.read AND pcr.roleid IN ('.implode(', ', $roles).')
				LEFT JOIN opportunities opp ON (opp.id = tm.opportunity_id)
					LEFT JOIN organisation_roles oppcr ON opp.organisation_id = oppcr.organisation_id AND oppcr.read AND oppcr.roleid IN ('.implode(', ', $roles).')
				LEFT JOIN tactile_activities act ON (act.id = tm.activity_id)
					LEFT JOIN organisation_roles actcr ON act.organisation_id = actcr.organisation_id AND actcr.read AND actcr.roleid IN ('.implode(', ', $roles).')
				WHERE t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . '
				AND (
					cr.roleid IS NOT NULL
					OR pcr.roleid IS NOT NULL
					OR oppcr.roleid IS NOT NULL
					OR actcr.roleid IS NOT NULL
					
					OR org.owner = ' . $db->qstr(EGS::getUsername()) . '
					OR p.owner = ' . $db->qstr(EGS::getUsername()) . '
					OR opp.owner = ' . $db->qstr(EGS::getUsername()) . '
					OR act.owner = ' . $db->qstr(EGS::getUsername()) . '
					
					OR org.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
					OR p.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
					OR opp.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
					OR act.assigned_to = ' . $db->qstr(EGS::getUsername()) . '
					
					OR (p.organisation_id IS NULL AND cr.roleid IS NULL AND pcr.roleid IS NULL AND oppcr.roleid IS NULL AND actcr.roleid IS NULL)
					OR (opp.organisation_id IS NULL AND cr.roleid IS NULL AND pcr.roleid IS NULL AND oppcr.roleid IS NULL AND actcr.roleid IS NULL)
					OR (act.organisation_id IS NULL AND cr.roleid IS NULL AND pcr.roleid IS NULL AND oppcr.roleid IS NULL AND actcr.roleid IS NULL)
				)
				AND lower(t.name) LIKE ' . $db->qstr('import%') . ' 
				GROUP BY t.name, t.created
				ORDER BY t.created DESC LIMIT 10';
		} else {
			$query = 'SELECT distinct t.name, t.created from tags t JOIN tag_map tm ON (t.id = tm.tag_id) WHERE t.usercompanyid = ' . $db->qstr(EGS::getCompanyId()) . '
				AND lower(t.name) LIKE ' . $db->qstr('import%') . '
				ORDER BY t.created DESC LIMIT 10';
		}
		
		$import_tags = $db->getArray($query);
		$this->view->set('import_tags', $import_tags);
		
		unset($_SESSION['_csv_import_filename']);
		unset($_SESSION['_csv_selected_fields']);
	}
	
	protected function _suggest_tag() {
		$db = DB::Instance();
		$suggested_tag = 'Import' . date('Ymd');
		$chr = 98;
		$i = 0;
		while (false !== $t_id = $db->getOne('SELECT id FROM tags where name = ' . $db->qstr($suggested_tag) .
			' AND usercompanyid = ' . $db->qstr(EGS::getCompanyId()))) {
			if (chr($chr) == 'z') {
				$suggested_tag = 'Import' . date('Ymd') . chr($chr) . '_' + ($i++);
			} else {
				$suggested_tag = 'Import' . date('Ymd') . chr($chr++);
			}
		}
		return $suggested_tag;
	}
	
	protected function _getCustomFields() {
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		if ($account->is_free() && !$account->in_trial()) {
			return false;
		}
		
		$org = new Tactile_Organisation();
		$org_fields = $org->getCustomFields();
		$this->view->set('organisation_custom_fields', $org_fields);
		$this->view->set('organisation_custom_fields_json', $org_fields->asJson());
		
		$person = new Tactile_Person();
		$person_fields = $person->getCustomFields();
		$this->view->set('person_custom_fields', $person_fields);
		$this->view->set('person_custom_fields_json', $person_fields->asJson());
	}
	
	function setup_function() {
		$db = DB::Instance();
		
		$this->view->set('suggested_tag', $this->_suggest_tag());
		
		// Set defaults
		if(isset($this->_data['type'])) {
			$this->view->set('import_type', $this->_data['type']);
		}
		if(isset($this->_data['file_type'])) {
			$this->view->set('file_type', $this->_data['file_type']);
		}
		
		// Override defaults with POST contents
		$repopulate_vars = array('file_type', 'import_type', 'tags', 'gmail_username');
		foreach ($repopulate_vars as $var) {
			if (isset($_SESSION['_controller_data'][$var])) {
				$this->view->set($var, $_SESSION['_controller_data'][$var]);
			}
		}
		if (isset($_SESSION['_controller_data']['sharing']['read'])) {
			$this->view->set('sharing_read', $_SESSION['_controller_data']['sharing']['read']);
		}
		if (isset($_SESSION['_controller_data']['sharing']['write'])) {
			$this->view->set('sharing_write', $_SESSION['_controller_data']['sharing']['write']);
		}
		
		$roles = Omelette_Role::getRolesAndUsers();
		$this->view->set('roles', $roles);
		unset($_SESSION['_controller_data']);
	}
	
	function csv() {
		$this->setup_function();
		
		// Have we just received an uploaded file?
		if (isset($_FILES['upload_file'])) {
			if ($_FILES['upload_file']['error'] > 0) {
				switch($_FILES['upload_file']['error']) {
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						Flash::Instance()->addError('The file you tried to upload is too big, try splitting it into mutliple smaller files');
						break;
					default:
						Flash::Instance()->addError('There was an error uploading your file.');
						break;
				}			
				sendTo('import','index');
				return;
			}
			
			$filename = $_FILES['upload_file']['tmp_name'];
			$destination = DATA_ROOT . 'tmp/' . basename($filename);
			move_uploaded_file($filename, $destination);
			$_SESSION['_csv_import_filename'] = $destination;
		}
		
		// Catch when we want to return here from step2 (maybe they didn't select a required field)
		if (isset($_SESSION['_csv_import_filename'])) {
			$file = new SPLFileObject($_SESSION['_csv_import_filename']);
			
			// Some typical defaults
			$default_selected_fields = array(
				'title'					=> 'person_title',
				'firstname'				=> 'person_firstname',
				'first name'			=> 'person_firstname',
				'last name'				=> 'person_surname',
				'surname'				=> 'person_surname',
				'job title'				=> 'person_jobtitle',
				'jobtitle'				=> 'person_jobtitle',
				'person description'	=> 'person_description',
				'description'			=> 'person_description',

				'telephone'				=> 'personcontact_phones',				
				'phone'					=> 'personcontact_phones',
				'home phone'			=> 'personcontact_phones',
				'mobile'				=> 'personcontact_mobiles',
				'mobile phone'			=> 'personcontact_mobiles',
				'email'					=> 'personcontact_emails',
				'home email'			=> 'personcontact_emails',
			
				'street'				=> 'personaddress_street1',
				'street1'				=> 'personaddress_street1',
				'street 2'				=> 'personaddress_street2',
				'street2'				=> 'personaddress_street2',
				'street 3'				=> 'personaddress_street3',
				'street3'				=> 'personaddress_street3',
				'city'					=> 'personaddress_town',
				'town'					=> 'personaddress_town',
				'state'					=> 'personaddress_county',
				'county'				=> 'personaddress_county',
				'postal code'			=> 'personaddress_postcode',
				'postcode'				=> 'personaddress_postcode',
				'zip'					=> 'personaddress_postcode',
				'country'				=> 'personaddress_country_code',
			
				'company'				=> 'organisation_name',
				'organisation'			=> 'organisation_name',
				'account number'		=> 'organisation_accountnumber',
				
				'business phone'		=> 'organisationcontact_phones',
				'fax'					=> 'organisationcontact_faxes',
				'business fax'			=> 'organisationcontact_faxes',
				'business email'		=> 'organisationcontact_emails',
				'web page'				=> 'organisationcontact_websites',
			
				'business street'		=> 'organisationaddress_street1',
				'business street 2'		=> 'organisationaddress_street2',
				'business street 3'		=> 'organisationaddress_street3',
				'business city'			=> 'organisationaddress_town',
				'business state'		=> 'organisationaddress_county',
				'business postal code'	=> 'organisationaddress_postcode',
				'business country'		=> 'organisationaddress_country_code'
			);
			
			// Get headings from uploaded file
			$extractor = new OutlookCSVExtractor();
			$csv_headers = array();
			$select_options = array('' => '--');
			$selected_fields = array();
			$csvs = array();
			$unique_headings = array();
			do {
				// Handle blank rows at start of file
				$csvs = $extractor->cleanValue($file->fgetcsv());
				$unique_headings = array_unique($csvs);
			} while (!$file->eof() && (count($unique_headings) === 0 || (count($unique_headings) === 1 && $unique_headings[0] === '')));
			if (count($unique_headings) < 1 || (count($unique_headings) === 1 && $unique_headings[0] === '') || (count($unique_headings) === 1 && strlen($unique_headings[0]) > 100)) {
				Flash::Instance()->addError('We could not find any headings in your CSV file. Please check it is correctly formatted and try again');
				sendTo('import','index');
				return;
			}
			
			foreach ($csvs as $header) {
				array_push($csv_headers, $header);
				if ($header !== '') {
					array_push($select_options, $header);
					
					// Search default list to guess which field this header matches
					if (!isset($_SESSION['_csv_selected_fields'])) {
						if (isset($default_selected_fields[strtolower($header)])) {
							$selected_fields[$default_selected_fields[strtolower($header)]] = array_search($header, $csv_headers);
						}
					}
				} else {
					// Handle blank columns
					array_push($select_options, '--BLANK HEADING--');
				}
			}
			
			if (isset($_SESSION['_csv_selected_fields'])) {
				foreach ($_SESSION['_csv_selected_fields'] as $group_field => $header_index) {
					if ($header_index !== '') {
						$selected_fields[$group_field] = (int) $header_index;						
					}
				}
			}
			
			$tactile_organisation_fields = array(
				'Organisation' => array(
					'Name *'			=> 'organisation_name',
					'Account Number'	=> 'organisation_accountnumber',
					'Web Page'			=> 'organisationcontact_websites',
					'Description'		=> 'organisation_description',
				),
				'Organisation Contact Methods' => array(
					'Phone'				=> 'organisationcontact_phones',	
					'Email'				=> 'organisationcontact_emails',	
					'Fax'				=> 'organisationcontact_faxes'
				),
				'Organisation Address' => array(
					'Street'			=> 'organisationaddress_street1',
					'Street 2'			=> 'organisationaddress_street2',
					'Street 3'			=> 'organisationaddress_street3',
					'City'				=> 'organisationaddress_town',
					'County / State'	=> 'organisationaddress_county',
					'Postal Code / ZIP' => 'organisationaddress_postcode',
					'Country'			=> 'organisationaddress_country_code'
				)
			);
			$tactile_person_fields = array(
				'Person' => array(
					'Title'				=> 'person_title',
					'First Name *'		=> 'person_firstname',
					'Surname *'			=> 'person_surname',
					'Job Title'			=> 'person_jobtitle',
					'Description'		=> 'person_description'
				),
				'Person Contact Methods' => array(
					'Phone'				=> 'personcontact_phones',
					'Email'				=> 'personcontact_emails',
					'Mobile'			=> 'personcontact_mobiles'
				),
				'Person Address' => array(
					'Street 1'			=> 'personaddress_street1',
					'Street 2'			=> 'personaddress_street2',
					'Street 3'			=> 'personaddress_street3',
					'City'				=> 'personaddress_town',
					'County / State'	=> 'personaddress_county',
					'Postal Code / ZIP'	=> 'personaddress_postcode',
					'Country'			=> 'personaddress_country_code'
				)
			);
			
			$this->view->set('csv_headers', $csv_headers);
			$this->view->set('select_options', $select_options);
			$this->view->set('tactile_organisation_fields', $tactile_organisation_fields);
			$this->view->set('tactile_person_fields', $tactile_person_fields);
			$this->view->set('selected_fields', $selected_fields);
			$this->setTemplateName('csv_step2');
			
			$this->_getCustomFields();
		}
	}
	
	function cloud() {
		$this->setup_function();
	}
	
	function vcard() {
		$this->setup_function();
	}
	
	function google() {
		$this->setup_function();
		
		if(!isset($this->_data['token']) && !isset($_SESSION['cp_token'])) {
			$_SESSION['cp_import'] = array(
				'tags'=>isset($this->_data['tags']) ? $this->_data['tags'] : '',
				'import_type'=>isset($this->_data['import_type']) ? $this->_data['import_type'] : '',
				'sharing'=>isset($this->_data['Sharing']) ? $this->_data['Sharing'] : ''
			);
			if(!defined('TACTILE_GDATA_CONTACTS_PROCESSOR_URL')) {
				throw new Exception("Need to define the URL for Google to send people back to: TACTILE_GDATA_CONTACTS_PROCESSOR_URL");
			}
			$googleUri = Zend_Gdata_AuthSub::getAuthSubTokenUri(
	            TACTILE_GDATA_CONTACTS_PROCESSOR_URL.'?return_url='.Omelette::getUserSpace(),
	            Gdata_Contacts::CONTACTS_FEED_URI, 0, 1);
	        $this->view->set('google_auth_url', $googleUri);
		}
		else {
			if(!isset($_SESSION['cp_token'])) {
				// You can convert the single-use token to a session token.
				try {
	       			$session_token =  Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
			        // Store the session token in our session.
			        $_SESSION['cp_token'] = $session_token;
				}
				catch(Zend_Gdata_App_AuthException $e) {
					sendTo('import');
					Flash::Instance()->addError('The token provided is invalid or has expired, please re-authenticate');
					return;
				}
			}
			else {
				$client = new Zend_Gdata_HttpClient();
				Zend_Gdata_AuthSub::getAuthSubTokenInfo($_SESSION['cp_token'], $client);
				if($client->getLastResponse()->getStatus()!== 200) {
					Flash::Instance()->addError("The Auth-Token we have is invalid or has expired, please re-authenticate");
					unset($_SESSION['cp_token']);
					sendTo('import');
					return;
				}
			}
		    $this->view->set('google_token', true);
		    $this->view->set('file_type', 'gdata');
		    if(isset($this->_data['gdata_username'])){

		    	$client = Zend_Gdata_AuthSub::getHttpClient($_SESSION['cp_token']);
				$very_big_number = 10000;
				
				//this puts together the URL parts that make up the request
				$query = new Gdata_Groups_Query();				
				$query->setUsername($this->_data['gdata_username']);		
				$query->setMaxResults($very_big_number);
				
				$g = new Gdata_Groups($client);
				
				try {
					$feed = $g->getGroupsFeed($query);
				}
				catch(Zend_Gdata_App_HttpException $e) {
					Flash::Instance()->addError("There was a problem authenticating with Google, please try again");
					$this->logger->err($e->getMessage());
					unset($_SESSION['cp_token']);
					sendTo('import','google');
					return;
				}
				
				$xml = $client->getLastResponse()->getBody();

				$groups = array('all'=>'All');
				while($feed->valid()){
					$entry =  $feed->current();
					$groups[$entry->id->text]=$entry->title->text;
					$feed->next();
				}
				$this->view->set('groups',$groups);
				$this->view->set('gdata_username',$this->_data['gdata_username']);
				
		    }
		}
	}
	
	function freshbooks() {
		$this->setup_function();
	}
	
	/**
	 * Carries out an import of CSV data, as exported from Outlook, into the company and person tables
	 * 
	 * For more than 50 rows, we don't want to do it now so we'll add it to a queue
	 */
	function upload() {
		// Save POSTed variables to session
		$this->saveData();
		
		if (!isset($this->_data['file_type'])) {
			Flash::Instance()->addError('File type not set, you have probably come here in error.');
			sendTo('import','index');
			return;
		}
		
		$filename = "";
		switch ($this->_data['file_type']) {
			case 'csv': {
				if (!isset($this->_data['CSVField'])) {
					Flash::Instance()->addError('No CSV mappings found.');
					sendTo('import','csv');
					return;
				}
				
				if (!isset($_SESSION['_csv_import_filename']) || $_SESSION['_csv_import_filename'] == "") {
					Flash::Instance()->addError('CSV file not found.');
					sendTo('import','index');
					return;
				}
				$filename = $_SESSION['_csv_import_filename'];
				
				$csv_mappings = array(
					'person'				=> array(),
					'personaddress'			=> array(),
					'personcontact'			=> array(),
					'personcustom'			=> array(),
					'organisation'			=> array(),
					'organisationaddress'	=> array(),
					'organisationcontact'	=> array(),
					'organisationcustom'	=> array()
				);
				foreach ($this->_data['CSVField'] as $group_field => $header_index) {
					if ($header_index != '') {
						$split = split('_', $group_field, 2);
						$group = $split[0];
						$field = $split[1];
						$csv_mappings[$group][$field] = $header_index;
					}
				}
				
				$errors = array();
				// Check for required fields in person
				if (count($csv_mappings['person']) != 0) {
					$required_fields = array(
						'firstname' => 'Person Firstname',
						'surname' => 'Person Surname'
					);
					foreach ($required_fields as $field => $title) {
						if (array_search($field, array_keys($csv_mappings['person'])) === false) {
							$errors[] = $title . ' is a required field';
						}
					}
				}
				// Check for required fields in organisation
				if (count($csv_mappings['organisation']) != 0) {
					$required_fields = array(
						'name' => 'Organisation Name'
					);
					foreach ($required_fields as $field => $title) {
						if (array_search($field, array_keys($csv_mappings['organisation'])) === false) {
							$errors[] = $title . ' is a required field';
						}
					}
				}
				if (count($errors) > 0) {
					array_unshift($errors, 'Some required fields were missing, please check and try again.');
					Flash::Instance()->addErrors($errors);
					$_SESSION['_csv_selected_fields'] = $this->_data['CSVField'];
					sendTo('import','csv');
					return;
				}
				
				// Unset the session variables so that the next import by the user isn't messed up
				unset($_SESSION['_csv_import_filename']);
				unset($_SESSION['_csv_selected_fields']);
				break;
			}
			
			case 'cloud': // Fall through
			case 'vcf': {
				if(!isset($_FILES['upload_file']) || $_FILES['upload_file']['error'] > 0) {
					switch($_FILES['upload_file']['error']) {
						case UPLOAD_ERR_INI_SIZE:
						case UPLOAD_ERR_FORM_SIZE:
							Flash::Instance()->addError('The file you tried to upload is too big, try splitting it into mutliple smaller files');
							break;
						default:
							Flash::Instance()->addError('There was an error uploading your file.');
							break;
					}			
					sendTo('import','index');
					return;
				}
				$filename = $_FILES['upload_file']['tmp_name'];
				break;
			}
			
			case 'gdata': {
				if(!isset($_SESSION['cp_token'])) {
					Flash::Instance()->addError("You need to authenticate with Google");
					sendTo('import', 'index');
					return;
				}
				if(empty($this->_data['gdata_username'])) {
					Flash::Instance()->addError("Please provide the gmail username that you authenticated with");
					sendTo('import');
					return;
				}
				// Create an authenticated HTTP Client to talk to Google.
				/* @var $client Zend_Http_Client */
				$client = Zend_Gdata_AuthSub::getHttpClient($_SESSION['cp_token']);
				
				$very_big_number = 10000000;
				
				//this puts together the URL parts that make up the request
				$query = new Gdata_Contacts_Query();				
				$query->setUsername($this->_data['gdata_username']);		
				$query->setMaxResults($very_big_number);
				$query->setSortOrder(Gdata_Contacts_Query::SORT_DESCENDING);
				$query->setOrderBy('lastmodified');
				
				if($this->_data['gdata_group']!='all'){
					$query->setGroup($this->_data['gdata_group']);
				}
				
				$g = new Gdata_Contacts($client);
				
				try {
					$feed = $g->getContactsFeed($query);
				}
				catch(Zend_Gdata_App_HttpException $e) {
					Flash::Instance()->addError("There was a problem authenticating with Google, please try again: " . $e->getMessage());
					unset($_SESSION['cp_token']);
					sendTo('import');
					return;
				}
				/*
				$doc = new DOMDocument();
				$dom = $feed->getDOM($doc);
				
				$xml = $doc->saveXML($dom);
				*/
				//not using the $feed->getDOM() method at the moment as it's having problems
				//with the 'organization' node, seems to always put it in the 'default' namespace rather than 'gd'
				$xml = $client->getLastResponse()->getBody();
				$filename = tempnam(DATA_ROOT.'tmp', 'gdata');
				$fp = fopen($filename, 'w+');
				fwrite($fp, $xml);
				fclose($fp);
				chmod($filename, 0666);
				break;
			}
			
			case 'freshbooks': {
				require_once 'Service/Freshbooks.php';
				$account = CurrentlyLoggedInUser::Instance()->getAccount();
				$fb = new Service_Freshbooks(Tactile_AccountMagic::getValue('freshbooks_account'), Tactile_AccountMagic::getValue('freshbooks_token'));
				
				$query = $fb->newClientQuery('list');
				
				$all_clients = array();
				
				$fb = new Service_Freshbooks(Tactile_AccountMagic::getValue('freshbooks_account'), Tactile_AccountMagic::getValue('freshbooks_token'));
				$query = $fb->newClientQuery('list');
				$query->addParam('per_page', 50);
				$response = $fb->execute($query);
			
				$clients = $response->getClients();
				
				$filename = tempnam(DATA_ROOT.'tmp', 'freshbooks');
				$fp = fopen($filename, 'w+');
				fwrite($fp, $fb->getLastResponse()->getBody());
				fclose($fp);
				chmod($filename, 0666);
				break;
			}
			
			case 'shoeboxed': {
				require_once 'Service/Shoeboxed.php';
				$account = CurrentlyLoggedInUser::Instance()->getAccount();
				$return_params = array(
					'file_type'		=> 'shoeboxed',	
					'tags'			=> $this->_data['tags'],
					'sharing[read]'	=> $this->_data['sharing']['read'],
					'sharing[write]'=> $this->_data['sharing']['write']
				);
				$apparams = http_build_query($return_params);
				
				$sb = new Service_Shoeboxed(SHOEBOXED_TOKEN, SHOEBOXED_APPNAME, SHOEBOXED_APPURL, $apparams);
				
				// Have we arrived here from Shoeboxed?
				if (!empty($this->_data['tkn']) && !empty($this->_data['uname'])) {
					// User has authenticated with SB already, so let's grab some data
					$sb->setUserToken($this->_data['uname'], $this->_data['tkn']);
					try {
						$xml = $sb->getCards();
						if (false === $xml) {
							Flash::Instance()->addError("There was an error fetching business cards from Shoeboxed");
							$error = $sb->getError();
							if (!empty($error)) {
								Flash::Instance()->addError('Shoeboxed: ' . $error);
							}
							sendTo('import');
							return;
						}
						
					} catch (Service_Shoeboxed_Exception $e) {
						Flash::Instance()->addError("There was a problem communicating with Shoeboxed, please try again");
						sendTo('import');
						return;
					}
					
					// Save the grabbed data to a temporary file
					$filename = tempnam(DATA_ROOT.'tmp', 'shoeboxed');
					$fp = fopen($filename, 'w+');
					fwrite($fp, $xml);
					fclose($fp);
					chmod($filename, 0666);
					
				} else {
					// Send user off to shoeboxed, who will send them back
					header("Location: " . $sb->getAuthUri());
					return;
				}
				break;
			}
		}
		
		$importer = new ContactImporter($filename);
		$importer->setLogger($this->logger);
		switch ($this->_data['file_type']) {
			case 'vcf':
				$importer->setExtractor(new VCardExtractor());
				break;
			case 'gdata':
				$importer->setExtractor(new GDataExtractor($feed));	//pass it the feed to save re-parsing
				break;
			case 'freshbooks':
				$importer->setExtractor(new FreshbooksExtractor($response));
				break;
			case 'cloud':
				$importer->setExtractor(new OutlookCSVExtractor());
				break;
			case 'csv':
				$importer->setExtractor(new OutlookCSVExtractor($csv_mappings));
				break;
			case 'shoeboxed':
				$importer->setExtractor(new ShoeboxedExtractor());
			default:
				break;
		}
		
		// Add suggested import tag to list of tags by default
		$suggested_tag = $this->_suggest_tag();
		$this->_data['tags'] = !empty($this->_data['tags']) ?
			($suggested_tag . ', ' . $this->_data['tags']) : $suggested_tag;
		
		// Determine if import operation can be completed in a reasonable time
		try {
			$count = $importer->countRecords();
		} catch (Exception $e) {
			Flash::Instance()->addError("There was a problem trying to read from the contact data. Please check your source and try again.");
			sendTo('import');
			return;
		}
		$flash = Flash::Instance();
		if ($count < 1) {
			$flash->addError("Zero records found, please check your source is valid and try again");
			sendTo('import');
			return;
		}
		
		// Permissions
		$user_model = CurrentlyLoggedInUser::Instance()->getModel();
		if ($user_model->hasFixedPermissions()) {
			$sharing = array('read' => $user_model->getDefaultPermissions('read'), 'write' => $user_model->getDefaultPermissions('write'));
		} else {
			$sharing = !empty($this->_data['sharing']) ? $this->_data['sharing'] : array('read' => 'everyone', 'write' => 'everyone');
		}
		
		if($count > 50) {
			$flash->addMessage('The number of contacts in this import is quite large,
				so rather than make you wait we\'ll send you an email when it\'s completed');
			$task = new DelayedContactImport();
			
			$destination = DATA_ROOT . 'jobfiles/' . basename($filename);
			switch($this->_data['file_type']) {
				case 'csv':
					$task->setCSVFieldMapping($csv_mappings);
					rename($filename, $destination);
					chmod($destination, 0666);
					break;
				case 'cloud': // Fall through
				case 'vcf':					
					move_uploaded_file($filename, $destination);
					chmod($destination, 0666);
					break;
				case 'gdata': // Fall through
				case 'freshbooks':
				case 'shoeboxed':
					rename($filename, $destination);
					chmod($destination, 0666);
					break;
			}
			
			$task->setFile($destination);
			$task->setSharing($sharing);
			$task->setTags($this->_data['tags']);
			$task->setFileType($this->_data['file_type']);
			$task->save();
			sendTo('companys', 'index', 'contacts');
			return;
			
		} else {
			// Prepare the lists of people and companies to import
			$importer->prepare();
			
			$sharing = Omelette_OrganisationRoles::normalize($sharing);
			$importer->setOrganisationRolesRead($sharing['read']);
			$importer->setOrganisationRolesWrite($sharing['write']);
			$importer->setTags($this->_data['tags']);
			
			$successful_import = $importer->import($errors);
			if($successful_import) {
				$msg = $importer->num_records_imported() . ' records successfully imported';
				if ($importer->num_records_with_errors() > 0) {
					$msg .= ' with ' . $importer->num_records_with_errors() . ' records not imported due to errors';
				}
				
				$flash->clearMessages();
				$flash->addMessage($msg);
				sendTo('tags', 'by_tag', null, array('tag[0]'=>$suggested_tag));
				
				if($this->_data['file_type'] == 'gdata') {
					try {
						Zend_Gdata_AuthSub::AuthSubRevokeToken($_SESSION['cp_token']);
						unset($_SESSION['cp_token']);
					}
					catch(Zend_Gdata_App_AuthException $e) {
						//if revoking fails it's because it's invalid/expired, and so we don't care
					}
				}				
				return;
			}
			else {
				$flash->addError('A problem occurred during your import');
				sendTo('import', 'index');
			}
		}
	}
	
	function google_old() {
		require_once 'Zend/Gdata/AuthSub.php';
		require_once 'Gdata/Contacts.php';
		require_once 'Gdata/Contacts/Query.php';
		require_once 'Gdata/Contacts/Feed.php';
		
		if(!isset($_SESSION['cp_token'])) {
			if(!isset($this->_data['token'])) {
				$_SESSION['cp_import'] = array(
					'tags'=>isset($this->_data['tags']) ? $this->_data['tags'] : '',
					'import_type'=>isset($this->_data['import_type']) ? $this->_data['import_type'] : '',
					'sharing'=>isset($this->_data['Sharing']) ? $this->_data['Sharing'] : ''
				);
				$googleUri = Zend_Gdata_AuthSub::getAuthSubTokenUri(
		            TACTILE_GDATA_CONTACTS_PROCESSOR_URL.'?return_url='.urlencode(SERVER_ROOT.'/import/google'),
		            Gdata_Contacts::CONTACTS_FEED_URI, 0, 1);
		        header("Location: ".$googleUri);
		        return;
			}
			else {
				// You can convert the single-use token to a session token.
	       		$session_token =  Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
		        // Store the session token in our session.
		        $_SESSION['cp_token'] = $session_token;
			}
		}
		
		// Create an authenticated HTTP Client to talk to Google.
		/* @var $client Zend_Http_Client */
		$client = Zend_Gdata_AuthSub::getHttpClient($_SESSION['cp_token']);
		
		$g = new Gdata_Contacts($client);
		
		$very_big_number = 10000000;
		
		$query = new Gdata_Contacts_Query('http://www.google.com/m8/feeds/contacts/greg.jones@gmail.com/base');
		
		$query->setMaxResults($very_big_number);
		$query->setSortOrder(Gdata_Contacts_Query::SORT_DESCENDING);
		$query->setOrderBy('lastmodified');
		
		$feed = new Gdata_Contacts_Feed();
		
		//$feed->transferFromXML(file_get_contents(FILE_ROOT.'exampleFeed.xml'));
		
		$feed = $g->getContactsFeed($query);
		
		
		if(false) {
			$doc = new DOMDocument();
			$dom = $feed->getDOM($doc);
			$filename = tempnam(DATA_ROOT.'jobfiles');
			$fp = fopen($filename, 'w+');
			fwrite($fp, $doc->saveXML($dom));
			fclose($fp);
			$task = new DelayedContactImport();
			$task->setFile($filename);
			$task->setFileType('gdata');
			$task->setTags($_SESSION['cp_import']['tags']);
			$task->setSharing($_SESSION['cp_import']['sharing']);
			$task->save();
		} else {
			$people = array();
			$companies = array();
			
			/* @var $entry Gdata_Contacts_Entry */
			foreach($feed->entries as $entry) {
				$person_data = array();
				
				$fullname = $entry->title->text;
				$splitname = explode(' ',$fullname,2);
				if(count($splitname) < 2) {
					$people[] = null;
					continue;	//can't continue without enough name parts
				}
				$person_data['firstname'] = $splitname[0];
				$person_data['surname'] = $splitname[1];
				
				$emails = $entry->emails;
				foreach($emails as $email) {
					$email_data = array(
						'contact' => $email->address,
						'name' => ucfirst(str_replace($feed->lookupNamespace('gd').'#','',$email->rel))
					);
					if($email->primary) {
						$email_data['main'] = true;
					}
					if(!isset($person_data['emails'])) {
						$person_data['emails'] = array();
					}
					$person_data['emails'][] = $email_data;
				}
				
				$numbers = $entry->phoneNumbers;
				foreach($numbers as $number) {
					$phone_data = array(
						'contact' => $number->text
					);
					if($number->primary) {
						$phone_data['main'] = true;
					}
					if(!isset($person_data['phones'])) {
						$person_data['phones'] = array();
					}
					$person_data['phones'][] = $phone_data;
				}
				$org = $entry->organization;
				if(!is_null($org)) {
					$orgName = $org->orgName->text;
					$companies[] = $orgName;

					$jobTitle = $org->orgTitle->text;
					$person_data['jobtitle'] = $jobTitle;
				}
				else {
					$companies[] = null;
				}
				$people[] = $person_data;
			}
			$company_type=  'Organisation';
			$company_model = DataObject::Construct($company_type);
			$cc = new ConstraintChain();
				
			$existing_companies = array_flip($company_model->getAll($cc, true));
			
			$saver = new ModelSaver();
			$errors = array();
			foreach($companies as $i=>$company) {
				$row_errors = array();
				if(is_null($company)) {
					continue;
				}
				if(isset($existing_companies[$company])) {
					$people[$i]['organisation_id'] = $existing_companies[$company];
				}
				else {
					$company = $saver->save(array('name'=>$company),$company_type, $row_errors);
					if($company !== false) {
						$companies[$company->name] = $company->id;
						$people[$i]['organisation_id'] = $company->id;
					}
				}
				if(count($row_errors) > 0) {
					$errors[$i] = $row_errors;
				}
			}
			
			foreach($people as $i=>$person_data) {
				$row_errors = array();
				if($person_data === null) {
					continue;
				}
				
				$person = $saver->save($person_data, 'Person', $row_errors);
				if($person !== false) {
					if(isset($person_data['emails'])) {
						foreach($person_data['emails'] as $email_data) {
							$email_data['type'] = 'E';
							$email_data['person_id'] = $person->id;
							$saver->save($email_data, 'Personcontactmethod', $row_errors);
						}
					}
					if(isset($person_data['phones'])) {
						foreach($person_data['phones'] as $email_data) {
							$email_data['type'] = 'T';
							$email_data['person_id'] = $person->id;
							$saver->save($email_data, 'Personcontactmethod', $row_errors);
						}
					}
					$this->person_ids[$i] = $person->id;
				}
				if (count($row_errors)>0) {
					if (!isset($errors[$i])) {
						$errors[$i]=array();
					}
					$errors[$i] = array_merge($errors[$i],$row_errors);
				}
			}
		}
	}
	
	function shoeboxed() {
		$this->setup_function();
		/*
		if(!isset($this->_data['token']) && !isset($_SESSION['cp_token'])) {
			$_SESSION['cp_import'] = array(
				'tags'=>isset($this->_data['tags']) ? $this->_data['tags'] : '',
				'import_type'=>isset($this->_data['import_type']) ? $this->_data['import_type'] : '',
				'sharing'=>isset($this->_data['Sharing']) ? $this->_data['Sharing'] : ''
			);
			if(!defined('TACTILE_GDATA_CONTACTS_PROCESSOR_URL')) {
				throw new Exception("Need to define the URL for Google to send people back to: TACTILE_GDATA_CONTACTS_PROCESSOR_URL");
			}
			$googleUri = Zend_Gdata_AuthSub::getAuthSubTokenUri(
	            TACTILE_GDATA_CONTACTS_PROCESSOR_URL.'?return_url='.Omelette::getUserSpace(),
	            Gdata_Contacts::CONTACTS_FEED_URI, 0, 1);
	        $this->view->set('google_auth_url', $googleUri);
		}
		else {
			if(!isset($_SESSION['cp_token'])) {
				// You can convert the single-use token to a session token.
				try {
	       			$session_token =  Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
			        // Store the session token in our session.
			        $_SESSION['cp_token'] = $session_token;
				}
				catch(Zend_Gdata_App_AuthException $e) {
					sendTo('import');
					Flash::Instance()->addError('The token provided is invalid or has expired, please re-authenticate');
					return;
				}
			}
			else {
				$client = new Zend_Http_Client();
				Zend_Gdata_AuthSub::getAuthSubTokenInfo($_SESSION['cp_token'], $client);
				if($client->getLastResponse()->getStatus()!== 200) {
					Flash::Instance()->addError("The Auth-Token we have is invalid or has expired, please re-authenticate");
					unset($_SESSION['cp_token']);
					sendTo('import');
					return;
				}
			}
		    $this->view->set('google_token', true);
		    $this->view->set('file_type', 'gdata');
		}*/
	}
	
	function highrise(){
		$user = CurrentlyLoggedInUser::Instance();
		if(!$user->isAdmin()){
			Flash::Instance()->addError('You must be an admin to import Highrise data.');
			sendTo('companys', 'index', 'contacts');
			return;
		}
		
		$this->setup_function();
	
	}
	
	function highrise_users(){	
		$user = CurrentlyLoggedInUser::Instance();
		if(!$user->isAdmin()){
			Flash::Instance()->addError('You must be an admin to import Highrise data.');
			sendTo('companys', 'index', 'contacts');
			return;
		}
				
		if((empty($this->_data['username']) || empty($this->_data['password']) || empty($this->_data['site'])) && !isset($_SESSION['_hr_username'])){
			$this->view->set('error','Please complete all fields.');
			Flash::Instance()->addError('Please complete all the fields.');
			sendTo('import','highrise');
			return;
		} 	
		
		if(isset($this->_data['user'])){
			$task = new DelayedHighriseImporter();
			
			// Add suggested import tag to list of tags by default
			$suggested_tag = $this->_suggest_tag();
			$this->_data['tags'] = !empty($this->_data['tags']) ?
				($suggested_tag . ', ' . $this->_data['tags']) : $suggested_tag;
			
			$task->setTags($this->_data['tags']);
			$task->credentials($_SESSION['_hr_site'],$_SESSION['_hr_username'],$_SESSION['_hr_password']);
			$task->setUsers($this->_data['user']);
			$task->setTypes($this->_data['types']);
			$task->save();
			Flash::Instance()->addMessage('We will run the Highrise import in the background and email you once it\'s complete.');
			sendTo('companys', 'index', 'contacts');
			return;
		}

		$this->setup_function();
		require_once('Service/Highrise.php');
		require_once('Service/Highrise/Collection/Users.php');
		
	
		
		$service = new Service_Highrise($this->_data['site'],$this->_data['username'],$this->_data['password']);
		Service_Highrise_Collection::setDefaultService($service);
		$users = new Service_Highrise_Collection_Users();
		$users->fetchAll();
		
		
		if(count($users) < 1){
			Flash::Instance()->addError('Your Highrise login details are incorrect, please try again.');
			sendTo('import','highrise');
			return;
		}
		
		$this->view->set('highrise_users',$users);

		$user = new Omelette_User();
		$this->view->set('tactile_users', $user->getAll());
		
		$ops = new Opportunitystatus();
		$this->view->set('types',$ops->getAll());
		
		$_SESSION['_hr_username'] = $this->_data['username'];
		$_SESSION['_hr_password'] = $this->_data['password'];
		$_SESSION['_hr_site'] = $this->_data['site'];
		
	}
	
}
