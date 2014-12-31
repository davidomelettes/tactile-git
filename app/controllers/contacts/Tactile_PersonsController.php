<?php
class Tactile_PersonsController extends Controller {

	/**
	 * @var Tactile_Person
	 */
	protected $person;
	
	function __construct($module=null,$view=null) {
		parent::__construct($module,$view);
		$this->uses('Person');
		$this->mixes('save_contact','ContactMethodActions',array('Tactile_Personcontactmethod'));
		$this->mixes('save_contact_multi','ContactMethodActions',array('Tactile_Personcontactmethod', 'PersoncontactmethodCollection'));
		$this->mixes('save_custom_multi','CustomFieldActions',array('CustomfieldMap', 'CustomfieldMapCollection'));
		$this->mixes('delete_custom','CustomFieldActions',array('CustomfieldMap', 'CustomfieldMapCollection'));		
		$this->mixes('save_address','AddressSaver',array('Tactile_Personaddress', 'person_id'));
		$this->mixes('delete_address','AddressSaver',array('Tactile_Personaddress', 'person_id'));
		$this->mixes('delete_contact','ContactMethodActions',array('PersonContactMethod'));
		$this->mixes('contact_methods', 'ContactMethodActions', array('Person', 'PersoncontactmethodCollection', 'person_id'));
		$this->mixes('opportunities','RelatedItemsLoader',array('opportunities','Person'),'get_related');
		$this->mixes('activities','RelatedItemsLoader',array('activities','Person'),'get_related');
		$this->mixes('save_note','NoteSaver',array());
		
		$this->mixes('add_tag','TagHandler',array('Person'));
		$this->mixes('remove_tag','TagHandler',array('Person'));
		$this->mixes('by_tag','TagHandler',array('Tactile_Person','persons'));
		
		$this->mixes('recently_viewed','RecentlyViewedHandler',array('Tactile_Person','persons'));
		
		$this->mixes('files','RelatedItemsLoader',array('s3_files','Person'),'get_related');
		$this->mixes('new_file','S3FileHandler',array('Person','people'));
		$this->mixes('save_file','S3FileHandler',array('Person','contacts','persons'));
		
		$this->mixes('add_activity_track', 'ActivityTrackAdder', array('Tactile_Person', 'person_id', 'people'));
		$this->mixes('save_activity_track', 'ActivityTrackAdder', array('Tactile_Person', 'person_id', 'people'));
		
		$this->mixes('export', 'ExportHandler', array('person', 'people'));
		$this->mixes('mass_action', 'MassActionHandler', array('Tactile_Person', 'people'));
	}

	public function getPerson() {
		return $this->person;
	}
	
	protected function _getContactMethods() {
		$person = $this->person;
		
		$methods = new PersoncontactmethodCollection();
		$sh = new SearchHandler($methods, false);
		$sh->addConstraint(new Constraint('person_id', '=' , $person->id));
		$sh->setOrderby('position, main desc, name');
		$methods->load($sh);
		return $methods;
	}
	
	protected function _getAddresses() {
		$person = $this->person;
		
		$addresses = new Tactile_PersonaddressCollection();
		$sh = new SearchHandler($addresses, false);
		$sh->addConstraint(new Constraint('person_id', '=' , $person->id));
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
		
		$person = $this->person;
		
		$customfieldsCollection = $person->getCustomFields();
		$this->view->set('custom_fields', $customfieldsCollection);
		$this->view->set('custom_fields_json', $customfieldsCollection->asJson());
		
		$customfieldsMapCollection = $person->getCustomValues();
		$this->view->set('custom_fields_map', $customfieldsMapCollection);
		$this->view->set('existing_custom_fields_json', $customfieldsMapCollection->asJson());
	}
	
	protected function _getTimeline($page=1) {
		$timeline = new Timeline();
		$cc = new ConstraintChain();
		$cc->add(new Constraint('person_id', '=', $this->person->id));
		
		$timeline->addType('note');
		$timeline->addType('email');
		$timeline->addType('flag');
		$timeline->addType('s3file');
		$timeline->addType('opportunity');
		$timeline->addType('new_activity');
		$timeline->addType('completed_activity');
		$timeline->addType('overdue_activity');
		
		$timeline->load($cc, $page);
		
		$this->view->set('current_query', 'id='.$this->person->id);
		$this->view->set('cur_page', $timeline->cur_page);
		$this->view->set('num_pages', $timeline->num_pages);
		$this->view->set('per_page', $timeline->per_page);
		$this->view->set('num_records', $timeline->total);
		
		$this->view->set('timeline_rss', CurrentlyLoggedInUser::Instance()->getTimelineFeedAddress() . '&amp;person_id='.$this->person->id);
		return $timeline;
	}
	
	function index() {
		if ($this->view->is_json) {
			$people_list_type = 'alphabetical';
		} else {
			$people_list_type = Omelette_Magic::getValue('persons_index_restriction', EGS::getUsername(), 'alphabetical');
		}
		switch($people_list_type) {
			case 'alphabetical':
			case 'firstname':
			case 'recent':
			case 'mine':
			case 'recently_viewed':
			case 'individuals':
				break;
			default:
				$people_list_type = 'alphabetical';
				break;
		}
		$this->$people_list_type();
		
		UsageWarningHelper::displayUsageWarning($this->view, 'contacts');
	}
	
	function search() {
		$this->useRestriction('alphabetical');
		$this->view->set('sub_title', 'Matching Search Query');
	}
	
	private function useRestriction($name) {
		$people = new Omelette_PersonCollection($this->person);
		$sh = new SearchHandler($people,false);
		$sh->extractOrdering();
		$sh->extractPaging();
		$this->view->set('restriction',$name);
		$sort_order = 'alphabetical';
		switch($name) {
			case 'recent':
				$sh->setOrderby('per.created','desc');
				$sort_order = 'date';
				break;
			case 'individuals':
				$sh->addConstraint(new Constraint('per.organisation_id','IS','NULL'));
				break;
			case 'mine':
				$this->view->set('restriction','mine');
				$sh->addConstraint(new Constraint('per.assigned_to','=',EGS::getUsername()));
				break;
			case 'firstname':
				$sh->setOrderby('per.firstname','asc');
				break;
			case 'alphabetical':	//fall through
			default:
				$this->view->set('restriction','alphabetical');
				break;
		}
		$this->view->set('sort_order', $sort_order);
		$this->view->set('sort_field', $sh->getOrderBy());
		
		$this->_handleSearchFields($sh);
		Controller::index($people,$sh);
		$this->setTemplateName('index');
		
		if(!$this->view->is_json) {
			$this->useTagList();
			
			//only want to show the 'add' link if person can actually be added
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
		$fields = array('per.firstname' => 'firstname', 'per.surname' => 'surname', 'per.surname' => 'name', 'e.contact'=>'email');
		foreach($fields as $queryfield => $field) {
			if(!empty($this->_data[$field])) {
				$query[$field] = $this->_data[$field];
				$value = $this->_data[$field];
				$value = str_replace('*', '%', $value);
				if(!is_numeric($queryfield)) {
					$field = $queryfield;
				}
				$constraint = new Constraint('lower('.$field.')', 'LIKE', strtolower($value));
				$sh->addConstraint($constraint);
			}
		}
		
		if(!empty($this->_data['fullname'])) {
			$query['fullename'] = $this->_data['fullname'];
			$value = $this->_data['fullname'];
			$value = str_replace('*', '%', $value);
			$constraint = new Constraint("per.firstname||' '||per.surname", 'ILIKE', $value);
			$sh->addConstraint($constraint);
		}
		
		$exact_fields = array('per.organisation_id' => 'organisation_id');
		foreach($exact_fields as $queryfield => $field) {
			if(!empty($this->_data[$field])) {
				$query[$field] = $this->_data[$field];
				$value = $this->_data[$field];
				if(!is_numeric($queryfield)) {
					$field = $queryfield;
				}
				$constraint = new Constraint($field, '=', $value);
				$sh->addConstraint($constraint);
			}
		}
		foreach(array('per.lastupdated' => 'updated', 'per.created' => 'created') as $dbfield => $queryfield) {
			foreach(array('after' => '>', 'before' => '<') as $criteria => $operator) {
				if(!empty($this->_data[$queryfield.'_'.$criteria])) {
					$query[$queryfield.'_'.$criteria] = $this->_data[$queryfield.'_'.$criteria];
					$ts = strtotime($this->_data[$queryfield.'_'.$criteria]);
					if($ts === false) {
						Flash::Instance()->addError("Invalid Date Value for " . $queryfield.'_'.$criteria);
						sendTo();
						return;
					}			
					$datetime = date('Y-m-d H:i:s', $ts);
					$sh->addConstraint(new Constraint('per.' . $dbfield, $operator, $datetime));		
				}
			}
		}
		$this->view->set('current_query', http_build_query($query));
	}
	
	public function useTagList($tags=null) {
		$taggable = new TaggedItem($this->person);
		$tags_to_show = $taggable->getTagList($tags);
		$this->view->set('all_tags',$tags_to_show);
	}
	
	function mine() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('persons_index_restriction', 'mine', EGS::getUsername());
		}
		$this->useRestriction('mine');		
	}
	
	function alphabetical() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('persons_index_restriction', 'alphabetical', EGS::getUsername());
		}
		$this->useRestriction('alphabetical');
	}

	function firstname() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('persons_index_restriction', 'firstname', EGS::getUsername());
		}
		$this->useRestriction('firstname');
	}

	function recent() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('persons_index_restriction', 'recent', EGS::getUsername());
		}
		$this->useRestriction('recent');
	}
	
	function individuals() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('persons_index_restriction', 'individuals', EGS::getUsername());
		}
		$this->useRestriction('individuals');
	}
	
	function view() {
		$person = $this->person;
		if(!isset($this->_data['id']) || false===$person->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid id specified');
			sendTo('people');
			return;
		}
		if(!$person->canView()) {
			Flash::Instance()->addError('You don\'t have permission to view that Person');
			sendTo('people');
			return;
		}
		
		if (!$this->view->is_json) {
			$this->_getCustomFields();
			
			$fields = array(
				'organisation',	
				'person_reports_to',
				'jobtitle',
				'dob',
				'can_call',
				'language',
				'can_email',
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
			
			/* This is so we can show the orgs contact details */
			$org = new Tactile_Organisation();
			if (FALSE !== $org->load($person->organisation_id)) {
				$methods = new OrganisationcontactmethodCollection();
				$sh = new SearchHandler($methods, false);
				$sh->addConstraint(new Constraint('organisation_id', '=' , $person->organisation_id));
				$sh->setOrderby('position, main desc, name');
				$methods->load($sh);
				$this->view->set('organisation_contact_methods', $methods);
				$addresses = new Tactile_OrganisationaddressCollection();
				$sh = new SearchHandler($addresses, false);
				$sh->addConstraint(new Constraint('organisation_id', '=' , $person->organisation_id));
				$sh->setOrderby('main desc, name');
				$addresses->load($sh);
				$this->view->set('organisation_addresses', $addresses);
				$this->view->set('Organisation', $org);
			}

			$this->view->set('ipscape_site_address', Tactile_AccountMagic::getValue('ipscape_site_address'));
			
			$this->view->set('head_title', h($person->fullname));
			$page = !empty($this->_data['timeline_page']) ? ((int)$this->_data['timeline_page']) : 1;
			$this->view->set('activity_timeline', $this->_getTimeline($page));
			$this->view->set('timeline_view', Omelette_Magic::getValue('timeline_view', EGS::getUsername(), 'list'));
			$account = CurrentlyLoggedInUser::Instance()->getAccount();
			$this->view->set('show_zendesk', $account->isZendeskEnabled());
			$this->view->set('show_resolve', $account->isResolveEnabled());			
			$this->view->set('logo_url', $person->getLogoUrl());
			$this->view->set('pipeline', $person->getPipelineDetails());
			$this->view->set('closetime', $person->getTimeToClose());
			$this->view->set('winrate', $person->getWinRate());
			$this->view->set('last_contact', $person->getLastContact());
			ViewedPage::createOrUpdate(ViewedPage::TYPE_PERSON,$this->person->id,EGS::getUsername(),$this->person->fullname);
			$this->view->set('contact_methods', $this->_getContactMethods());
			$this->view->set('addresses', $this->_getAddresses());
			
			$track = new ActivityTrack();
			$this->view->set('activity_tracks', $track->getAll());
		}
	}
	
	function timeline() {
		if (!$this->view->is_json) {
			sendTo('people');
			return;
		}
		$person = $this->person;
		if(!isset($this->_data['id']) || false===$person->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid id specified');
			sendTo('people');
			return;
		}
		if(!$person->canView()) {
			Flash::Instance()->addError('You don\'t have permission to view that person');
			sendTo('people');
			return;
		}
		$page = !empty($this->_data['page']) ? ((int)$this->_data['page']) : 1;
		
		$this->view->set('activity_timeline', $this->_getTimeline($page));
	}
	
	function options() {
		if (!$this->view->is_json) {
			sendTo('people');
			return;
		}
		$user = new Omelette_User();
		$this->view->set('assigned_to', $user->getAll());
		$language = new Language();
		$this->view->set('language_code', $language->getAll());
		$country = new Country();
		$this->view->set('country_code', $country->getAll());
		
		// Default country code
		$this->view->set('default_country_code', EGS::getCountryCode());
	}
	
	function _new() {
		if(!isset($this->_data['id'])) {
			$check = OrganisationsController::checkBeforeForm('people');
			if($check === false) {
				return false;
			}
		}
		parent::_new();
		$role = DataObject::Construct('Role');
		$roles = $role->getAll();
		$this->view->set('roles',$roles);
	}
	
	function edit() {
		$person = $this->_uses['Person'];
		if(empty($this->_data['id'])||$person->load($this->_data['id'])===false) {
			Flash::Instance()->addError('The person you tried to edit doesn\'t exist');
			sendTo('persons','index','contacts');
			return;
		}

		if(!$person->canEdit()) {
			Flash::Instance()->addError('You don\'t have permission to edit that person');
			sendTo('persons','view','contacts',array('id'=>$person->id));
			return;
		}
		$this->_new();
		$this->setTemplateName('new');
	}
	
	function delete() {
		ModelDeleter::delete($this->_uses['Person'],'Person',array('persons','index','contacts'));
	}
	
	function save() {
		$db = DB::Instance();
		
		$cms = array('phone'=>'T','fax'=>'F','email'=>'E','mobile'=>'M');
		foreach($cms as $cm=>$type) {
			if(!empty($this->_data['Person'][$cm]['contact']) && empty($this->_data['Person'][$cm]['name']) && empty($this->_data['Person'][$cm]['id'])) {
				$this->_data['Person'][$cm]['name']='Main';
			}
		}
		// Make new address saving method backward-compatible
		$address_fields = array('street1', 'street2', 'street3', 'town', 'county', 'country_code');
		$has_address = false;
		foreach ($address_fields as $field) {
			if (!empty($this->_data['Person'][$field])) {
				$has_address = true;
				$this->_data['Person']['address'][$field] = $this->_data['Person'][$field];
				unset($this->_data['Person'][$field]);
			}
			if (!empty($this->_data['Person']['id']) && $has_address) {
				// Make sure we save the address ID if this is an existing address
				$main_address_id = $db->getOne("SELECT id FROM person_addresses WHERE organisation_id = " . $db->qstr($this->_data['Person']['id']) . " AND main LIMIT 1");
				if (!empty($main_address_id)) {
					$this->_data['Person']['address']['id'] = $main_address_id;
				}
			}
		}
		
		$db->StartTrans();
		$data = isset($this->_data['Person']) ? $this->_data['Person'] : array();
		
		$errors = array();
		if (empty($data['organisation_id']) && !empty($data['organisation']) && $data['organisation'] !== 'Type to find') {
			if (FALSE !== ($org = Tactile_Organisation::factoryFromString($data['organisation'], null, $org_errors))) {
				if ($org->save()) {
					$data['organisation_id'] = $org->id;
				} else {
					$errors[] = 'There was a problem saving the associated Organisation';
				}
			} else {
				$errors = array_merge($errors, $org_errors);
			}
		}
		if (empty($data['person_reports_to']) && !empty($data['person_person_reports_to']) && $data['person_person_reports_to'] !== 'Type to find') {
			if (FALSE !== ($person = Tactile_Person::factoryFromString($data['person_person_reports_to'], null, $person_errors))) {
				if ($person->save()) {
					$data['person_reports_to'] = $person->id;
				} else {
					$errors[] = 'There was a problem saving the associated Person';
				}
			} else {
				$errors = array_merge($errors, $person_errors);
			}
		}
		
		if (empty($errors)) {
			$saver = new ModelSaver();
			$person = $saver->save($data,'Person',$errors);
			if($person!==false) {
				$aliases = $saver->saveAliases($data,$person,$errors);
				if($aliases!==false) {
					$db->CompleteTrans();
					$this->view->set('model', $person);
					sendTo('persons','view','contacts',array('id'=>$person->id));
					return;
				}
			}
		}
		$db->FailTrans();
		$db->CompleteTrans();
		$this->saveData();
		if(!empty($data['id'])) {
			sendTo('persons','edit','contacts',array('id'=>$data['id']));
			return;
		}
		sendTo('persons','new','contacts');
	}
	
	
	function filter() {
		$p = new Person();
		$cc = new ConstraintChain();
		if(!empty($this->_data['organisation_id'])) {
			$cc->add(new Constraint('organisation_id','=',$this->_data['organisation_id']));
		}
		$people = $p->getAll($cc);
		echo json_encode($people);exit;
	}
	
	public function filtered_list() {
		$people = new Omelette_PersonCollection();
		$sh = new SearchHandler($people,false);
		$sh->extractPaging();
		
		$cc = new ConstraintChain();
		//data can come in either as a already-joined name, or as firstname/surname separately
		if(!empty($this->_data['name'])) {
			$cc->add(new Constraint('firstname || \' \' || surname','ILIKE',$this->_data['name'].'%'));
		}
		else if(!empty($this->_data['firstname']) || !empty($this->_data['surname'])) {
			if(!empty($this->_data['firstname'])) {
				$cc->add(new Constraint('firstname', 'ILIKE', $this->_data['firstname'].'%'));
			}
			if(!empty($this->_data['surname'])) {
				$cc->add(new Constraint('surname', 'ILIKE', $this->_data['surname'].'%'));
			}
		}
		
		//also allows restriction to a single company
		if(!empty($this->_data['organisation_id'])) {
			$cc->add(new Constraint('organisation_id','=',$this->_data['organisation_id']),'AND');
		}
		$sh->addConstraintChain($cc);
		$sh->perpage = 10;
		$people->load($sh);
		
		//we want to put recently-viewed things at the top:
		//re-use the same model instance
		$model = $people->getModel();	
		//then get the query, same one as used for the index
		$query = $model->getQueryForRecentlyViewedSearch();
		
		//recent things have their own collection that knows what to do with the query
		$recent = new ViewedItemCollection($model);
		$sh = new SearchHandler($recent,false);
		$sh->extractPaging();
		$sh->perpage = 3;
		
		//but we need to re-use the contraints (name like X and organisation_id) from above:
		$query->where($cc->__toString());
		$recent->load($sh,$query);
		
		//remove duplicates from the alphabetical list:
		foreach($recent as $person) {
			if(false !== ($index = $people->contains('id', $person->id))) {
				$people->remove($index);
			}
		}
		
		//both get sent to the view and displayed separately, easiest way to put in a divider/distinguishing mark
		$this->view->set('field','fullname');
		$this->view->set('items',$people);
		$this->view->set('recent', $recent);
	}
	
	function get_job_titles() {
		$db = DB::Instance();
				
		$query = 'select jobtitle FROM people where jobtitle ilike '.$db->qstr($this->_data['searchterm'].'%').
		' and usercompanyid='.$db->qstr(EGS::getCompanyId()).' group by jobtitle  order by count(id) desc limit 20';
		$rows = $db->GetCol($query);
		$this->view->set('titles',$rows);
	}
	
	
	function get_company_id() {
		$p = new Person();
		if (FALSE !== $p->load($this->_data['id'])) {
			$response = array('status'=>'success','id'=>$p->organisation_id);
			echo json_encode($response);exit;
		}				
	}
	
	function new_opportunity() {
		AutoLoader::Instance()->addPath(CONTROLLER_ROOT.'crm/');
		$check = Tactile_OpportunitysController::checkBeforeForm();
		if($check===false) {
			return;
		}
		
		$this->uses($opp = DataObject::Construct('Opportunity'));
		$person = $this->_uses['Person'];
		$person->load($this->_data['id']);
		$opp->person_id=$person->id;
		$this->_data['person_id'] = $person->id;
		$this->_data['organisation_id'] = $person->organisation_id;
	}
	
	function new_activity() {
		$act = DataObject::Construct('Activity');
		$person = $this->_uses['Person']->load($this->_data['id']);
		$this->_data['person_id']=$person->id;
		$this->_data['organisation_id']=$person->organisation_id;
		$act->organisation_id = $person->organisation_id;
		$this->uses($act);
	}
		
	function by_jobtitle() {
		if (empty($this->_data['q'])) {
			Flash::Instance()->addError('Invalid search term');
			sendTo('people');
			return;
		}
		
		$items = new Omelette_PersonCollection;
		$sh = new SearchHandler($items, false);
		$sh->extractOrdering();
		$sh->extractPaging();
		
		$sh->addConstraint(new Constraint('lower(jobtitle)', '=', strtolower(urldecode($this->_data['q']))));
		Controller::index($items, $sh);
		
		$this->setTemplateName('index');
		$this->useTagList();
		$this->view->set('current_query', http_build_query(array('q' => $this->_data['q'])));
		$this->view->set('sub_title', 'with Job Title "' . $this->_data['q'] . '"');
	}
	
	
	public function zendesk_tickets() {
		if(empty($this->_data['id']) || false === $this->person->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid ID');
			return;
		}
		
		ZendeskHelper::ticket_list($this->person);
	}
	
	public function resolve_tickets() {
		if(empty($this->_data['id']) || false === $this->person->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid ID');
			return;
		}
		
		ResolveHelper::ticket_list($this->person);
	}
	
	public function get_thumbnail_urls() {
		$person = $this->person;
		if (empty($this->_data['ids'])) {
			return;
		}
		$tns = array();
		foreach ($this->_data['ids'] as $id) {
			if (FALSE !== $person->load($id) && FALSE !== $person->getThumbnailUrl()) {
				$tns[$id] = $person->getThumbnailUrl();
			}
		}
		$this->view->set('tns', $tns);
	}
	
	public function export_to_campaignmonitor() {
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		
		$permission_export_enabled = Tactile_AccountMagic::getAsBoolean('permission_export_enabled', 't', 't');
		if (!isModuleAdmin() && !$permission_export_enabled) {
			Flash::Instance()->addError('Contact exporting is disabled for non-admin users on your account');
			sendTo('people');
		}
		
		if (!$this->view->is_json || !$account->isCampaignMonitorEnabled() || !$user->isAdmin()) {
			sendTo('people');
			return;
		}
		
		$success = 'failure';
		
		// First, we need a list
		if (empty($this->_data['cm_list_id'])) {
			$this->view->set('status', $success);
			return;
		}
		$cm_list_id = $this->_data['cm_list_id'];
		require_once 'Service/CampaignMonitor.php';
		$cm = new Service_CampaignMonitor(Tactile_AccountMagic::getValue('cm_key'));
		$lists = $cm->clientGetLists(Tactile_AccountMagic::getValue('cm_client_id'));
		$cm_list_name = '';
		foreach ($lists as $list) {
			if ($list->getListId() == $cm_list_id) {
				$success = 'success';
				$cm_list_name = $list->getName();
			}
		}
		if ($success == 'success') {
			// Create delayed task
			$task = new DelayedCampaignMonitorExport();
			$task->setType('person');
			$task->setListId($cm_list_id);
			$task->setListName($cm_list_name);
			
			if (!empty($this->_data['query']) && !empty($this->_data['q'])) {
				$key = str_replace('by_','',$this->_data['query']);
				if (in_array($key, DelayedExport::$allowed_query_keys)) {
					$task->setQuery($key, $this->_data['q']);
				}
			}
			if(!empty($this->_data['tag'])) {
				$task->setTags(array_map('urldecode',$this->_data['tag']));
			}
			$task->save();
		}
		
		$this->view->set('status', $success);
	}
	
	public function list_all() {
		if (!$this->view->is_json) {
			sendTo('people');
			return;
		}
		$collection = new Omelette_PersonCollection();
		$sh = new SearchHandler($collection, false);
		$sh->extractFields();
		$sh->extractPaging();
		$sh->perpage = 0;
		$sh->setOrderby('surname, firstname');
		$this->_handleSearchFields($sh);
		$query = $collection->getLoadQuery($sh)->__toString();
		
		$db = DB::Instance();
		$results = $db->getArray($query);
		
		$json = array('status' => 'success', 'people' => array());
		foreach ($results as $result) {
			$json['people'][] = array(
				'id'				=> $result['id'],
				'firstname'			=> $result['firstname'],
				'surname'			=> $result['surname'],
				'organisation_id'	=> $result['organisation_id'],
				'organisation'		=> $result['organisation'],
				'lastupdated'		=> date('Y-m-d\TH:i:sO', strtotime($result['lastupdated']))
			);
		}
		$this->view->set('list_all', json_encode($json));
	}
	
	public function update_last_contacted() {
		$this->setTemplateName('save');
		$person = $this->person;
		if (!isset($this->_data['id']) || false === $person->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid id specified');
			sendTo('people');
			return;
		}
		if (!$person->canView() && !isModuleAdmin()) {
			Flash::Instance()->addError('You don\'t have permission to view that Person');
			sendTo('people');
			return;
		}
		$person->last_contacted = date('Y-m-d H:i:s');
		$person->last_contacted_by = EGS::getUsername();
		if ($person->save()) {
			Flash::Instance()->addMessage('Last contacted date updated');
		} else {
			Flash::Instance()->addError('Failed to update last contacted date');
		}
		sendTo('people/view/'.$person->id);
	}
	
}
