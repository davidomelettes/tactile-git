<?php
class Tactile_OpportunitysController extends Controller {

	/**
	 * The 'used' Opportunity
	 * @access protected
	 * @var Opportunity
	 */
	protected $opportunity;
	
	public function __construct($module,$view=null) {
		parent::__construct($module,$view);
		$this->uses('Opportunity');
		$this->mixes('save_note','NoteSaver',array('OpportunityNote'));
		$this->mixes('activities','RelatedItemsLoader',array('activities','Opportunity'),'get_related');
		$this->mixes('save_custom_multi','CustomFieldActions',array('CustomfieldMap', 'CustomfieldMapCollection'));
		$this->mixes('delete_custom','CustomFieldActions',array('CustomfieldMap', 'CustomfieldMapCollection'));
		$this->mixes('add_tag','TagHandler',array('Opportunity'));
		$this->mixes('remove_tag','TagHandler',array('Opportunity'));
		$this->mixes('by_tag','TagHandler',array('Tactile_Opportunity','opportunitys'));
		
		$this->mixes('recently_viewed','RecentlyViewedHandler',array('Opportunity','opportunitys'));
		
		$this->mixes('files','RelatedItemsLoader',array('s3_files','Opportunity'),'get_related');
		$this->mixes('new_file','S3FileHandler',array('Opportunity','opportunities'));
		$this->mixes('save_file','S3FileHandler',array('Opportunity','crm','opportunitys'));
		
		$this->mixes('add_activity_track', 'ActivityTrackAdder', array('Tactile_Opportunity', 'opportunity_id', 'opportunities'));
		$this->mixes('save_activity_track', 'ActivityTrackAdder', array('Tactile_Opportunity', 'opportunity_id', 'opportunities'));

		$cons = array();
        $this->mixes('by_status', 'FieldFilter',
			array('Tactile_OpportunityCollection', 'status', 'opportunities', 'By Sales Stage',
            	$cons), 'by_field');
        $this->mixes('by_source', 'FieldFilter',
			array('Tactile_OpportunityCollection', 'source', 'opportunities', 'By Source',
            	$cons), 'by_field');
        $this->mixes('by_type', 'FieldFilter',
			array('Tactile_OpportunityCollection', 'type', 'opportunities', 'By Type',
            	$cons), 'by_field');
                                
		$this->mixes('export', 'ExportHandler', array('opportunity', 'opportunities'));
		$this->mixes('mass_action', 'MassActionHandler', array('Tactile_Opportunity', 'opportunities'));
		
		if (!$this->view->is_json) {
			$this->_loadGraph();
		}
	}

	/**
	 * Loads the pipeline graph if no preference has been stated
	 */
	protected function _loadGraph() {
		require_once('Charts/Tactile.php');
		$chart = new Charts_Tactile();
		$graph_method = Charts_Tactile::getDashboardGraphMethod();

		if (!is_callable(array($chart, $graph_method))) {
			$graph_method = 'pipeline';
		}
		if (is_callable(array($chart, $graph_method)) && FALSE !== call_user_func(array($chart, $graph_method))) {
			$this->view->set('graph_title', $chart->getTitle());
			$current_user = CurrentlyLoggedInUser::Instance();
			$graph = $chart->getGraph();
			
			// If the graph has data, the welcome message is displayed, 
			if (!$graph->hasData()
				&& !Omelette_Magic::getAsBoolean('hide_welcome_message', $current_user->getRawUsername())
				&& Omelette_Magic::getAsBoolean('show_sample_graph', $current_user->getRawUsername(), 't', 't')) {
				// Show a sample image instead
			} else {
				// Dashboard-specific styles
				$graph
					->addAxisStyle(array(0, '', 9))
					->addAxisStyle(array(1, '', 9));
				$this->view->set('graph', $graph);
			}
			
			$this->view->set('graph_url', $chart->getUrl());
		}
	}
	
	protected function _getTimeline($page=1) {
		$timeline = new Timeline();
		$cc = new ConstraintChain();
		$cc->add(new Constraint('opportunity_id', '=', $this->opportunity->id));
		$cc->add(new Constraint('type', '!=', 'opportunity'));
		
		$timeline->addType('note');
		$timeline->addType('email');
		$timeline->addType('flag');
		$timeline->addType('s3file');
		$timeline->addType('new_activity');
		$timeline->addType('completed_activity');
		$timeline->addType('overdue_activity');
		
		$timeline->load($cc, $page);
		
		$this->view->set('current_query', 'id='.$this->opportunity->id);
		$this->view->set('cur_page', $timeline->cur_page);
		$this->view->set('num_pages', $timeline->num_pages);
		$this->view->set('per_page', $timeline->per_page);
		$this->view->set('num_records', $timeline->total);
		
		$this->view->set('timeline_rss', CurrentlyLoggedInUser::Instance()->getTimelineFeedAddress() . '&amp;opportunity_id='.$this->opportunity->id);
		return $timeline;
	}
	
	protected function _getCustomFields() {
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		if ($account->is_free() && !$account->in_trial()) {
			return false;
		}
		
		$opp = $this->opportunity;
		
		$customfieldsCollection = $opp->getCustomFields();
		$this->view->set('custom_fields', $customfieldsCollection);
		$this->view->set('custom_fields_json', $customfieldsCollection->asJson());
		
		$customfieldsMapCollection = $opp->getCustomValues();
		$this->view->set('custom_fields_map', $customfieldsMapCollection);
		$this->view->set('existing_custom_fields_json', $customfieldsMapCollection->asJson());
	}
	
	private function useRestriction($name) {
		$opportunities = new Tactile_OpportunityCollection($this->opportunity);
		$sh = new SearchHandler($opportunities,false);
		$sh->extractOrdering();
		$sh->extractPaging();
		$this->view->set('restriction',$name);
		$sort_order = 'alphabetical';
		switch($name) {
			case 'open': {
				$sh->addConstraint(new Constraint('open','=','true'));
				$sh->addConstraint(new Constraint('archived','=',false));
				$sh->setOrderby('opp.name','asc');
				break;
			}
			case 'open_date': {
				$sh->addConstraint(new Constraint('open','=','true'));
				$sh->addConstraint(new Constraint('archived','=',false));
				$sh->setOrderby('opp.enddate','asc');
				$sort_order = 'date';
				break;
			}
			case 'recently_won': {
				$sh->addConstraint(new Constraint('won','=',true));
				$sh->addConstraint(new Constraint('opp.lastupdated', '>', date('Y-m-d', strtotime('-60 days'))));
				$sh->setOrderby('opp.lastupdated','desc');
				$sort_order = 'date';
				break;
			}
			case 'recently_lost': {
				$sh->addConstraint(new Constraint('won','=',false));
				$sh->addConstraint(new Constraint('open','=',false));
				$sh->addConstraint(new Constraint('opp.lastupdated', '>', date('Y-m-d', strtotime('-60 days'))));
				$sh->setOrderby('opp.lastupdated','desc');
				$sort_order = 'date';
				break;
			}
			case 'archived': {
				$sh->addConstraint(new Constraint('archived','=',true));
				$sh->setOrderby('opp.lastupdated','desc');
				$sort_order = 'date';
				break;
			}
			case 'mine': {
				$sh->addConstraint(new Constraint('opp.assigned_to','=',EGS::getUsername()));
				$sh->addConstraint(new Constraint('archived','=',false));
				$sh->setOrderby('opp.name','asc');
				break;
			}
			case 'mine_open': {
				$sh->addConstraint(new Constraint('opp.assigned_to','=',EGS::getUsername()));
				$sh->addConstraint(new Constraint('archived','=',false));
				$sh->addConstraint(new Constraint('open','=','true'));
				$sh->setOrderby('opp.name','asc');
				break;
			}
			case 'mine_open_date': {
				$sh->addConstraint(new Constraint('opp.assigned_to','=',EGS::getUsername()));
				$sh->addConstraint(new Constraint('archived','=',false));
				$sh->addConstraint(new Constraint('open','=','true'));
				$sh->setOrderby('opp.enddate','asc');
				$sort_order = 'date';
				break;
			}
			case 'most_recent': {
				$sh->addConstraint(new Constraint('archived','=',false));
				$sh->addConstraint(new Constraint('opp.lastupdated', '>', date('Y-m-d', strtotime('-30 days'))));
				$sh->setOrderby('opp.created','desc');
				$sort_order = 'date';
				break;
			}
			case 'mine_recently_won': {
				$sh->addConstraint(new Constraint('archived','=',false));
				$sh->addConstraint(new Constraint('opp.assigned_to','=',EGS::getUsername()));
				$sh->addConstraint(new Constraint('won','=',true));
				//$sh->addConstraint(new Constraint('opp.lastupdated', '>', date('Y-m-d', strtotime('-60 days'))));
				$sh->setOrderby('opp.lastupdated','desc');
				$sort_order = 'date';
				break;
			}
			case 'mine_recently_lost': {
				$sh->addConstraint(new Constraint('archived','=',false));
				$sh->addConstraint(new Constraint('opp.assigned_to','=',EGS::getUsername()));
				$sh->addConstraint(new Constraint('won','=',false));
				$sh->addConstraint(new Constraint('open','=',false));
				//$sh->addConstraint(new Constraint('opp.lastupdated', '>', date('Y-m-d', strtotime('-60 days'))));
				$sh->setOrderby('opp.lastupdated','desc');
				$sort_order = 'date';
				break;
			}
		}
		$this->view->set('sort_order', $sort_order);
		$this->view->set('sort_field', $sh->getOrderBy());
		
		$this->_handleSearchFields($sh);
		Controller::index($opportunities,$sh);
		$this->setTemplateName('index');
		
		if (!$this->view->is_json) {
			$this->useTagList();
			//only want to show the 'add' link if Opps can actually be added
			AutoLoader::Instance()->addPath(APP_CLASS_ROOT.'usage/');
			$user = CurrentlyLoggedInUser::Instance();
			$account = $user->getAccount();
			$limit_checker = new LimitChecker(
				new OpportunityUsageChecker($account), 
				$account->getPlan());
			$this->view->set('add_disallowed',!$limit_checker->isWithinLimit('opportunity_limit'));
			
			$this->view->set('permission_import_enabled', Tactile_AccountMagic::getAsBoolean('permission_import_enabled', 't', 't'));
			$this->view->set('permission_export_enabled', Tactile_AccountMagic::getAsBoolean('permission_export_enabled', 't', 't'));
		}
	}
	
	protected function _handleSearchFields(SearchHandler $sh) {
		$query = array();
		$fields = array('opp.name' => 'name');
		foreach($fields as $queryfield => $field) {
			if(!empty($this->_data[$field])) {
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
		$exact_fields = array('opp.organisation_id' => 'organisation_id', 'opp.person_id' => 'person_id');
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
		foreach(array('opp.lastupdated' => 'updated', 'opp.created' => 'created') as $dbfield => $queryfield) {
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
					$sh->addConstraint(new Constraint($dbfield, $operator, $datetime));		
				}
			}
		}
		$this->view->set('current_query', http_build_query($query));
	}
	
	public function useTagList($tags=null) {
		$taggable = new TaggedItem($this->opportunity);
		$tags_to_show = $taggable->getTagList($tags);
		$this->view->set('all_tags',$tags_to_show);
	}
	
	function open() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('opportunitys_index_restriction', 'open', EGS::getUsername());
		}
		$this->useRestriction('open');
	}
	
	function open_date() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('opportunitys_index_restriction', 'open_date', EGS::getUsername());
		}
		$this->useRestriction('open_date');
	}
	
	function index() {
		if ($this->view->is_json) {
			$opp_list_type = 'open';
		} else {
			$opp_list_type = Omelette_Magic::getValue('opportunitys_index_restriction', EGS::getUsername(), 'open');
		}
		switch($opp_list_type) {
			case 'most_recent':
			case 'mine':
			case 'open':
			case 'open_date':
			case 'mine_open':
			case 'mine_open_date':
			case 'recently_won':
			case 'recently_lost':
			case 'archived':
			case 'recently_viewed':
				break;
			default:
				$opp_list_type = 'open';
				break;
		}
		$this->$opp_list_type();
		
		UsageWarningHelper::displayUsageWarning($this->view, 'opportunities');
	}
	
	function search() {
		$this->useRestriction('open');
		$this->view->set('sub_title', 'Matching Search Query');
	}
	
	function recently_won() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('opportunitys_index_restriction', 'recently_won', EGS::getUsername());
		}
		$this->useRestriction('recently_won');
	}
	
	function all() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('opportunitys_index_restriction', 'all', EGS::getUsername());
		}
		$this->useRestriction('all');
	}

	function recently_lost() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('opportunitys_index_restriction', 'recently_lost', EGS::getUsername());
		}
		$this->useRestriction('recently_lost');
	}
	
	function archived() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('opportunitys_index_restriction', 'archived', EGS::getUsername());
		}
		$this->useRestriction('archived');
	}
	
	function mine() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('opportunitys_index_restriction', 'mine', EGS::getUsername());
		}
		$this->useRestriction('mine');
	}
	
	function mine_open() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('opportunitys_index_restriction', 'mine_open', EGS::getUsername());
		}
		$this->useRestriction('mine_open');
	}
	
	function mine_open_date() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('opportunitys_index_restriction', 'mine_open_date', EGS::getUsername());
		}
		$this->useRestriction('mine_open_date');
	}
	
	function mine_recently_won() {
		$this->useRestriction('mine_recently_won');
	}
	
	function mine_recently_lost() {
		$this->useRestriction('mine_recently_lost');
	}
	
	function most_recent() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('opportunitys_index_restriction', 'most_recent', EGS::getUsername());
		}
		$this->useRestriction('most_recent');
	}
	
	function view() {
		if(!isset($this->_data['id']) || false===$this->opportunity->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid id specified');
			sendTo();
			return;
		}
		if(!$this->opportunity->canView()) {
			Flash::Instance()->addError('You don\'t have permission to view that Opportunity');
			sendTo('opportunities');
			return;
		}
		
		$c = DataObject::Construct('Organisation');
		$p = DataObject::Construct('Person');
		
		$this->uses($c);
		$this->uses($p);
		$p->load($this->_uses['Opportunity']->person_id);
		$c->load($this->_uses['Opportunity']->organisation_id);
		
		if($this->opportunity->is_archived()) {
			//only want to allow unarchiving if within limits
			AutoLoader::Instance()->addPath(APP_CLASS_ROOT.'usage/');
			$user = CurrentlyLoggedInUser::Instance();
			$account = $user->getAccount();
			$limit_checker = new LimitChecker(
				new OpportunityUsageChecker($account), 
				$account->getPlan()
			);
			$this->view->set('within_opp_limit', $limit_checker->isWithinLimit('opportunity_limit'));
		}
		
		if (!$this->view->is_json) {
			$this->_getCustomFields();
			
			$fields = array(
				'organisation',
				'cost' => 'Value',
				'person',
				'probability',
				'enddate' => 'Expected Close',
				'assigned_to'
			);
			$crms = array(
				'status' => 'Pipeline Stage',
				'source',
				'type'
			);
			$summary_groups = array($fields, $crms);
			$this->view->set('summary_groups', $summary_groups);
			$view_summary_stats = Omelette_Magic::getAsBoolean('view_summary_stats', EGS::getUsername(), 't', 't');
			$view_summary_info = Omelette_Magic::getAsBoolean('view_summary_info', EGS::getUsername(), 't', 't');
			$view_recent_activity = Omelette_Magic::getAsBoolean('view_recent_activity', EGS::getUsername(), 't', 't');
			$this->view->set('view_summary_stats', $view_summary_stats);
			$this->view->set('view_summary_info', $view_summary_info);
			$this->view->set('view_recent_activity', $view_recent_activity);
			
			/* This is so we can show the orgs contact details */
			$org = new Tactile_Organisation();
			if (FALSE !== $org->load($this->opportunity->organisation_id)) {
				$methods = new OrganisationcontactmethodCollection();
				$sh = new SearchHandler($methods, false);
				$sh->addConstraint(new Constraint('organisation_id', '=' , $this->opportunity->organisation_id));
				$sh->setOrderby('position, main desc, name');
				$methods->load($sh);
				$this->view->set('organisation_contact_methods', $methods);
			}
			
			$per = new Tactile_Person();
			if (FALSE !== $per->load($this->opportunity->person_id)) {
				$methods = new PersoncontactmethodCollection();
				$sh = new SearchHandler($methods, false);
				$sh->addConstraint(new Constraint('person_id', '=' , $this->opportunity->person_id));
				$sh->setOrderby('position, main desc, name');
				$methods->load($sh);
				$this->view->set('contact_methods', $methods);
			}
			
			$this->view->set('head_title', $this->opportunity->getFormatted('name'));
			$page = !empty($this->_data['timeline_page']) ? ((int)$this->_data['timeline_page']) : 1;
			$this->view->set('activity_timeline', $this->_getTimeline($page));
			$this->view->set('won', $this->opportunity->isWon());
			$this->view->set('age', $this->opportunity->age());
			$this->view->set('timeline_view', Omelette_Magic::getValue('timeline_view', EGS::getUsername(), 'list'));
			ViewedPage::createOrUpdate(ViewedPage::TYPE_OPPORTUNITY, $this->opportunity->id, EGS::getUsername(), $this->opportunity->name);
			
			$track = new ActivityTrack();
			$this->view->set('activity_tracks', $track->getAll());
		}
	}
	
	function timeline() {
		if (!$this->view->is_json) {
			sendTo('opportunities');
			return;
		}
		$opp = $this->opportunity;
		if (!isset($this->_data['id']) || false === $opp->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid id specified');
			sendTo('opportunities');
			return;
		}
		$page = !empty($this->_data['page']) ? ((int)$this->_data['page']) : 1;
		
		$this->view->set('activity_timeline', $this->_getTimeline($page));
	}

	function options() {
		if (!$this->view->is_json) {
			sendTo('opportunities');
			return;
		}
		$options = array(
			'assigned_to'	=> array(),
			'status'		=> array(),
			'source'		=> array(),
			'type'			=> array()
		);
		
		$user = new Omelette_User();
		foreach ($user->getAll() as $key => $value) {
			$options['assigned_to'][$value] = $value;
		}
		$status = new OpportunityStatus();
		foreach ($status->getAll() as $key => $value) {
			$options['status'][$key] = $value;
		}
		$source = new OpportunitySource();
		foreach ($source->getAll() as $key => $value) {
			$options['source'][$key] = $value;
		}
		$type = new OpportunityType();
		foreach ($type->getAll() as $key => $value) {
			$options['type'][$key] = $value;
		}
		
		$this->view->set('options_json', json_encode($options));
	}
	
	function opportunity_contacts() {
		if (empty($this->_data['id']) || false === $this->opportunity->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid ID');
			return;
		}
		$this->view->set('related_contacts', $this->opportunity->getRelatedContacts());
		
		// Set the default for adding a related contact
		$this->view->set('opportunity_related_contact_type', Omelette_Magic::getValue('opportunity_related_contact_type', EGS::getUsername(), 'organisation'));
	}
	
	function save() {
		if (!empty($this->_data['id'])) {
			if (false === $this->opportunity->load($this->_data['id'])) {
				Flash::Instance()->addError('Invalid id specified');
				sendTo();
				return;
			}
			$user = CurrentlyLoggedInUser::Instance();
			if(!$user->canEdit($this->opportunity)){
				Flash::Instance()->addError('You do not have permission to edit this opportunity');
				sendTo('opportunitys','view','crm',array('id'=>$this->opportunity->id));
				return;
			}
			if ($this->opportunity->is_archived()) {
				Flash::Instance()->addError('You cannot edit archived opportunities');
				sendTo('opportunitys','view','crm',array('id'=>$this->opportunity->id));
				return;
			}
		}
		
		$db = DB::Instance();
		$db->StartTrans();
		$data = isset($this->_data['Opportunity']) ? $this->_data['Opportunity'] : array();
		
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
		if (empty($data['person_id']) && !empty($data['person']) && $data['person'] !== 'Type to find') {
			$person_data = array();
			if (!empty($data['organisation_id'])) {
				$person_data['organisation_id'] = $data['organisation_id'];
			}
			if (FALSE !== ($person = Tactile_Person::factoryFromString($data['person'], $person_data, $person_errors))) {
				if ($person->save()) {
					$data['person_id'] = $person->id;
				} else {
					$errors[] = 'There was a problem saving the associated Person';
				}
			} else {
				$errors = array_merge($errors, $person_errors);
			}
		}
		
		if (empty($errors)) {
			$saver = new ModelSaver();
			$user = CurrentlyLoggedInUser::Instance();
			$opp = $saver->save($data,'Opportunity',$errors, $user);
			if($opp!==false) {
				$db->CompleteTrans();
				$this->view->set('model', $opp);
				sendTo('opportunitys','view','crm',array('id'=>$opp->id));
				return;
			}
		}
		$db->FailTrans();
		$db->CompleteTrans();
		$this->saveData();
		if(!empty($data['id'])) {
			sendTo('opportunitys','edit','crm',array('id'=>$data['id']));
			return;
		}
		sendTo('opportunitys','new','crm');
	}
	
	function save_opportunity_contact() {
		if (!empty($this->_data['Related_Contact']['opportunity_id'])) {
			if (false === $this->opportunity->load($this->_data['Related_Contact']['opportunity_id'])) {
				Flash::Instance()->addError('Invalid id specified');
				sendTo();
				return;
			}
			$user = CurrentlyLoggedInUser::Instance();
			if(!$user->canEdit($this->opportunity)){
				Flash::Instance()->addError('You do not have permission to add this contact');
				sendTo('opportunitys','view','crm',array('id'=>$this->opportunity->id));
				return;
			}
			if ($this->opportunity->is_archived()) {
				Flash::Instance()->addError('You cannot add contacts to archived opportunities');
				sendTo('opportunitys','view','crm',array('id'=>$this->opportunity->id));
				return;
			}
		}
		
		$data = isset($this->_data['Related_Contact']) ? $this->_data['Related_Contact'] : array();
		$errors = array();
		
		if (empty($data['relationship'])) {
			$errors[] = 'Relationship cannot be blank';
		}
		
		$db = DB::Instance();
		$db->StartTrans();
		
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
		} else if(!empty($data['organisation_id'])) {
			// ToDo: Check that the org exists/is readable
			$data['person_id'] = null;
		} else if (empty($data['person_id']) && !empty($data['person']) && $data['person'] !== 'Type to find') {
			$person_data = array();
			if (!empty($data['organisation_id'])) {
				$person_data['organisation_id'] = $data['organisation_id'];
			}
			if (FALSE !== ($person = Tactile_Person::factoryFromString($data['person'], $person_data, $person_errors))) {
				if ($person->save()) {
					$data['person_id'] = $person->id;
				} else {
					$errors[] = 'There was a problem saving the associated Person';
				}
			} else {
				$errors = array_merge($errors, $person_errors);
			}
		} else if(!empty($data['person_id'])) {
			// ToDo: Check that the person exists/is readable
			$data['organisation_id'] = null;
		}

		if (empty($errors)) {
			if (empty($data['person_id'])) {
				$query = '
					INSERT INTO opportunity_contacts (opportunity_id, organisation_id, relationship)
					VALUES (
						'.$db->qstr($data['opportunity_id']).',
						'.$db->qstr($data['organisation_id']).',
						'.$db->qstr($data['relationship']).'
					)
				';
			} else if(empty($data['organisation_id'])) {
				$query = '
					INSERT INTO opportunity_contacts (opportunity_id, person_id, relationship)
					VALUES (
						'.$db->qstr($data['opportunity_id']).',
						'.$db->qstr($data['person_id']).',
						'.$db->qstr($data['relationship']).'
					)
				';
			}

			if(!empty($data['organisation_id']) || !empty($data['person_id'])) {
				$db->Execute($query);
			}
			
			$db->CompleteTrans();
			
			sendTo('opportunitys','view','crm',array('id'=>$this->opportunity->id));
			return;

		}
		Flash::Instance()->addErrors($errors);
		$db->FailTrans();
		$db->CompleteTrans();
		
		sendTo('opportunitys');
	}
	
	public function unrelate_contact() {
		$db = DB::Instance();
		$user = CurrentlyLoggedInUser::Instance();
		if (empty($this->_data['id']) || !$this->opportunity->load($this->_data['id']) || !$user->canEdit($this->opportunity)) {
			Flash::Instance()->addError('You do not have permission to edit this Opportunity');
			sendTo('opportunities');
			return;
		}
		$sql = "DELETE FROM opportunity_contacts WHERE opportunity_id = " . $db->qstr($this->_data['id']) . " AND ";
		if (!empty($this->_data['person_id'])) {
			$sql .= "person_id = " . $db->qstr($this->_data['person_id']);
		} else {
			$sql .= "organisation_id = " . $db->qstr($this->_data['organisation_id']);
		}
		$db->Execute($sql);
		Flash::Instance()->addMessage('Contact unlinked');
		sendTo('opportunitys','view','crm',array('id'=>$this->opportunity->id));
	}
	
	public function _new() {
		if(!isset($this->_data['id'])) {
			$check = self::checkBeforeForm();
			if($check===false) {
				return;
			}
		}
		if(isset($this->_data['organisation_id'])) {
			$org = DataObject::Construct('Organisation');
			if (FALSE === ($org->load($this->_data['organisation_id']))) {
				unset($this->_data['organisation_id']);
			}
			else {
				$_POST['Opportunity']['organisation_id'] = $org->id;
				$_POST['Opportunity']['organisation'] = $org->name;
			}
		}
		if(isset($this->_data['person_id'])) {
			$person = DataObject::Construct('Person');
			$company = $person->load($this->_data['person_id']);
			if($person === false) {
				unset($this->_data['person_id']);
			}
			else {
				$_POST['Opportunity']['person_id'] = $person->id;
				$_POST['Opportunity']['person'] = $person->fullname;
			}
		}
		parent::_new();
	}
	
	public static function checkBeforeForm() {
		AutoLoader::Instance()->addPath(APP_CLASS_ROOT.'usage/');
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		$limit_checker = new LimitChecker(
			new OpportunityUsageChecker($account), 
			$account->getPlan());
		if(false === $limit_checker->isWithinLimit('opportunity_limit')) {
			if($user->isAccountOwner()) {
				Flash::Instance()->addError("You have as many open opportunities as your account is allowed. You must either close an existing open opportunity, or upgrade your account");
				sendTo('account/change_plan');
				return;
			}
			else {
				Flash::Instance()->addError("You have as many open opportunities as your account is allowed. You must either close an existing open opportunity, or your Account Owner will need to upgrade");
				sendTo('/opportunities/');
				return;
			}
		}
	}
	
	function edit() {
		if(!isset($this->_data['id']) || false===$this->opportunity->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid id specified');
			sendTo();
			return;
		}
		$user = CurrentlyLoggedInUser::Instance();
		if(!$user->canEdit($this->opportunity)){
			Flash::Instance()->addError('You do not have permission to edit this opportunity');
			sendTo('opportunitys','view','crm',array('id'=>$this->opportunity->id));
			return;
		}
		if ($this->opportunity->is_archived()) {
			Flash::Instance()->addError('You cannot edit archived opportunities');
			sendTo('opportunitys','view','crm',array('id'=>$this->opportunity->id));
			return;
		}
		parent::edit();
	}
	
	function delete() {
		$user = CurrentlyLoggedInUser::Instance();
		ModelDeleter::delete($this->opportunity,'Opportunity',array('opportunitys','index','crm'), $user);
	}
	
	function new_activity() {
		$flash  = Flash::Instance();
		if(!$flash->hasErrors()) {
			$_SESSION['_controller_data']=array();
		}
		$this->uses('Activity');
		$opp = $this->opportunity->load($this->_data['id']);
		$this->_data['opportunity_id']=$opp->id;
		
		$this->_data['organisation_id']=$opp->organisation_id;
		$this->_data['person_id']=$opp->person_id;
		$this->_uses['Activity']->person = $opp->person;
	}
	
	
	function get_company_id() {
		$opp = $this->opportunity;
		if (FALSE !== $opp->load($this->_data['id'])) {
			$response = array('status'=>'success','id'=>$opp->organisation_id);
			echo json_encode($response);exit;
		}				
	}
	
	function get_person_id() {
		$opp = $this->opportunity;
		$opp = $opp->load($this->_data['id']);
		if($opp!==false) {
			$response = array('status'=>'success','id'=>$opp->person_id);
			echo json_encode($response);exit;
		}				
	}
	
	public function filtered_list() {
		$opps = new Tactile_OpportunityCollection();
		$sh = new SearchHandler($opps,false);
		$sh->extractOrdering();
		$sh->extractPaging();
		
		$cc = new ConstraintChain();
		if(!empty($this->_data['name'])) {
			$cc->add(new Constraint('opp.name','ILIKE', '%' . $this->_data['name'] . '%'));
		}
		if(!empty($this->_data['organisation_id'])) {
			$cc->add(new Constraint('opp.organisation_id','=',$this->_data['organisation_id']),'AND');
		}
		$sh->addConstraintChain($cc);
		$sh->setOrderby('opp.name','asc');
		$sh->perpage = 10;
		$opps->load($sh);
		
		$model = $opps->getModel();
		$query = $model->getQueryForRecentlyViewedSearch();
		$recent = new ViewedItemCollection($model);
		$sh = new SearchHandler($recent, false);
		$sh->extractPaging();
		$sh->perpage = 3;
		$query->where($cc->__toString());
		$recent->load($sh, $query);
		
		//remove duplicates from the alphabetical list:
		foreach($recent as $opp) {
			if (false !== ($index = $opps->contains('id', $opp->id))) {
				$opps->remove($index);
			}
		}
		
		$this->view->set('field', 'name');
		$this->view->set('items', $opps);
		$this->view->set('recent', $recent);
	}
	
	public function list_all() {
		if (!$this->view->is_json) {
			sendTo('opportunities');
			return;
		}
		$collection = new Tactile_OpportunityCollection();
		$sh = new SearchHandler($collection, false);
		$sh->extractFields();
		$sh->extractPaging();
		$sh->perpage = 0;
		$sh->setOrderby('name');
		$this->_handleSearchFields($sh);
		$query = $collection->getLoadQuery($sh)->__toString();
		
		$db = DB::Instance();
		$results = $db->getArray($query);
		
		$json = array('status' => 'success', 'opportunities' => array());
		foreach ($results as $result) {
			$json['opportunities'][] = array(
				'id'				=> $result['id'],
				'name'				=> $result['name'],
				'organisation_id'	=> $result['organisation_id'],
				'organisation'		=> $result['organisation'],
				'person_id'			=> $result['person_id'],
				'person'			=> $result['person'],
				'lastupdated'		=> date('Y-m-d\TH:i:sO', strtotime($result['lastupdated'])),
				'archived'			=> $result['archived'] == 't'
			);
		}
		$this->view->set('list_all', json_encode($json));
	}
	
}
