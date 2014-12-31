<?php
class Tactile_ActivitysController extends Controller {

	/**
	 * The 'used' Activity
	 *
	 * @var Tactile_Activity
	 */
	protected $activity;

	function __construct($module,$view) {
		parent::__construct($module,$view);
		$this->uses('Activity');
		$this->mixes('save_note','NoteSaver',array());
		$this->mixes('add_tag','TagHandler',array('Activity'));
		$this->mixes('remove_tag','TagHandler',array('Activity'));
		$this->mixes('activities','RelatedItemsLoader',array('activities','Opportunity'),'get_related');
		$this->mixes('save_custom_multi','CustomFieldActions',array('CustomfieldMap', 'CustomfieldMapCollection'));
		$this->mixes('delete_custom','CustomFieldActions',array('CustomfieldMap', 'CustomfieldMapCollection'));
		$this->mixes('recently_viewed','RecentlyViewedHandler',array('Activity','activitys'));

		$this->mixes('files','RelatedItemsLoader',array('s3_files','Activity'),'get_related');
		$this->mixes('new_file','S3FileHandler',array('Activity','activities'));
		$this->mixes('save_file','S3FileHandler',array('Activity','crm','activitys'));

		$this->mixes('by_type', 'FieldFilter', array('Tactile_ActivityCollection', 'type', 'activities', 'of Type'), 'by_field');
		$this->mixes('to_user', 'FieldFilter', array('Tactile_ActivityCollection', 'act.assigned_to', 'activities', 'assigned to', array('act.completed' => 'NULL')), 'by_field');
		$this->mixes('by_user', 'FieldFilter', array('Tactile_ActivityCollection', 'act.owner', 'activities', 'assigned by', array('act.completed' => 'NULL')), 'by_field');
		
		$this->mixes('mass_action', 'MassActionHandler', array('Tactile_Activity', 'activities'));
		
		$this->mixes('export', 'ExportHandler', array('activity', 'activities'));
	}

	protected function _getTimeline($page=1) {
		$timeline = new Timeline();
		$cc = new ConstraintChain();
		$cc->add(new Constraint('activity_id', '=', $this->activity->id));
		$cc->add(new Constraint('type', 'IN', "('note', 'email', 'flag', 's3file')"));
		
		$timeline->addType('note');
		$timeline->addType('email');
		$timeline->addType('flag');
		$timeline->addType('s3file');
		
		$timeline->load($cc, $page);
		
		$this->view->set('current_query', 'id='.$this->activity->id);
		$this->view->set('cur_page', $timeline->cur_page);
		$this->view->set('num_pages', $timeline->num_pages);
		$this->view->set('per_page', $timeline->per_page);
		$this->view->set('num_records', $timeline->total);
		
		$this->view->set('timeline_rss', CurrentlyLoggedInUser::Instance()->getTimelineFeedAddress() . '&amp;activity_id='.$this->activity->id);
		return $timeline;
	}

	protected function _getCustomFields() {
		$user = CurrentlyLoggedInUser::Instance();
		$account = $user->getAccount();
		if ($account->is_free() && !$account->in_trial()) {
			return false;
		}
		
		$act = $this->activity;
		
		$customfieldsCollection = $act->getCustomFields();
		$this->view->set('custom_fields', $customfieldsCollection);
		$this->view->set('custom_fields_json', $customfieldsCollection->asJson());
		
		$customfieldsMapCollection = $act->getCustomValues();
		$this->view->set('custom_fields_map', $customfieldsMapCollection);
		$this->view->set('existing_custom_fields_json', $customfieldsMapCollection->asJson());
	}
	
	public function _new() {
		parent::_new();
		if(false!==($data=$this->restoreData())&&isset($data['Activity'])) {
			$data=$data['Activity'];
				
			if(isset($data['later'])) {
				$this->view->set('later',true);
				$this->view->set('date_selected','later');
			}
			else {
				switch ($data['class']) {
					case 'event':
						if(!empty($data['date'])) {
							$this->view->set('date',$data['date']);
							if(!empty($data['time_hours'])) {
								$this->view->set('hours',$data['time_hours']);
							}
							if(!empty($data['time_minutes'])) {
								$this->view->set('minutes',$data['time_minutes']);
							}
						}
						if(!empty($data['end_date'])) {
							$this->view->set('end_date',$data['end_date']);
								
							if(!empty($data['end_time_hours'])) {
								$this->view->set('end_hours',$data['end_time_hours']);
							}
							if(!empty($data['end_time_minutes'])) {
								$this->view->set('end_minutes',$data['end_time_minutes']);
							}
							if($data['date']==date(EGS::getDateFormat(),strtotime('today')) &&
							$data['end_date']==date(EGS::getDateFormat(),strtotime('today'))) {
								$this->view->set('date_selected','today');
							} else if($data['date']==date(EGS::getDateFormat(),strtotime('tomorrow')) &&
							$data['end_date']==date(EGS::getDateFormat(),strtotime('tomorrow'))) {
								$this->view->set('date_selected','tomorrow');
							} else {
								$this->view->set('date_selected','date');
							}
						} else {
							$this->view->set('date_selected',$data['date_choice']);
						}
						break;
					case 'todo':
					default:
						if(!empty($data['date'])) {
							$this->view->set('date',$data['date']);
								
							if(!empty($data['time_hours'])) {
								$this->view->set('hours',$data['time_hours']);
							}
							if(!empty($data['time_minutes'])) {
								$this->view->set('minutes',$data['time_minutes']);
							}
							if($data['date']==date(EGS::getDateFormat(),strtotime('today'))) {
								$this->view->set('date_selected','today');
							}
							else if($data['date']==date(EGS::getDateFormat(),strtotime('tomorrow'))) {
								$this->view->set('date_selected','tomorrow');
							}
							else {
								$this->view->set('date_selected','date');
							}
						}
						else {
							$this->view->set('date_selected',$data['date_choice']);
						}
						break;
				}
			}
		}

		if(isset($this->_data['organisation_id'])) {
			$org = DataObject::Construct('Organisation');
			if (FALSE === ($org->load($this->_data['organisation_id']))) {
				unset($this->_data['organisation_id']);
			}
			else {
				$_POST['Activity']['organisation_id'] = $org->id;
				$_POST['Activity']['organisation'] = $org->name;
			}
		}
		if(isset($this->_data['person_id'])) {
			$person = DataObject::Construct('Person');
			if (FALSE === ($person->load($this->_data['person_id']))) {
				unset($this->_data['person_id']);
			}
			else {
				$_POST['Activity']['person_id'] = $person->id;
				$_POST['Activity']['person'] = $person->fullname;
			}
		}
	}

	function index() {
		if ($this->view->is_json) {
			$act_list_type = 'all';
		}
		else {
			$act_list_type = Omelette_Magic::getValue('activitys_index_restriction', EGS::getUsername(), 'mine');
		}
		switch($act_list_type) {
			case 'my_overdue':
			case 'recently_completed':
			case 'mine':
			case 'all_current':
			case 'all_overdue':
			case 'recently_viewed':
			case 'all':
				break;
			default:
				$act_list_type = 'mine';
				break;
		}
		$this->$act_list_type();
	}
	
	function search() {
		$this->useRestriction('all');
		$this->view->set('sub_title', 'Matching Search Query');
	}

	function all() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('activitys_index_restriction', 'all', EGS::getUsername());
		}
		$this->useRestriction('all');
	}

	private function useRestriction($name, $params = array()) {
		//each row will need to check the enabled status of the users, so we want to do the query now:
		//Omelette_User::FillUserCache();

		$activities = new Tactile_ActivityCollection($this->activity);
		$sh = new SearchHandler($activities,false);
		$sh->extractOrdering();
		$sh->extractPaging();
		$this->view->set('restriction',$name);
		$sort_order = 'alphabetical';
		switch($name) {
			case 'mine': {
				$sh->addConstraint(new Constraint('act.assigned_to','=',EGS::getUsername()));
				$sh->addConstraint(new Constraint('act.completed','IS','NULL'));
				$sh->setOrderBy('act.date','ASC');
				$sort_order = 'date';
				break;
			}
			case 'recently_completed': {
				$sh->addConstraint(new Constraint('act.completed','IS NOT','NULL'));
				$sh->setOrderBy('act.completed','DESC');
				$sort_order = 'date';
				break;
			}
			case 'my_overdue': {
				$sh->addConstraint(new Constraint('act.assigned_to','=',EGS::getUsername()));
				$sh->addConstraint(new Constraint('act.completed','IS','NULL'));
				$sh->addConstraint(new Constraint('act.overdue','=',true));
				$sh->setOrderBy('act.date','ASC');
				$sort_order = 'date';
				break;
			}
			case 'my_later': {
				$sh->addConstraint(new Constraint('act.assigned_to','=',EGS::getUsername()));
				$sh->addConstraint(new Constraint('act.completed','IS','NULL'));
				$sh->addConstraint(new Constraint('act.later','=',true));
				$sh->setOrderBy('act.date','ASC');
				$sort_order = 'date';
				break;
			}
			case 'my_today': {
				// TODO: Check works with timezones
				$sh->addConstraint(new Constraint('act.assigned_to','=',EGS::getUsername()));
				$sh->addConstraint(new Constraint('act.completed','IS','NULL'));
				//$sh->addConstraint(new Constraint('act.overdue', '=', false));
				$sh->addConstraint(new Constraint('act.due::date', '=', date('Y m d')));
				$sh->setOrderBy('act.date','ASC');
				$sort_order = 'date';
				break;
			}
			case 'all_overdue': {
				$sh->addConstraint(new Constraint('act.overdue','=',true));
				$sh->addConstraint(new Constraint('act.completed','IS','NULL'));
				$sh->setOrderBy('act.date','ASC');
				$sort_order = 'date';
				break;
			}
			case 'by_organisation':	//fall through
			case 'by_person':	//fall through
			case 'by_opportunity': {
				$sh->addConstraint(new Constraint('act.'.str_replace('by_','',$name).'_id','=',$this->_data['id']));
				$this->view->set('sub_title','attached to "'.$params['model']->{$params['field']}.'"');
				$sh->setOrderBy('act.date','ASC');
				$sort_order = 'date';
				break;
			}
			case 'all':
				$sh->setOrderby('act.name', 'asc');
				break;
			case 'all_current':	//fall through
			default:
				$this->view->set('restriction','all_current');
				$sh->addConstraint(new Constraint('act.completed','IS','NULL'));
				$sh->setOrderBy('act.date','ASC');
				$sort_order = 'date';
				break;
		}
		$this->view->set('sort_order', $sort_order);
		$this->view->set('sort_field', $sh->getOrderBy());
		
		$this->_handleSearchFields($sh);
		Controller::index($activities,$sh);
		$this->setTemplateName('index');

		if (!$this->view->is_json) {
			$user = CurrentlyLoggedInUser::Instance()->getModel();
			$this->view->set('webkey', $user->webkey);
			$this->useTagList();
			$this->view->set('permission_import_enabled', Tactile_AccountMagic::getAsBoolean('permission_import_enabled', 't', 't'));
			$this->view->set('permission_export_enabled', Tactile_AccountMagic::getAsBoolean('permission_export_enabled', 't', 't'));
		}
	}

	protected function _handleSearchFields(SearchHandler $sh) {
		$query = array();
		$fields = array('act.name' => 'name');
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
		$exact_fields = array('act.organisation_id' => 'organisation_id', 'act.person_id' => 'person_id', 'act.opportunity_id' => 'opportunity_id');
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
		foreach(array('act.lastupdated' => 'updated', 'act.created' => 'created', 'act.completed'=>'completed', 'act.due'=>'due') as $dbfield => $queryfield) {
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
		$taggable = new TaggedItem($this->activity);
		$tags_to_show = $taggable->getTagList($tags);
		$this->view->set('all_tags',$tags_to_show);
	}

	public function by_organisation() {
		$company = DataObject::Construct('Organisation');
		if(!isset($this->_data['id']) || false === $company->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid ID specified');
			sendTo('activities');
			return;
		}
		$this->useRestriction('by_organisation', array('model'=>$company, 'field'=>'name'));
	}

	public function by_person() {
		$person = DataObject::Construct('Person');
		if(!isset($this->_data['id']) || false === $person->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid ID specified');
			sendTo('activities');
			return;
		}
		$this->useRestriction('by_person', array('model'=>$person, 'field'=>'fullname'));
	}

	public function by_opportunity() {
		$opportunity = DataObject::Construct('Opportunity');
		if(!isset($this->_data['id']) || false === $opportunity->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid ID specified');
			sendTo('activities');
			return;
		}
		$this->useRestriction('by_opportunity', array('model'=>$opportunity, 'field'=>'name'));
	}

	function mine() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('activitys_index_restriction', 'mine', EGS::getUsername());
		}
		$this->useRestriction('mine');
	}

	function all_current() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('activitys_index_restriction', 'all_current', EGS::getUsername());
		}
		$this->useRestriction('all_current');
	}

	function recently_completed() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('activitys_index_restriction', 'recently_completed', EGS::getUsername());
		}
		$this->useRestriction('recently_completed');
	}

	function my_overdue() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('activitys_index_restriction', 'my_overdue', EGS::getUsername());
		}
		$this->useRestriction('my_overdue');
	}

	function all_overdue() {
		if (!$this->view->is_json) {
			Omelette_Magic::saveChoice('activitys_index_restriction', 'all_overdue', EGS::getUsername());
		}
		$this->useRestriction('all_overdue');
	}
	
	function my_later() {
		$this->useRestriction('my_later');
	}
	
	function my_today() {
		$this->useRestriction('my_today');
	}
	

	/**
	 * We want the TagHandler::by_tag functionality, but want to intercept
	 * so we can pre-load the users enabled statuses
	 *
	 */
	function by_tag() {
		Omelette_User::FillUserCache();
		TagHandler::by_tag(array('Tactile_Activity','activitys'));
	}

	public function timeline() {
		if (!$this->view->is_json) {
			sendTo('activities');
			return;
		}
		$act = $this->activity;
		if (!isset($this->_data['id']) || false === $act->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid id specified');
			sendTo('activities');
			return;
		}
		$page = !empty($this->_data['page']) ? ((int)$this->_data['page']) : 1;

		$this->view->set('activity_timeline', $this->_getTimeline($page));
	}

	function options() {
		if (!$this->view->is_json) {
			sendTo('activities');
			return;
		}
		$user = new Omelette_User();
		$this->view->set('assigned_to', $user->getAll());
		$type = new ActivityType();
		$this->view->set('type', $type->getAll());
	}

	/**
	 * Uses an instance of ModelSaver to save an Activity
	 * - has special logic for the handling of date/time/later
	 */
	public function save() {
		if(isset($this->_data['id']) && false===$this->activity->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid ID specified');
			sendTo('activities');
			return;
		}
		$user = CurrentlyLoggedInUser::Instance();
		if(isset($this->_data['id']) && !$user->canEdit($this->activity)) {
			Flash::Instance()->addError('You don\'t have permission to do that');
			sendTo('activities');
			return;
		}
		$saver = new ModelSaver();
		$errors = array();
		$activity_data = isset($this->_data['Activity']) ? $this->_data['Activity'] : array();
		
		$activity_data = Tactile_Activity::processFormData($activity_data);
		
		//working out 'today' and 'tomorrow' needs tz to be 'user'
		/*$user_zone = CurrentlyLoggedInUser::Instance()->getTimezoneString();
		date_default_timezone_set($user_zone);
		
		/*if (!isset($activity_data['date_choice']) && !isset($activity_data['id'])) {
			// likely an api request
			if (isset($activity_data['later']) && $activity_data['later'] != false) {
				// they've asked for later
				$activity_data['date_choice'] = 'later';
			} else {
				// default to date
				$activity_data['date_choice'] = 'date';
			}
		}
		/*if (!isset($activity_data['class'])) {
			$activity_data['class'] = (isset($activity_data['id']) ? $this->activity->class : 'todo');
		}
		/*if (isset($activity_data['date_choice'])) {
			switch($activity_data['date_choice']) {
				case 'later': {	//later means no date/time
					$activity_data['later']		= true;
					$activity_data['date']		= '';
					$activity_data['time']		= '';
					$activity_data['end_date']	= '';
					$activity_data['end_time']	= '';
					break;
				}
				case 'today': { //today means set date, handle optional time and make sure not later
					$activity_data['later'] = false;
					switch ($activity_data['class']) {
						case 'event':
							$activity_data['date'] = date(EGS::getDateFormat());
							if(!empty($activity_data['time_hours']) || !empty($activity_data['time_minutes'])) {
								$activity_data['time'] = $activity_data['time_hours'].':'.$activity_data['time_minutes'];
								$this->activity->getField('time')->setnotnull();
							}
							$activity_data['end_date'] = date(EGS::getDateFormat());
							if(!empty($activity_data['end_time_hours']) || !empty($activity_data['end_time_minutes'])) {
								$activity_data['end_time'] = $activity_data['end_time_hours'].':'.$activity_data['end_time_minutes'];
								$this->activity->getField('end_time')->setnotnull();
							}
							break;
						case 'todo':
						default:
							$activity_data['end_date']	= '';
							$activity_data['end_time']	= '';
							$activity_data['date'] = date(EGS::getDateFormat());
							if(!empty($activity_data['time_hours']) || !empty($activity_data['time_minutes'])) {
								$activity_data['time'] = $activity_data['time_hours'].':'.$activity_data['time_minutes'];
								$this->activity->getField('time')->setnotnull();
							}
							break;
					}
					break;
				}
				case 'tomorrow': {	//see today
					$activity_data['later'] = false;
					switch ($activity_data['class']) {
						case 'event':
							$activity_data['date'] = date(EGS::getDateFormat(),strtotime('tomorrow'));
							if(!empty($activity_data['time_hours']) || !empty($activity_data['time_minutes'])) {
								$activity_data['time'] = $activity_data['time_hours'].':'.$activity_data['time_minutes'];
								$this->activity->getField('time')->setnotnull();
							}
							$activity_data['end_date'] = date(EGS::getDateFormat(),strtotime('tomorrow'));
							if(!empty($activity_data['end_time_hours']) || !empty($activity_data['end_time_minutes'])) {
								$activity_data['end_time'] = $activity_data['end_time_hours'].':'.$activity_data['end_time_minutes'];
								$this->activity->getField('end_time')->setnotnull();
							}
							break;
						case 'todo':
						default:
							$activity_data['end_date']	= '';
							$activity_data['end_time']	= '';
							$activity_data['date'] = date(EGS::getDateFormat(),strtotime('tomorrow'));
							if(!empty($activity_data['time_hours']) || !empty($activity_data['time_minutes'])) {
								$activity_data['time'] = $activity_data['time_hours'].':'.$activity_data['time_minutes'];
								$this->activity->getField('time')->setnotnull();
							}
							break;
					}
					break;
				}
				case 'date': {	//date becomes non-optional, handle hours and make sure not later
					$activity_data['later'] = false;
					switch ($activity_data['class']) {
						case 'event':
							$this->activity->getField('date')->setnotnull();
							if(!empty($activity_data['time_hours']) || !empty($activity_data['time_minutes'])) {
								$activity_data['time'] = $activity_data['time_hours'].':'.$activity_data['time_minutes'];
								$this->activity->getField('time')->setnotnull();
							}
							$this->activity->getField('end_date')->setnotnull();
							if(!empty($activity_data['end_time_hours']) || !empty($activity_data['end_time_minutes'])) {
								$activity_data['end_time'] = $activity_data['end_time_hours'].':'.$activity_data['end_time_minutes'];
								$this->activity->getField('end_time')->setnotnull();
							}
							break;
						case 'todo':
						default:
							$activity_data['end_date']	= '';
							$activity_data['end_time']	= '';
							$this->activity->getField('date')->setnotnull();
							if(!empty($activity_data['time_hours']) || !empty($activity_data['time_minutes'])) {
								$activity_data['time'] = $activity_data['time_hours'].':'.$activity_data['time_minutes'];
								$this->activity->getField('time')->setnotnull();
							}
							break;
					}
					break;
				}
				default: {
					throw new Exception("Invalid type of date chosen");
				}
			}

			//any day adjustments for tz need to be 'local'
			date_default_timezone_set('Europe/London');
			if($activity_data['date_choice']!='later'&&!empty($activity_data['time'])) {
				$date_part = fix_date($activity_data['date']);
				if($date_part!==false) {
					$date_string = $date_part.' '.$activity_data['time'].' '.$user_zone;
					$activity_data['date'] = date(EGS::getDateFormat(),strtotime($date_string));
				}
				if (!empty($activity_data['end_time'])) {
					$end_date_part = fix_date($activity_data['end_date']);
					if($end_date_part!==false) {
						$end_date_string = $end_date_part.' '.$activity_data['end_time'].' '.$user_zone;
						$activity_data['end_date'] = date(EGS::getDateFormat(),strtotime($end_date_string));
					}
				}
			}
		}*/

		//grab the model before we save for comparisons in a moment...
		if(isset($activity_data['id'])) {
			$before = DataObject::Construct('Activity');
			$before->load($activity_data['id']);
			//if assigned_to changes, then so must assigned_by...
			if($before->assigned_to != $activity_data['assigned_to']) {
				$activity_data['assigned_by'] = EGS::getUsername();
			}
		}
		
		$db = DB::Instance();
		$db->StartTrans();
		$errors = array();
		if (empty($activity_data['organisation_id']) && !empty($activity_data['organisation']) && $activity_data['organisation'] !== 'Type to find') {
			if (FALSE !== ($org = Tactile_Organisation::factoryFromString($activity_data['organisation'], null, $org_errors))) {
				if ($org->save()) {
					$activity_data['organisation_id'] = $org->id;
				} else {
					$errors[] = 'There was a problem saving the associated Organisation';
				}
			} else {
				$errors = array_merge($errors, $org_errors);
			}
		}
		if (empty($activity_data['person_id']) && !empty($activity_data['person']) && $activity_data['person'] !== 'Type to find') {
			$person_data = array();
			if (!empty($activity_data['organisation_id'])) {
				$person_data['organisation_id'] = $activity_data['organisation_id'];
			}
			if (FALSE !== ($person = Tactile_Person::factoryFromString($activity_data['person'], $person_data, $person_errors))) {
				if ($person->save()) {
					$activity_data['person_id'] = $person->id;
				} else {
					$errors[] = 'There was a problem saving the associated Person';
				}
			} else {
				$errors = array_merge($errors, $person_errors);
			}
		}
		if (empty($activity_data['opportunity_id']) && !empty($activity_data['opportunity']) && $activity_data['opportunity'] !== 'Type to find') {
			$opp_data = array();
			if (!empty($activity_data['organisation_id'])) {
				$opp_data['organisation_id'] = $activity_data['organisation_id'];
			}
			if (!empty($activity_data['person_id'])) {
				$opp_data['person_id'] = $activity_data['person_id'];
			}
			if (FALSE !== ($opp = Tactile_Opportunity::factoryFromString($activity_data['opportunity'], $opp_data, $opp_errors))) {
				if ($opp->save()) {
					$activity_data['opportunity_id'] = $opp->id;
				} else {
					$errors[] = 'There was a problem saving the associated Opportunity';
				}
			} else {
				$errors = array_merge($errors, $opp_errors);
			}
		}
		
		//then actually save
		if (empty($errors)) {
			$activity = $saver->save($activity_data,$this->activity,$errors, $user);
			if($activity!==false) {
				$db->CompleteTrans();
				$should_send = EmailPreference::getSendStatus('activity_notification', $activity->assigned_to);
				/* @var $activity Tactile_Activity */
				$activity;
				//if it's a new assignee, then send them an email - though not for self-assigned activities
				if($should_send && $activity->assigned_to != EGS::getUsername() && ( !isset($before) || $before->assigned_to !== $activity->assigned_to)) {
					$activity->notifyAssignedTo();
				}
				//if we're not the assignee, and we're making changes then we want to let them know
				// (just that 'something' has changed- not necessarily what)
				else if($should_send && isset($before) && $activity->assigned_to != EGS::getUsername()) {
					$activity->notifyChange();
				}
				$this->view->set('model', $activity);
				sendTo('activitys','view','crm',array('id'=>$activity->id));
				return;
			}
		}
		$db->FailTrans();
		$db->CompleteTrans();
		$this->saveData();
		if(!empty($this->_data['Activity']['id'])) {
			sendTo('activitys','edit','crm',array('id'=>$this->_data['Activity']['id']));
			return;
		}
		sendTo('activitys','new','crm');
		return;
	}

	function view() {
		if(!isset($this->_data['id']) || false===$this->activity->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid id specified');
			sendTo();
			return;
		}
		if(!$this->activity->canView()) {
			Flash::Instance()->addError('You don\'t have permission to view that Activity');
			sendTo('activities');
			return;
		}

		$c = DataObject::Construct('Organisation');
		$p = DataObject::Construct('Person');
		$this->uses($c);
		$this->uses($p);
		$person_id = $this->_uses['Activity']->person_id;
		$organisation_id = $this->_uses['Activity']->organisation_id;

		if(!empty($organisation_id)) {
			$success = $c->load($organisation_id);
			if($success===false) {
				Flash::Instance()->addError('You don\'t have permission to do that');
				sendTo('activitys','index','crm');
				return;
			}
		}
		if(!empty($person_id)) {
			$p->load($person_id);
		}

		if (!$this->view->is_json) {
			$this->_getCustomFields();
			
			$fields = array(
				'organisation',
				'opportunity',
				'person',
				'assigned_string_view()' => 'Assigned To'
			);
			if ($this->activity->isEvent()) {
				$act_fields = array(
					'date_string()'		=> 'Start',
					'duration_string()'	=> 'Duration',
					'end_date_string()'	=> 'End',
					'location',
					'type',
				);
			} else {
				$act_fields = array(
					'date_string()'		=> 'Due',	
					'type',
					'completed'
				);
			}
			$summary_groups = array($fields, $act_fields);
			$this->view->set('summary_groups', $summary_groups);
			$view_summary_info = Omelette_Magic::getAsBoolean('view_summary_info', EGS::getUsername(), 't', 't');
			$view_recent_activity = Omelette_Magic::getAsBoolean('view_recent_activity', EGS::getUsername(), 't', 't');
			$this->view->set('view_summary_info', $view_summary_info);
			$this->view->set('view_recent_activity', $view_recent_activity);
			
			/* This is so we can show the orgs contact details */
			$org = new Tactile_Organisation();
			if (FALSE !== $org->load($this->activity->organisation_id)) {
				$methods = new OrganisationcontactmethodCollection();
				$sh = new SearchHandler($methods, false);
				$sh->addConstraint(new Constraint('organisation_id', '=' , $this->activity->organisation_id));
				$sh->setOrderby('position, main desc, name');
				$methods->load($sh);
				$this->view->set('organisation_contact_methods', $methods);
			}

			$per = new Tactile_Person();
			if (FALSE !== $per->load($this->activity->person_id)) {
				$methods = new PersoncontactmethodCollection();
				$sh = new SearchHandler($methods, false);
				$sh->addConstraint(new Constraint('person_id', '=' , $this->activity->person_id));
				$sh->setOrderby('position, main desc, name');
				$methods->load($sh);
				$this->view->set('contact_methods', $methods);
			}

			$this->view->set('head_title', $this->activity->getFormatted('name'));
			$page = !empty($this->_data['timeline_page']) ? ((int)$this->_data['timeline_page']) : 1;
			$this->view->set('activity_timeline', $this->_getTimeline($page));
			$this->view->set('timeline_view', Omelette_Magic::getValue('timeline_view', EGS::getUsername(), 'list'));
			ViewedPage::createOrUpdate(ViewedPage::TYPE_ACTIVITY, $this->activity->id, EGS::getUsername(), $this->activity->name);
		}
	}


	function list_index() {
		$activities = new Tactile_ActivityCollection();
		$sh = new SearchHandler($activities,false);
		$sh->extractOrdering();
		$sh->extractPaging();
		$cc = new ConstraintChain();
		$cc->add(new Constraint('completed','is','NULL'));
		$sh->addConstraintChain($cc);
		$sh->setLimit(0);
		$activities->load($sh);
		$this->view->set('activities',$activities);

		$this->view->set('assigned_usernames',$activities->pluck('assigned'));
		if(isset($_SESSION['preferences']['magic']['activity_list'])) {
			$prefs = $_SESSION['preferences']['magic']['activity_list'];
		}
		else {
			$prefs = array(str_replace('//'.USER_SPACE,'',EGS_USERNAME)=>'selected');
		}
		$this->view->set('selected_prefs',$prefs);
	}

	function edit() {
		if(!isset($this->_data['id']) || false===$this->activity->load($this->_data['id'])) {
			Flash::Instance()->addError('Invalid id specified');
			sendTo();
			return;
		}
		$act = $this->activity;
		$user = CurrentlyLoggedInUser::Instance();
		if(!$user->canEdit($act)){
			Flash::Instance()->addError('You do not have permission to edit this activity');
			sendTo('activitys','view','crm',array('id'=>$act->id));
			return;
		}
		$user_zone = CurrentlyLoggedInUser::Instance()->getTimezoneString();
		$date = $act->date;
		$time = $act->time;
		$end_date = $act->end_date;
		$end_time = $act->end_time;
		if(empty($time)) {
			$datetime = $date.' '.$time;
			$end_datetime = $end_date.' '.$end_time;
		} else {
			$datetime = $date.' '.$time.' Europe/London';
			$end_datetime = $end_date.' '.$end_time.' Europe/London';
		}
		
		if($act->is_later()) {
			$this->view->set('date_selected','later');
		}
		else {
			date_default_timezone_set($user_zone);
			$this->view->set('date',date(EGS::getDateFormat(),strtotime($datetime)));
			if ($act->isEvent()) {
				$this->view->set('end_date',date(EGS::getDateFormat(),strtotime($end_datetime)));
			}
			if(!empty($time)) {
				$split = explode(':',date('H:i',strtotime($datetime)));
				$hours = $split[0];
				$minutes = $split[1];
				$this->view->set('hours',$hours);
				$this->view->set('minutes',$minutes);
			}
			if(!empty($end_time)) {
				$split = explode(':',date('H:i',strtotime($end_datetime)));
				$end_hours = $split[0];
				$end_minutes = $split[1];
				$this->view->set('end_hours',$end_hours);
				$this->view->set('end_minutes',$end_minutes);
			}
				
			switch ($act->class) {
				case 'event':
					if(date('Y-m-d',strtotime($datetime)) == date('Y-m-d',strtotime('today')) &&
					date('Y-m-d',strtotime($end_datetime)) == date('Y-m-d',strtotime('today'))) {
						$this->view->set('date_selected','today');
					}
					else if(date('Y-m-d',strtotime($datetime)) == date('Y-m-d',strtotime('tomorrow')) &&
					date('Y-m-d',strtotime($end_datetime)) == date('Y-m-d',strtotime('tomorrow'))) {
						$this->view->set('date_selected','tomorrow');
					}
					else {
						$this->view->set('date_selected','date');
					}
					$this->view->set('location',$act->location);
					break;
				case 'todo':
				default:
					if(date('Y-m-d',strtotime($datetime)) == date('Y-m-d',strtotime('today'))) {
						$this->view->set('date_selected','today');
					}
					else if(date('Y-m-d',strtotime($datetime)) == date('Y-m-d',strtotime('tomorrow'))) {
						$this->view->set('date_selected','tomorrow');
					}
					else {
						$this->view->set('date_selected','date');
					}
					break;
			}
		}

		parent::edit();
	}

	function delete() {
		$user = CurrentlyLoggedInUser::Instance();
		ModelDeleter::delete($this->_uses['Activity'],'Activity',array('activitys','index','crm'), $user);
	}

	function complete() {
		$act = $this->activity;
		$user = CurrentlyLoggedInUser::Instance();
		if(!isset($this->_data['id']) || $act->load($this->_data['id'])===false) {
			Flash::Instance()->addError('Invalid activity');
			sendTo();
			return;
		}
		if(false === $user->canEdit($act)) {
			Flash::Instance()->addError('You don\'t have permission to do that');
			sendTo('activitys','view','crm',array('id'=>$this->_data['id']));
			return;
		}
		$saver = new ModelSaver();
		$errors = array();
		$activity_data = array(
			'id'		=> $act->id,
			'completed'	=> date('Y-m-d H:i:s')
		);
		$act = $saver->save($activity_data, $act, $errors, $user);
		if ($act !== false) {
			$should_send = EmailPreference::getSendStatus('activity_notification', $act->assigned_to);
			if($should_send && $act->assigned_to!==EGS::getUsername()) {
				$act->notifyCompleted();
			}
			Flash::Instance()->clearMessages();
			Flash::Instance()->addMessage('Activity Completed Successfully');
			sendTo('activitys','view','crm',array('id'=>$this->_data['id']));
			return;
		}
		Flash::Instance()->addError('Error saving Activity');
		sendTo('activitys','view','crm',array('id'=>$this->_data['id']));
		return;
	}

	function uncomplete() {
		$act = $this->activity;
		$user = CurrentlyLoggedInUser::Instance();
		if(!isset($this->_data['id']) || $act->load($this->_data['id'])===false) {
			Flash::Instance()->addError('Invalid activity');
			sendTo();
			return;
		}
		if(false === $user->canEdit($act)) {
			Flash::Instance()->addError('You don\'t have permission to do that');
			sendTo('activitys','view','crm',array('id'=>$this->_data['id']));
			return;
		}
		$saver = new ModelSaver();
		$errors = array();
		$activity_data = array(
			'id'		=> $act->id,
			'completed'	=> ''
			);
			$act = $saver->save($activity_data, $act, $errors, $user);
			if ($act !== false) {
				$should_send = EmailPreference::getSendStatus('activity_notification', $act->assigned_to);
				if($should_send && $act->assigned_to!==EGS::getUsername()) {
					$act->notifyUncompleted();
				}
				Flash::Instance()->clearMessages();
				Flash::Instance()->addMessage('Activity Uncompleted Successfully');
				sendTo('activitys','view','crm',array('id'=>$this->_data['id']));
				return;
			}
			Flash::Instance()->addError('Error saving Activity');
			sendTo('activitys','view','crm',array('id'=>$this->_data['id']));
			return;
	}

	public function filtered_list() {
		$activities = new Tactile_ActivityCollection();
		$sh = new SearchHandler($activities,false);
		$sh->extractOrdering();
		$sh->extractPaging();
		$cc = new ConstraintChain();
		if(!empty($this->_data['name'])) {
			$cc->add(new Constraint('act.name','ILIKE',$this->_data['name'].'%'));
		}
		$sh->addConstraintChain($cc);
		$sh->setLimit(20,0);
		$sh->setOrderby('act.created', 'desc');
		$activities->load($sh);
		$this->view->set('field','name');
		$this->view->set('items',$activities);
	}

	public function icalendar() {
		$act = $this->activity;
		if(!isset($this->_data['id']) || $act->load($this->_data['id'])===false) {
			Flash::Instance()->addError('Invalid activity');
			sendTo();
			return;
		}

		$c = DataObject::Construct('Organisation');
		$p = DataObject::Construct('Person');
		$this->uses($c);
		$this->uses($p);
		$person_id = $this->_uses['Activity']->person_id;
		$organisation_id = $this->_uses['Activity']->organisation_id;
		if(!empty($organisation_id)) {
			$success = $c->load($organisation_id);
			if($success===false) {
				Flash::Instance()->addError('You don\'t have permission to do that');
				sendTo('activitys','index','crm');
				return;
			}
		}

		Autoloader::Instance()->addPath(FILE_ROOT . 'omelette/lib/icalendar/');
		$cal = new VCalendar();
		$item = $act->toVCalendarItem();
		$cal->addItem($item);

		$this->view->set('icalendar', $cal->toString());

		$this->view->setContentType('text/calendar');

		$this->view->set('layout', 'blank');
	}
	
	public function list_all() {
		if (!$this->view->is_json) {
			sendTo('activities');
			return;
		}
		$collection = new Tactile_ActivityCollection();
		$sh = new SearchHandler($collection, false);
		$sh->extractFields();
		$sh->extractPaging();
		$sh->perpage = 0;
		$sh->setOrderby('act.name');
		$this->_handleSearchFields($sh);
		$query = $collection->getLoadQuery($sh)->__toString();
		
		$db = DB::Instance();
		$results = $db->getArray($query);
		
		$json = array('status' => 'success', 'activities' => array());
		foreach ($results as $result) {
			$json['activities'][] = array(
				'id'				=> $result['id'],
				'name'				=> $result['name'],
				'class'				=> $result['class'],
				'date'				=> ('' === $result['date']) ? null : date('Y-m-d', strtotime($result['date'])),
				'time'				=> $result['time'],
				'end_date'			=> ('' === $result['end_date']) ? null : date('Y-m-d', strtotime($result['date'])),
				'end_time'			=> $result['end_time'],
				'organisation_id'	=> $result['organisation_id'],
				'organisation'		=> $result['organisation'],
				'person_id'			=> $result['person_id'],
				'person'			=> $result['person'],
				'opportunity_id'	=> $result['opportunity_id'],
				'opportunity'		=> $result['opportunity'],
				'lastupdated'		=> date('Y-m-d\TH:i:sO', strtotime($result['lastupdated']))
			);
		}
		$this->view->set('list_all', json_encode($json));
	}
	
}
