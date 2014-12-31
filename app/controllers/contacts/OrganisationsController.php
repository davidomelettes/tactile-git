<?php
require_once 'Zend/Loader.php';

/**
 * Controller for Organisations (Companies)
 * @author gj
 */
class OrganisationsController extends Controller {

	/**
	 * @var Tactile_Organisation
	 */
	protected $organisation;
	
	
	function __construct($module,$view) {
		parent::__construct($module,$view);
		$this->uses('Organisation');
		$this->mixes('save_contact','ContactMethodActions',array('Tactile_Organisationcontactmethod'));
		$this->mixes('save_contact_multi','ContactMethodActions',array('Tactile_Organisationcontactmethod', 'OrganisationcontactmethodCollection'));
		$this->mixes('save_custom_multi','CustomFieldActions',array('CustomfieldMap', 'CustomfieldMapCollection'));
		$this->mixes('delete_custom','CustomFieldActions',array('CustomfieldMap', 'CustomfieldMapCollection'));
		$this->mixes('delete_contact','ContactMethodActions',array('Tactile_Organisationcontactmethod'));
		$this->mixes('contact_methods', 'ContactMethodActions', array('Organisation', 'OrganisationcontactmethodCollection', 'organisation_id'));
		$this->mixes('save_address','AddressSaver',array('Tactile_Organisationaddress', 'organisation_id'));
		$this->mixes('delete_address','AddressSaver',array('Tactile_Organisationaddress', 'organisation_id'));
		$this->mixes('opportunities','RelatedItemsLoader',array('opportunities','Organisation'),'get_related');
		$this->mixes('activities','RelatedItemsLoader',array('activities','Organisation'),'get_related');
		$this->mixes('people','RelatedItemsLoader',array('people','Organisation'),'get_related');

		$this->mixes('save_note','NoteSaver',array());
		
		$this->mixes('add_tag','TagHandler',array('Organisation'));
		$this->mixes('remove_tag','TagHandler',array('Organisation'));
		$this->mixes('by_tag','TagHandler',array('Tactile_Organisation','organisations'));
		
		$this->mixes('recently_viewed','RecentlyViewedHandler',array('Tactile_Organisation','organisations'));
		
		$this->mixes('files','RelatedItemsLoader',array('s3_files','Organisation'),'get_related');
		$this->mixes('new_file','S3FileHandler',array('Organisation','organisations'));
		$this->mixes('save_file','S3FileHandler',array('Organisation',null,'organisations'));
		
		$this->mixes('add_activity_track', 'ActivityTrackAdder', array('Tactile_Organisation', 'organisation_id', 'organisations'));
		$this->mixes('save_activity_track', 'ActivityTrackAdder', array('Tactile_Organisation', 'organisation_id', 'organisations'));
		
		$cons = array();
		$this->mixes('by_status', 'FieldFilter',
			array('Omelette_OrganisationCollection', 'status_id', 'clients', 'By Status',
				$cons), 'by_field');
		$this->mixes('by_source', 'FieldFilter',
			array('Omelette_OrganisationCollection', 'source_id', 'clients', 'By Source',
				$cons), 'by_field');
		$this->mixes('by_classification', 'FieldFilter',
			array('Omelette_OrganisationCollection', 'classification_id', 'clients', 'By Classification',
				$cons), 'by_field');
		$this->mixes('by_rating', 'FieldFilter',
			array('Omelette_OrganisationCollection', 'rating_id', 'clients', 'By Rating',
				$cons), 'by_field');
		$this->mixes('by_industry', 'FieldFilter',
			array('Omelette_OrganisationCollection', 'industry_id', 'clients', 'By Industry',
				$cons), 'by_field');
		$this->mixes('by_type', 'FieldFilter',
			array('Omelette_OrganisationCollection', 'type_id', 'clients', 'By Type',
				$cons), 'by_field');
		$this->mixes('by_town', 'FieldFilter',
			array('Omelette_OrganisationCollection', 'a.town', 'clients', 'From the Town',
				$cons), 'by_field');
		$this->mixes('by_county', 'FieldFilter',
			array('Omelette_OrganisationCollection', 'a.county', 'clients', 'From the County',
				$cons), 'by_field');
		
		$this->mixes('export', 'ExportHandler', array('organisation', 'organisations'));
		$this->mixes('mass_action', 'MassActionHandler', array('Tactile_Organisation', 'organisations'));
	}

	public function getOrganisation() {
		return $this->organisation;
	}
	
	protected function _getTimeline($page=1) {
		$timeline = new Timeline();
		$cc = new ConstraintChain();
		$cc->add(new Constraint('t.organisation_id', '=', $this->organisation->id));
		
		$timeline->addType('note');
		$timeline->addType('email');
		$timeline->addType('flag');
		$timeline->addType('s3file');
		$timeline->addType('opportunity');
		$timeline->addType('new_activity');
		$timeline->addType('completed_activity');
		$timeline->addType('overdue_activity');
		
		$timeline->load($cc, $page);
		
		$this->view->set('current_query', 'id='.$this->organisation->id);
		$this->view->set('cur_page', $timeline->cur_page);
		$this->view->set('num_pages', $timeline->num_pages);
		$this->view->set('per_page', $timeline->per_page);
		$this->view->set('num_records', $timeline->total);
		
		$this->view->set('timeline_rss', CurrentlyLoggedInUser::Instance()->getTimelineFeedAddress() . '&amp;organisation_id='.$this->organisation->id);
		return $timeline;
	}
	
	protected function _getContactMethods() {
		$org = $this->organisation;
		
		$methods = new OrganisationcontactmethodCollection();
		$sh = new SearchHandler($methods, false);
		$sh->addConstraint(new Constraint('organisation_id', '=' , $org->id));
		$sh->setOrderby('position, main desc, name');
		$methods->load($sh);
		return $methods;
	}
	
	protected function _getAddresses() {
		$org = $this->organisation;
		
		$addresses = new Tactile_OrganisationaddressCollection();
		$sh = new SearchHandler($addresses, false);
		$sh->addConstraint(new Constraint('organisation_id', '=' , $org->id));
		$sh->setOrderby('main desc, name');
		$addresses->load($sh);
		return $addresses;
	}
	
	protected function _getCustomFields() {
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		if ($account->is_free() && !$account->in_trial()) {
			return false;
		}
		
		$org = $this->organisation;
		
		$customfieldsCollection = $org->getCustomFields();
		$this->view->set('custom_fields', $customfieldsCollection);
		$this->view->set('custom_fields_json', $customfieldsCollection->asJson());
		
		$customfieldsMapCollection = $org->getCustomValues();
		$this->view->set('custom_fields_map', $customfieldsMapCollection);
		$this->view->set('existing_custom_fields_json', $customfieldsMapCollection->asJson());
	}
	
	function view() {
		/* @var $org Tactile_Organisation */
		$org = $this->organisation;
		if (!isset($this->_data['id']) || false === $org->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid id specified');
			sendTo('organisations');
			return;
		}
		if (!$org->canView() && !isModuleAdmin()) {
			Flash::Instance()->addError('You don\'t have permission to view that Organisation');
			sendTo('organisations');
			return;
		}
		
		if (!$this->view->is_json) {
			$this->_getCustomFields();
			
			$fields = array(
				'accountnumber',
				'parent',
				'company_status'		=> 'Status',
				'company_source'		=> 'Source',
				'company_classification'=> 'Classification',
				'company_rating'		=> 'Rating',
				'company_industry'		=> 'Industry',
				'company_type'			=> 'Type',
				'assigned_to'
			);
			$summary_groups = array($fields);
			$this->view->set('summary_groups', $summary_groups);
			$view_summary_stats = Omelette_Magic::getAsBoolean('view_summary_stats', EGS::getUsername(), 't', 't');
			$view_summary_info = Omelette_Magic::getAsBoolean('view_summary_info', EGS::getUsername(), 't', 't');
			$view_recent_activity = Omelette_Magic::getAsBoolean('view_recent_activity', EGS::getUsername(), 't', 't');
			$this->view->set('view_summary_stats', $view_summary_stats);
			$this->view->set('view_summary_info', $view_summary_info);
			$this->view->set('view_recent_activity', $view_recent_activity);
			
			$this->view->set('head_title', $org->getFormatted('name'));
			$page = !empty($this->_data['timeline_page']) ? ((int)$this->_data['timeline_page']) : 1;
			$this->view->set('activity_timeline', $this->_getTimeline($page));
			$this->view->set('timeline_view', Omelette_Magic::getValue('timeline_view', EGS::getUsername(), 'list'));
			$account = CurrentlyLoggedInUser::Instance()->getAccount();
			$this->view->set('show_invoices', $account->isFreshbooksEnabled());
			$this->view->set('show_zendesk', $account->isZendeskEnabled());
			$this->view->set('show_resolve', $account->isResolveEnabled());
			$this->view->set('logo_url', $org->getLogoUrl());
			$this->view->set('pipeline', $org->getPipelineDetails());
			$this->view->set('closetime', $org->getTimeToClose());
			$this->view->set('winrate', $org->getWinRate());
			ViewedPage::createOrUpdate(ViewedPage::TYPE_ORGANISATION, $this->organisation->id, EGS::getUsername(), $this->organisation->name);
			$this->view->set('contact_methods', $this->_getContactMethods());
			$this->view->set('addresses', $this->_getAddresses());
			
			$track = new ActivityTrack();
			$this->view->set('activity_tracks', $track->getAll());
		}
	}

	function timeline() {
		if (!$this->view->is_json) {
			sendTo('organisations');
			return;
		}
		$org = $this->organisation;
		if (!isset($this->_data['id']) || false === $org->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid id specified');
			sendTo('organisations');
			return;
		}
		if (!$org->canView()) {
			Flash::Instance()->addError('You don\'t have permission to view that organisation');
			sendTo('organisations');
			return;
		}
		$page = !empty($this->_data['page']) ? ((int)$this->_data['page']) : 1;
		
		$this->view->set('activity_timeline', $this->_getTimeline($page));
	}
	
	function options() {
		if (!$this->view->is_json) {
			sendTo('organisations');
			return;
		}
		$options = array(
			'assigned_to'			=> array(),
			'status'				=> array(),
			'source'				=> array(),
			'classification'		=> array(),
			'rating'				=> array(),
			'industry'				=> array(),
			'type'					=> array(),
			'country_code'			=> array(),
			'default_country_code'	=> EGS::getCountryCode(),
			'sharing'				=> array(
				'read' => array(
					'everyone' => array(),
					'private' => array()
				),
				'write' => array(
					'everyone' => array(),
					'private' => array()
				),
			)
		);
		
		$user = new Omelette_User();
		foreach ($user->getAll() as $key => $value) {
			$options['assigned_to'][$value] = $value;
		}
		$status = new CompanyStatus();
		foreach ($status->getAll() as $key => $value) {
			$options['status'][$key] = $value;
		}
		$source = new CompanySource();
		foreach ($source->getAll() as $key => $value) {
			$options['source'][$key] = $value;
		}
		$classification = new CompanyClassification();
		foreach ($classification->getAll() as $key => $value) {
			$options['classification'][$key] = $value;
		}
		$rating = new CompanyRating();
		foreach ($rating->getAll() as $key => $value) {
			$options['rating'][$key] = $value;
		}
		$industry = new CompanyIndustry();
		foreach ($industry->getAll() as $key => $value) {
			$options['industry'][$key] = $value;
		}
		$type = new CompanyType();
		foreach ($type->getAll() as $key => $value) {
			$options['type'][$key] = $value;
		}
		$country = new Country();
		foreach ($country->getAll() as $key => $value) {
			$options['country_code'][$key] = $value;
		}
		$role = new Role();
		foreach ($role->getAll() as $key => $value) {
			$options['sharing']['read']['private'][$key] = $value;
			$options['sharing']['write']['private'][$key] = $value;
		}
	
		$this->view->set('options_json', json_encode($options));
	}
	
	function index() {
		if ($this->view->is_json) {
			$org_list_type = 'alphabetical';
		} else {
			$org_list_type = Omelette_Magic::getValue('organisations_index_restriction', EGS::getUsername(), 'alphabetical');
		}
		switch($org_list_type) {
			case 'alphabetical':
			case 'recent':
			case 'mine':
			case 'recently_viewed':
				break;
			default:
				$org_list_type = 'alphabetical';
				break;
		}
		$this->$org_list_type();
		
		UsageWarningHelper::displayUsageWarning($this->view, 'contacts');
	}
	
	function search() {
		$this->useRestriction('alphabetical');
		$this->view->set('sub_title', 'Matching Search Query');
	}
	
	private function useRestriction($name) {
		$clients = new Omelette_OrganisationCollection($this->organisation);
		$sh = new SearchHandler($clients,false);
		$sh->extract();
		$this->view->set('restriction',$name);
		$sort_order = 'alphabetical';
		switch($name) {
			case 'recent':
				$sh->setOrderby('org.created','desc');
				$sort_order = 'date';
				break;
			case 'mine':
				$this->view->set('restriction','mine');
				$sh->addConstraint(new Constraint('assigned_to','=',EGS::getUsername()));
				break;
			case 'alphabetical':	//fall through
			default:
				$this->view->set('restriction','alphabetical');
				$sh->setOrderby('name');
				break;
		}
		$this->view->set('sort_order', $sort_order);
		$this->view->set('sort_field', $sh->getOrderBy());
		
		$this->_handleSearchFields($sh);
		Controller::index($clients,$sh);
		$this->setTemplateName('index');
		
		if(!$this->view->is_json) {
			$this->useTagList();
			
			//only want to show the 'add' link if client can actually be added
			AutoLoader::Instance()->addPath(APP_CLASS_ROOT.'usage/');
			$user = CurrentlyLoggedInUser::Instance();
			$account = $user->getAccount();
			$limit_checker = new LimitChecker(
				new ContactUsageChecker($account), 
				$account->getPlan());
			$this->view->set('add_disallowed',!$limit_checker->isWithinLimit('contact_limit'));
			
			$this->view->set('permission_import_enabled', Tactile_AccountMagic::getAsBoolean('permission_import_enabled', 't', 't'));
			$this->view->set('permission_export_enabled', Tactile_AccountMagic::getAsBoolean('permission_export_enabled', 't', 't'));
		}
	}
	
	protected function _handleSearchFields(SearchHandler $sh) {
		$query = array();
		$fields = array('org.name' => 'name' , 'org.accountnumber' => 'accountnumber', 'e.contact' => 'email');
		foreach($fields as $queryfield => $field) {
			if (!empty($this->_data[$field])) {
				$query[$field] = $this->_data[$field];
				$value = $this->_data[$field];
				$value = str_replace('*', '%', $value);
				if(!is_numeric($queryfield)) {
					$field = $queryfield;
				}
				$constraint = new Constraint($field, 'ILIKE', $value);
				$sh->addConstraint($constraint);
			}
		}
		foreach (array('org.lastupdated' => 'updated', 'org.created' => 'created') as $dbfield => $queryfield) {
			foreach (array('after' => '>', 'before' => '<') as $criteria => $operator) {
				if (!empty($this->_data[$queryfield.'_'.$criteria])) {
					$query[$queryfield.'_'.$criteria] = $this->_data[$queryfield.'_'.$criteria];
					$ts = strtotime($this->_data[$queryfield.'_'.$criteria]);
					if($ts === false) {
						Flash::Instance()->addError("Invalid Date Value for " . $queryfield.'_'.$criteria);
						sendTo();
						return;
					}			
					$datetime = date('Y-m-d H:i:s', $ts);
					$sh->addConstraint(new Constraint($dbfield, $operator, $datetime));		
				}
			}
		}
		$this->view->set('current_query', http_build_query($query));
	}
	
	public function useTagList($tags=null) {
		$taggable = new TaggedItem($this->organisation);
		$tags_to_show = $taggable->getTagList($tags);
		$this->view->set('all_tags',$tags_to_show);
	}
	
	function alphabetical() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('organisations_index_restriction', 'alphabetical', EGS::getUsername());
		}
		$this->useRestriction('alphabetical');
	}

	function recent() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('organisations_index_restriction', 'recent', EGS::getUsername());
		}
		$this->useRestriction('recent');
	}
	
	function mine() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('organisations_index_restriction', 'mine', EGS::getUsername());
		}
		$this->useRestriction('mine');
	}
	
	function _new() {
		if(!isset($this->_data['id'])) {
			$check = self::checkBeforeForm('clients');
			if($check === false) {
				return false;
			}
		}
		parent::_new();
		$roles = Omelette_Role::getRolesAndUsers(true);
		$this->view->set('roles',$roles);
		
		$user_model = CurrentlyLoggedInUser::Instance()->getModel();
		if (empty($this->_data['id'])) {
			$this->view->set('fixed_permissions', $user_model->hasFixedPermissions());
			$this->view->set('default_permissions_read', $user_model->getDefaultPermissions('read'));
			$this->view->set('default_permissions_write', $user_model->getDefaultPermissions('write'));
		}
	}
	
	public static function checkBeforeForm($where) {
		AutoLoader::Instance()->addPath(APP_CLASS_ROOT.'usage/');
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		$limit_checker = new LimitChecker(
			new ContactUsageChecker($account), 
			$account->getPlan());
		if(false === $limit_checker->isWithinLimit('contact_limit')) {
			if($user->isAccountOwner()) {
				Flash::Instance()->addError("You have as many contacts (companies & people) as your account is allowed. You must delete something to make room, or upgrade your account");
				sendTo('account/change_plan');
				return;
			}
			else {
				Flash::Instance()->addError("You have as many open contacts (companies & people) as your account is allowed. You must delete something to make room, or your Account Owner will need to upgrade");
				sendTo('/'.$where.'/');
				return;
			}
		}
	}
	
	function edit() {
		if(!isset($this->_data['id']) || false === $this->organisation->load($this->_data['id'])) {
			Flash::Instance()->addError("Invalid ID specified");
			sendTo('organisations');
			return;
		}
		
		if (!$this->organisation->canView() && !isModuleAdmin()) {
			Flash::Instance()->addError('You don\'t have permission to edit that organisation');
			sendTo('organisations');
			return;
		}
		
		parent::edit();
	}

	function delete() {
		ModelDeleter::delete($this->_uses['Organisation'],'Organisation',array('organisations'));
	}

	function get_categorisation_options() {
		$crm = new CompanyCrm();
		$options=array();
		foreach($crm->belongsTo as $name=>$info) {
			$model = new $info['model'];
			$options['CompanyCrm_'.$name] = $model->getAll();
			$compulsory = $crm->getField($info['field'])->not_null;
			if(!$compulsory) {
				$options['CompanyCrm_'.$name] = array('NULL'=>'None')+$options['CompanyCrm_'.$name];
			}
		}
		$this->view->set('options',$options);
	}

	function add_note() {
		$this->uses(new CompanyNote());
		$this->setTemplateName('add_note');
	}

	function get_countries() {
		$c = new Country();
		echo json_encode($c->getAll());exit;
	}

	function save() {
		$db = DB::Instance();
		$flash=Flash::Instance();
		
		if(!empty($this->_data['Organisation']['id'])&&!isModuleAdmin()&&!Organisation::checkAccess('write',$this->_data['Organisation']['id'])) {
			$flash->addError('You don\'t have permission to edit this Organisation');
			sendTo('organisations');
			return;
		}
		
		$cms = array('phone' => 'T', 'fax' => 'F', 'email' => 'E', 'website' => 'W');
		foreach($cms as $cm=>$type) {
			if(!empty($this->_data['Organisation'][$cm]['contact']) && empty($this->_data['Organisation'][$cm]['name']) && empty($this->_data['Organisation'][$cm]['id'])) {
				$this->_data['Organisation'][$cm]['name']='Main';
			}
		}
		// Make new address saving method backward-compatible
		$address_fields = array('street1', 'street2', 'street3', 'town', 'county', 'country_code');
		$has_address = false;
		foreach ($address_fields as $field) {
			if (!empty($this->_data['Organisation'][$field])) {
				$has_address = true;
				$this->_data['Organisation']['address'][$field] = $this->_data['Organisation'][$field];
				unset($this->_data['Organisation'][$field]);
			}
			if (!empty($this->_data['Organisation']['id']) && $has_address) {
				// Make sure we save the address ID if this is an existing address
				$main_address_id = $db->getOne("SELECT id FROM organisation_addresses WHERE organisation_id = " . $db->qstr($this->_data['Organisation']['id']) . " AND main LIMIT 1");
				if (!empty($main_address_id)) {
					$this->_data['Organisation']['address']['id'] = $main_address_id;
				} else {
					$this->_data['Organisation']['address']['id'] = '';
				}
			}
		}
		
		$db->StartTrans();
		$saver = new ModelSaver();
		$data = isset($this->_data['Organisation']) ? $this->_data['Organisation'] : array();
		$client = $saver->save($data,'Organisation', $errors);
		if($client!==false) {
			$aliases = $saver->saveAliases($data,$client,$errors);
		}
		if($client===false||$aliases===false) {
			$this->saveData();
			$db->FailTrans();
			$db->CompleteTrans();
			if(isset($data['id']) && !empty($data['id'])) {
				sendTo('organisations','edit','',array('id'=>$data['id']));
				return;
			}
			sendTo('organisations','new');
			return;
		}
		
		// Free or Fixed permissions?
		$user_model = CurrentlyLoggedInUser::Instance()->getModel();
		if ($user_model->hasFixedPermissions()) {
			$sharing = array('read' => $user_model->getDefaultPermissions('read'), 'write' => $user_model->getDefaultPermissions('write'));
		} else {
			$sharing = !empty($this->_data['Sharing']) ? $this->_data['Sharing'] : array('read' => 'everyone', 'write' => 'everyone');
		}
		
		// Allowed to specify permissions? New, or am owner/admin without fixed permissions
		if (empty($data['id']) || (!$user_model->hasFixedPermissions() && ($client->owner == EGS::getUsername() || isModuleAdmin()))) {
			$levels = array('read', 'write');
			$company_id = $client->id;
			OrganisationRoles::deleteForCompany($company_id);
			$crs=array();
			$everyone=array(
				'organisation_id'=>$company_id,
				'roleid'=>Omelette::getUserSpaceRole()->id
			);
			$private = array(
				'organisation_id'=>$company_id,
				'roleid'=>CurrentlyLoggedInUser::getUserRole(EGS::getUsername())->id
			);
			foreach($levels as $level) {
				if(isset($sharing[$level])) {
					switch($sharing[$level]) {
						case 'everyone':
							if(!isset($crs['everyone'])) {
								$crs['everyone']=$everyone;
							}
							$crs['everyone'][$level]=true;
							if($level=='write') {
								$crs['everyone']['read']=true;
							}

							break;
						case 'private':
							if(!isset($crs['private'])) {
								$crs['private']=$private;
							}
							$crs['private'][$level] = true;
							if($level=='write') {
								$crs['private']['read']=true;
							}
							break;
						default:
							if(is_array($sharing[$level])) {
								foreach($sharing[$level] as $role_id) {
									if(!isset($crs[$role_id])) {
										$crs[$role_id]=array(
										'organisation_id'=>$company_id,
										'roleid'=>$role_id
										);
									}
									$crs[$role_id][$level]=true;
									if($level=='write') {
										$crs[$role_id]['read']=true;
									}
								}
							}
					}
				}
			}
			$errors=array();
			foreach($crs as $cr_data) {
				$cr = DataObject::Factory($cr_data,$errors,'OrganisationRoles');
				if($cr!==false) {
					$cr->save();
				}
			}

		}
		
		if (!empty($data['person_id'])) {
			// May have received a newly created person via a multi-quickadd form
			// This person might have been created before the organisation field was filled in
			$person = new Person();
			if (FALSE !== $person->load($data['person_id'])) {
				$cid = $person->organisation_id;
				if (empty($cid)) {
					// Only update if not belonging to an existing company
					$person->organisation_id = $client->id;
					$person->save();
				}
			}
		}
		$this->view->set('model', $client);
		$db->CompleteTrans();
		sendTo('organisations','view','',array('id'=>$client->id));
	}


	function new_person() {
		$check = OrganisationsController::checkBeforeForm('people');
		if($check === false) {
			return false;
		}
		$this->uses('Person');
	}
	
	function new_opportunity() {
		AutoLoader::Instance()->addPath(CONTROLLER_ROOT.'crm/');
		$check = Tactile_OpportunitysController::checkBeforeForm();
		if($check===false) {
			return;
		}
		
		$flash  = Flash::Instance();
		if(!$flash->hasErrors()) {
			$_SESSION['_controller_data']=array();
		}
		$this->uses($opp=DataObject::Construct('Opportunity'));
		$opp->organisation_id = $this->_data['id'];
		$this->_data['organisation_id'] = $this->_data['id'];
	}
	
	function new_activity() {
		$flash  = Flash::Instance();
		if(!$flash->hasErrors()) {
			$_SESSION['_controller_data']=array();
		}
		$this->uses($act = DataObject::Construct('Activity'));
		$act->organisation_id = $this->_data['id'];
		$this->_data['organisation_id'] = $this->_data['id'];
	}
	
	/**
	 * Get upto 20 Organisations
	 *
	 */
	public function filtered_list() {
		$orgs = new Omelette_OrganisationCollection();
		$sh = new SearchHandler($orgs,false);
		$sh->extract();
		
		$cc = new ConstraintChain();
		if(!empty($this->_data['name'])) {
			$cc->add(new Constraint('org.name','ILIKE',$this->_data['name'].'%'));
		}
		$sh->addConstraintChain($cc);
		$sh->perpage = 10;
		$orgs->load($sh);
		
		$model = $orgs->getModel();	
		//then get the query, same one as used for the index
		$query = $model->getQueryForRecentlyViewedSearch();
		
		//recent things have their own collection that knows what to do with the query
		$recent = new ViewedItemCollection($model);
		$sh = new SearchHandler($recent,false);
		$sh->extract();
		$sh->perpage = 3;
		$db = DB::Instance();
		
		//things in the ViewedItem query are 'x', need to unambiguise 'name'
		$query->where('x.name ILIKE ' . $db->qstr($this->_data['name'].'%'));
		$recent->load($sh, $query);
		
		// Search by exact Account Number match
		$by_accountno = new Omelette_OrganisationCollection();
		$sh = new SearchHandler($by_accountno, false);
		$sh->extract();
		$sh->addConstraint(new Constraint('accountnumber','=',$this->_data['name']));
		$by_accountno->load($sh);
		
		// Remove duplicates from the other lists
		foreach ($by_accountno as $org) {
			if (false !== ($index = $recent->contains('id', $org->id))) {
				$recent->remove($index);
			}
			if (false !== ($index = $orgs->contains('id', $org->id))) {
				$orgs->remove($index);
			}
		}
		foreach($recent as $org) {
			if(false !== ($index = $orgs->contains('id', $org->id))) {
				$orgs->remove($index);
			}
		}
		
		$this->view->set('items',$orgs);
		$this->view->set('recent', $recent);
		$this->view->set('by_accountno', $by_accountno);
	}

	/**
	 * Get all companies
	 *
	 */
	public function all_companies() {
		$this->filtered_list();
		$this->setTemplateName('filtered_list');
	}
	
	public function freshbooks() {
		if(empty($this->_data['id']) || false === $this->organisation->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid ID');
			return;
		}
		$fb_id = $this->organisation->freshbooks_id;
		if(empty($fb_id)) {
			$this->view->set('is_attached', false);
		}
		else {
			FreshbooksHelper::invoice_list($this->organisation);
			if(!Flash::Instance()->hasErrors()) {
				FreshbooksHelper::estimate_list($this->organisation);
			}
		}
	}
	
	public function freshbooks_client_list() {
		FreshbooksHelper::client_list();
	}
	
	public function details_for_freshbooks() {
		if(empty($this->_data['id']) || false === $this->organisation->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid ID');
			return;
		}
		$org = $this->organisation;
		$freshbooks_client = array(
			'organization'	=> $org->name,
			'p_street1'		=> $org->address->street1,
			'p_street2'		=> $org->address->street2,
			'p_city'		=> $org->address->town,
			'p_county'		=> $org->address->county,
			'p_country'		=> $org->address->country,
			'p_code'		=> $org->address->postcode,
			'first_name'	=> '',
			'last_name'		=> '',
			'email'			=> '',
			'work_phone'	=> '',
		);
		
		$person = DataObject::Construct('Tactile_Person');
		$person = $person->loadBy('organisation_id', $this->organisation->id);
		if($person !== false) {
			$freshbooks_client['first_name'] = $person->firstname;
			$freshbooks_client['last_name'] = $person->surname;
			$freshbooks_client['email'] = $person->email->contact;
			$freshbooks_client['work_phone'] = $person->phone->contact;
		}
		$this->view->set('freshbooks_client', json_encode($freshbooks_client));
	}
	
	public function savefreshbookslink() {
		if(empty($this->_data['id']) || false === $this->organisation->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid ID');
			return;
		}
		$this->organisation->update($this->organisation->id, 'freshbooks_id', $this->_data['freshbooks_id']);
		$this->organisation->freshbooks_id = $this->_data['freshbooks_id'];
		FreshbooksHelper::invoice_list($this->organisation);
	}
	
	public function add_to_freshbooks() {
		if(empty($this->_data['id']) || false === $this->organisation->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid ID');
			return;
		}
		FreshbooksHelper::client_create($this->organisation);
	}
	
	public function freshbooks_reset_link() {
		if(empty($this->_data['id']) || false === $this->organisation->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid ID');
			return;
		}
		FreshbooksHelper::reset_link($this->organisation);
	}
	
	public function zendesk_tickets() {
		if(empty($this->_data['id']) || false === $this->organisation->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid ID');
			return;
		}
		
		ZendeskHelper::ticket_list($this->organisation);
	}
	
	public function resolve_tickets() {
		if(empty($this->_data['id']) || false === $this->organisation->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid ID');
			return;
		}
		
		ResolveHelper::ticket_list($this->organisation);
	}
	
	public function get_thumbnail_urls() {
		$org = $this->organisation;
		if (empty($this->_data['ids'])) {
			return;
		}
		$tns = array();
		foreach ($this->_data['ids'] as $id) {
			if (FALSE !== $org->load($id) && FALSE !== $org->getThumbnailUrl()) {
				$tns[$id] = $org->getThumbnailUrl();
			}
		}
		$this->view->set('tns', $tns);
	}
	
	public function delete_logo() {
		$org = $this->organisation;
		if (empty($this->_data['id']) || false === $org->load($this->_data['id'])) {
			return;
		}
		$db = DB::Instance();
		$db->StartTrans();
		$s3 = new S3_Service(S3_ACCESS_KEY, S3_SECRET);
		
		$file = new S3File();
		$file->load($org->logo_id);
		if ($file->canDelete() === false) {
			$db->FailTrans();
		}
		$success = $this->s3->object->delete($this->s3file->object, $this->s3file->bucket);
		
		$db->CompleteTrans();
	}
	
	public function list_all() {
		if (!$this->view->is_json) {
			sendTo('organisations');
			return;
		}
		
		$collection = new Omelette_OrganisationCollection();
		$sh = new SearchHandler($collection, false);
		$sh->extractFields();
		$sh->extractPaging();
		$sh->perpage = 0;
		$sh->setOrderby('name');
		$this->_handleSearchFields($sh);
		$query = $collection->getLoadQuery($sh)->__toString();
		
		$db = DB::Instance();
		$results = $db->getArray($query);
		
		$json = array('status' => 'success', 'organisations' => array());
		foreach ($results as $result) {
			$json['organisations'][] = array(
				'id'				=> $result['id'],
				'name'				=> $result['name'],
				'lastupdated'		=> date('Y-m-d\TH:i:sO', strtotime($result['lastupdated']))
			);
		}
		$this->view->set('list_all', json_encode($json));
	}
	
	public function update_last_contacted() {
		$this->setTemplateName('save');
		$org = $this->organisation;
		if (!isset($this->_data['id']) || false === $org->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid id specified');
			sendTo('organisations');
			return;
		}
		if (!$org->canView() && !isModuleAdmin()) {
			Flash::Instance()->addError('You don\'t have permission to view that Organisation');
			sendTo('organisations');
			return;
		}
		$org->last_contacted = date('Y-m-d H:i:s');
		$org->last_contacted_by = EGS::getUsername();
		if ($org->save()) {
			Flash::Instance()->addMessage('Last contacted date updated');
		} else {
			Flash::Instance()->addError('Failed to update last contacted date');
		}
		sendTo('organisations/view/'.$org->id);
	}
	
}
