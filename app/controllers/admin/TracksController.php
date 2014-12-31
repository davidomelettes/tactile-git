<?php

class TracksController extends Controller {
	
	protected $activitytrack;
	
	public function __construct($module, $view = null) {
		parent::__construct($module, $view);
		$this->uses('ActivityTrack');
	}
	
	public function index() {
		$tracks = new ActivityTrackCollection($this->activitytrack);
		$sh = new SearchHandler($tracks, false);
		$sh->extract();
		$sh->setOrderby('name', 'asc');
		Controller::index($tracks, $sh);
	}
	
	public function _new() {
		parent::_new();
	}
	
	public function save() {
		$data = !empty($this->_data['ActivityTrack']) ? $this->_data['ActivityTrack'] : array();
		$saver = new ModelSaver();
		$user = CurrentlyLoggedInUser::Instance();
		
		$track = $saver->save($data, 'ActivityTrack', $errors, $user);
		if ($track !== false) {
			$this->view->set('model', $track);
			sendTo('tracks/view/'. $track->id);
			return;
		}
		$this->saveData();
		Flash::Instance()->addErrors($errors);
		sendTo('tracks/new');
	}
	
	public function view() {
		if (FALSE == $this->activitytrack->load($this->_data['id'])) {
			Flash::Instance()->addError('Failed to load that Activity Track');
			sendTo('tracks');
			return;
		}
	}
	
	public function edit() {
		if (FALSE == $this->activitytrack->load($this->_data['id'])) {
			Flash::Instance()->addError('Failed to load that Activity Track');
			sendTo('tracks');
			return;
		}
		parent::edit();
	}
	
	public function delete() {
		$user = CurrentlyLoggedInUser::Instance();
		ModelDeleter::delete($this->activitytrack, 'Activity Track', 'tracks', $user);
	}
	
	public function new_stage() {
		if (FALSE == $this->activitytrack->load($this->_data['track_id'])) {
			Flash::Instance()->addError('Failed to load that Activity Track');
			sendTo('tracks');
			return;
		}
		$this->uses('ActivityTrackStage');
		parent::_new();
	}
	
	public function save_stage() {
		if (FALSE == $this->activitytrack->load($this->_data['track_id'])) {
			Flash::Instance()->addError('Failed to load that Activity Track');
			sendTo('tracks');
			return;
		}
		$data = !empty($this->_data['ActivityTrackStage']) ? $this->_data['ActivityTrackStage'] : array();
		$saver = new ModelSaver();
		$user = CurrentlyLoggedInUser::Instance();
		
		$stage = $saver->save($data, 'ActivityTrackStage', $errors, $user);
		if ($stage !== false) {
			$this->view->set('model', $stage);
			sendTo('tracks/view/'. $stage->track_id);
			return;
		}
		$this->saveData();
		Flash::Instance()->addErrors($errors);
		sendTo('tracks/new_stage', null, null, array('track_id'=>$this->_data['track_id']));
	}
	
	public function edit_stage() {
		$this->uses('ActivityTrackStage');
		if (FALSE == $this->activitytrackstage->load($this->_data['id'])) {
			Flash::Instance()->addError('Failed to load that Activity Track Stage');
			sendTo('tracks');
			return;
		}
		
		$this->new_stage();
		$this->setTemplateName('new_stage');
		if (isset($this->_data)){
			foreach($this->_uses as $modeltype) {
				$loaded = false;
				$model = $modeltype->get_name();
				if(isset($this->_data['id'])) {
					$id=$this->_data['id'];
					$loaded = true;
				}
				else if (isset($this->_data[$model]['id'])) {
					$id=$this->_data[$model]['id'];
					$loaded = true;
				}
				if($loaded) {
					$object=$this->_uses[$model];
					$object->load($id);
				}
			}
		}
	}
	
	public function delete_stage() {
		$this->uses('ActivityTrackStage');
		if (FALSE == $this->activitytrackstage->load($this->_data['id'])) {
			Flash::Instance()->addError('Failed to load that Activity Track Stage');
			sendTo('tracks');
			return;
		}
		
		$user = CurrentlyLoggedInUser::Instance();
		ModelDeleter::delete($this->activitytrackstage, 'Activity Track Stage', 'tracks/view/'.$this->activitytrackstage->track_id, $user);
	}
	
}
