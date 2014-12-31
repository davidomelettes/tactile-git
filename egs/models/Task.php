<?php
class Task extends DataObject {
	protected $defaultDisplayFields = array('name','budget','start_date','end_date','progress','milestone','deliverable');
	function __construct() {
		parent::__construct('tasks');
		$this->idField='id';

		$this->identifierField='name';

		$this->getField('start_date')->setDefault(mktime(SystemCompanySettings::DAY_START_HOURS,SystemCompanySettings::DAY_START_MINUTES));
		$this->getField('duration')->blockValidator('NumericValidator');
		$this->setEnum('progress',getRange(0,100,10,true,'',''));

 		$this->belongsTo('Project', 'project_id', 'project');
 		$this->belongsTo('Task','parent_id','parent');
		$this->belongsTo('User', 'owner', 'task_owner');
 		$this->belongsTo('User', 'altered_by', 'altered');
		$this->belongsTo('ProjectEquipment', 'equipment_id', 'equipment');
 		$this->hasMany('TaskResource','resources');
 		$this->actsAsTree();
		$this->setParent();
 		$this->belongsTo('Taskpriority', 'priority_id', 'priority');
		$this->hasMany('Hour','hours');
		$this->orderby='start_date';
		$this->getField('budget')->setFormatter(new PriceFormatter());
		$this->getField('progress')->setFormatter(new PercentageFormatter());
		$this->getField('duration')->setFormatter(new IntervalFormatter());
		
		$this->getField('equipment_hourly_cost')->setFormatter(new PriceFormatter());
		$this->getField('equipment_setup_cost')->setFormatter(new PriceFormatter());
	}


	/**
	 * Extend save to update properties of parent-tasks
	 */
	public function save($debug=false) {
		$res = parent::save($debug);
		$p_id = $this->parent_id;
		if($res===false || empty($p_id)) {
			return $res;
		}
		$this->updateParent();
		return true;
	}
	
	private function updateParent() {
		$p_id = $this->parent_id;
		if(!empty($p_id)) {
			$parent = new Task();
			$parent->load($p_id);
			$parent->updateProperties();
		}
	}
	/**
	 * Tasks with subtasks take on the start_date, end_date, duration and progress based on their children
	 * - earliest start_date
	 * - latest end_date
	 * - sum(duration)
	 * - (sum(progress*duration))/sum(duration) for progress
	 */
	public function updateProperties() {
		$db = DB::Instance();
		$query = 'SELECT t.start_date FROM tasks t WHERE t.parent_id='.$db->qstr($this->id).' ORDER BY start_date ASC';
		$this->start_date = $db->GetOne($query);
		
		$query = 'SELECT t.end_date FROM tasks t WHERE t.parent_id='.$db->qstr($this->id).' ORDER BY end_date DESC';
		$this->end_date = $db->GetOne($query);
		
		$query = 'SELECT sum(duration) AS duration FROM tasks t WHERE t.parent_id='.$db->qstr($this->id);
		$this->duration=$db->GetOne($query);
		
		
		$query = 'SELECT coalesce(
					(
						sum(
							(progress::float/100)*(extract(hours from duration))
						)
					)
					/
					(
						sum(
							extract (hours from duration)
						)
					)
				,0)*100 AS progress FROM tasks t WHERE parent_id='.$db->qstr($this->id);
		
		$this->progress = $db->GetOne($query);
		
		$this->save();
		$this->updateParent();
	}

	protected function getEnum($name,$val) {
		if($name=='progress') {
			return $val;
		}
		return parent::getEnum($name,$val);
	}

	public function complete() {
		if ($this->_loaded) {
			$this->update($this->id,array('end_date','progress'),array('(now())',100));
			$this->updateParent();
		}
	}
	
	public function getChildrenAsDOC($doc=null,$sh=null) {
		if($doc ==null) {
			$doc = new TaskCollection();
		}
		if($sh==null) {
			$sh = new SearchHandler($doc,false);
			$sh->setOrderBy('start_date');
		}
		return parent::getChildrenAsDOC($doc,$sh);
	}

}
?>
