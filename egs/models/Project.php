<?php
class Project extends DataObject {
	protected $defaultDisplayFields=array('job_no','name','company','person','end_date','completed');
	function __construct() {
		parent::__construct('projects');
		$this->idField='id';
		
		$this->identifierField='name';
		$this->orderby='job_no';
 		$this->validateUniquenessOf(array("job_no"));
 		$this->belongsTo('Company', 'company_id', 'company');
 		$this->belongsTo('User', 'owner', 'project_owner');
 		$this->belongsTo('User', 'altered_by', 'altered');
 		$this->belongsTo('Person', 'person_id', 'person');
		$this->belongsTo('Person','key_contact_id','key_contact');
 		$this->belongsTo('Opportunity', 'opportunity_id', 'opportunity');
 		$this->belongsTo('Projectcategory', 'category_id', 'category');
 		$this->belongsTo('Projectworktype', 'work_type_id', 'work_type');
		$this->belongsTo('Projectphase', 'phase_id', 'phase'); 
		$this->hasMany('Task','tasks');
		$this->hasMany('Hour','hours');
		$tasks=new TaskCollection();
		$sh = new SearchHandler($tasks,false);
		$sh->addConstraint(new Constraint('parent_id',' is ','NULL'));
		$sh->setOrderBy('start_date');
		$this->addSearchHandler('tasks',$sh);
		$this->hasMany('Resource','resources');
		
		$this->setAccessControlled(true);
	}

	function progress($format=true) {
		$db = DB::Instance();
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
				,0)*100 AS progress FROM tasks t WHERE parent_id IS NULL AND project_id='.$db->qstr($this->id);
		$progress = $db->GetOne($query);
		if(!$format) {
			return intval($progress);
		}
		return intval($progress).'%';
	}
	function expected_progress($format=true) {
		$db = DB::Instance();
		$query = 'select (CURRENT_DATE-start_date)/(end_date-start_date)*100 from projects where id='.$db->qstr($this->id);
		$exp_progress = $db->GetOne($query);
		if(!$format) {
			return intval($exp_progress);
		}
		return intval($exp_progress).'%';
	}
	function duration() {
		$db = DB::Instance();
		$query = 'select sum(to_char(duration,\'HH24\')::float)/'.SystemCompanySettings::DAY_LENGTH.' AS duration from tasks where project_id='.$db->qstr($this->id);
		$duration = $db->GetOne($query);
		return $duration;
	}
	
	public function getMostRecentChange() {
		$db = DB::Instance();
		$query = 'SELECT lastupdated FROM tasks WHERE project_id='.$db->qstr($this->id).' ORDER BY lastupdated DESC';
		$time = $db->GetOne($query);
		return $time;
	}
	
	function opp_contact() {
		$db = DB::Instance();
		$query = 'SELECT firstname || \' \' || surname AS name FROM person p JOIN users u ON (u.person_id=p.id) JOIN opportunities o ON (o.assigned=u.username) WHERE o.id='.$this->opportunity_id;
		$name = $db->GetOne($query);
		return $name;
	}
	
	function rag_status($html=true) {
		$exp_progress=$this->expected_progress(false);
		if($html) {
			$formatter = new TrafficLightFormatter();
		}
		else {
			$formatter = new NullFormatter();
		}
	
		if($this->progress(false)<$exp_progress) {
			if($this->progress(false)<(0.95*$exp_progress)) {
				return $formatter->format('red');
			}
			return $formatter->format('amber');
		}
		return $formatter->format('green');
	}
	
	public static function getResourceUsers($id) {
		$db = DB::Instance();
		$query = 'SELECT u.username, u.username AS user FROM users u JOIN resources r ON (u.person_id=r.person_id) WHERE r.project_id='.$db->qstr($id);
		$usernames = $db->GetAssoc($query);
		return $usernames;
	}	
	
	public static function getTotalsByWorktype($from_date=null,$to_date=null) {
		$db = DB::Instance();
		$query = 'SELECT wt.title, count(p.id) AS count FROM projects p LEFT JOIN project_work_types wt ON (p.work_type_id=wt.id)';
		$cc = new ConstraintChain();
		$cc->add(new Constraint('usercompanyid','=',EGS_COMPANY_ID));
		if($from_date!==null) {
		
		}
		if($to_date!==null) {
		
		}
		$where = $cc->__toString('p');
		if(!empty($where)) {
			$query.='WHERE '.$where;
		}
		$query.=' GROUP BY wt.title';
		$data = $db->GetAssoc($query);
		return $data;
	}
	
	
	public static function getTotalsByCategory($from_date=null,$to_date=null) {
		$db = DB::Instance();
		$query = 'SELECT pc.name, count(p.id) AS count FROM projects p LEFT JOIN project_categories pc ON (p.category_id=pc.id)';
		$cc = new ConstraintChain();
		$cc->add(new Constraint('usercompanyid','=',EGS_COMPANY_ID));
		if($from_date!==null) {
		
		}
		if($to_date!==null) {
		
		}
		$where = $cc->__toString('p');
		if(!empty($where)) {
			$query.='WHERE '.$where;
		}
		$query.=' GROUP BY pc.name';
		$data = $db->GetAssoc($query);
		return $data;
	}
	
	public static function getTotalsByCountry($from_date=null,$to_date=null) {
		$db = DB::Instance();
		$query = 'SELECT co.name AS country, count(p.id) AS count FROM projects p LEFT JOIN companyoverview c ON (p.company_id=c.id) LEFT JOIN countries co ON (c.countrycode=co.code)';
		$cc = new ConstraintChain();
		$cc->add(new Constraint('usercompanyid','=',EGS_COMPANY_ID));
		if($from_date!==null) {
		
		}
		if($to_date!==null) {
		
		}
		$where = $cc->__toString('p');
		if(!empty($where)) {
			$query.='WHERE '.$where;
		}
		$query.=' GROUP BY co.name';
		$data = $db->GetAssoc($query);
		return $data;
	}
	
	public static function getTotalhoursByEquipment($from_date=null,$to_date=null) {
		$db = DB::Instance();
		
		$query = 'SELECT eq.name, sum(h.duration) FROM hours h LEFT JOIN tasks t ON (h.task_id=t.id) RIGHT JOIN project_equipment eq ON (eq.id=t.equipment_id AND equipment)';
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('usercompanyid','=',EGS_COMPANY_ID));
		if($from_date!==null) {
		
		}
		if($to_date!==null) {
		
		}
		$where = $cc->__toString('eq');
		if(!empty($where)) {
			$query.=' WHERE '.$where;
		}
		$query.=' GROUP BY eq.name';
		$data = $db->GetAssoc($query);
		return $data;
	}
	
	public static function getTotalcostByEquipment($from_date=null,$to_date=null) {
		$db = DB::Instance();
		$query = 'select eq.name, (sum(to_char(h.duration,\'HH24\')::float*eq.hourly_cost)+count(t.id)*eq.setup_cost) from hours h LEFT JOIN tasks t ON (t.id=h.task_id AND h.equipment) RIGHT JOIN project_equipment eq ON (t.equipment_id=eq.id)';
		$cc = new ConstraintChain();
		$cc->add(new Constraint('usercompanyid','=',EGS_COMPANY_ID));
		if($from_date!==null) {
		
		}
		if($to_date!==null) {
		
		}
		$where = $cc->__toString('eq');
		if(!empty($where)) {
			$query.=' WHERE '.$where;
		}
		$query.=' group by eq.name, eq.setup_cost';
		$data = $db->GetAssoc($query);
		return $data;
	}
	
	public static function getTotalHoursByHourType($from_date=null,$to_date=null)  {
		$db = DB::Instance();
		$query = 'select ht.name, sum(h.duration) FROM hours h LEFT JOIN hour_types ht ON (h.type_id=ht.id)';
		$cc = new ConstraintChain();
		$cc->add(new Constraint('usercompanyid','=',EGS_COMPANY_ID));
		if($from_date!==null) {
		
		}
		if($to_date!==null) {
		
		}
		$where = $cc->__toString('h');
		if(!empty($where)) {
			$query.=' WHERE '.$where;
		}
		$query.=' GROUP BY ht.name';
		$data = $db->GetAssoc($query);
		return $data;
	}
	
	public static function getCostsByWorktype() {
		$db = DB::Instance();
		$query = 'select wt.title, sum(to_char(h.duration,\'HH24\')::float*r.standard_rate) AS value FROM hours h LEFT JOIN projects p ON (h.project_id=p.id AND NOT equipment) JOIN resources r ON (r.project_id=p.id) JOIN users u ON (u.person_id=r.person_id) RIGHT JOIN project_work_types wt ON (p.work_type_id=wt.id)';
	
		$cc = new ConstraintChain();
		$cc->add(new Constraint('usercompanyid','=',EGS_COMPANY_ID));
		$query.=' WHERE '.$cc->__toString('p');
		$query.=' group by wt.title';
		$costs = $db->GetAssoc($query);
		$query = 'select wt.title, (sum(to_char(h.duration,\'HH24\')::float*eq.hourly_cost)+count(t.id)*eq.setup_cost) from hours h LEFT JOIN tasks t ON (t.id=h.task_id AND h.equipment) LEFT JOIN projects p ON (h.project_id=p.id) RIGHT JOIN project_work_types wt ON (p.work_type_id=t.id) RIGHT JOIN project_equipment eq ON (t.equipment_id=eq.id)';
		$cc = new ConstraintChain();
		$cc->add(new Constraint('usercompanyid','=',EGS_COMPANY_ID));
		$query.=' WHERE '.$cc->__toString('eq');
		$query.=' group by wt.title, eq.setup_cost';
		$equip_costs=$db->GetAssoc($query);
		foreach($equip_costs as $work_type=>$cost) {
			if(!empty($work_type)) {
				if(!isset($costs[$work_type])) {
					$costs[$work_type]=0;
				}
				$costs[$work_type]+=$cost;
			}
		}
		return $costs;
	}
	
	public static function getProfitLossInformation() {
		$data=array();
		$data['income'] = self::getIncomeByWorktype();
		
		$data['costs'] = self::getCostsByWorkType();
		
		$types=array_merge(array_keys($data['income']),array_keys($data['costs']));
		$diff=array();
		foreach($types as $wt) {
			if(!isset($data['income'][$wt])) {
				$data['income'][$wt]=0;
			}
			if(!isset($data['costs'][$wt])) {
				$data['costs'][$wt]=0;
			}
			$diff[$wt] = $data['income'][$wt]-$data['costs'][$wt];
		}
		$data['profit/loss']=$diff;
		ksort($data['costs']);
		ksort($data['profit/loss']);
		ksort($data['income']);
		return $data;
	}
	
	public static function getIncomeByWorktype() {
		$db = DB::Instance();
		$query = 'SELECT wt.title, sum(p.cost) FROM projects p LEFT JOIN project_work_types wt ON (p.work_type_id=wt.id)';
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('usercompanyid','=',EGS_COMPANY_ID));
		$query.=' WHERE '.$cc->__toString('p');
		
		$query.=' GROUP BY wt.title';
		$data = $db->GetAssoc($query);
		return $data;
	}
	
	public static function getEquipmentUsageByWorktype() {
		$db = DB::Instance();
		
		$query = 'SELECT wt.title AS work_type, eq.name AS equipment, coalesce(sum(to_char(h.duration,\'HH24\')::float),1) AS sum FROM project_equipment eq 
			LEFT JOIN tasks t ON (eq.id=t.equipment_id) 
			LEFT JOIN projects p ON (t.project_id=p.id)
			LEFT JOIN hours h ON (h.project_id=t.project_id AND equipment)
			LEFT JOIN project_work_types wt ON (p.work_type_id=wt.id)';

		$query.=' GROUP BY wt.title, eq.name';
		$data = $db->GetArray($query);
		$f_data=array();
		foreach($data as $row) {
			$f_data[$row['work_type']][$row['equipment']]=$row['sum'];
		}
		return $f_data;
	}
	
	public static function getProjectManagers($id) {
		$db = DB::Instance();
		$query = 'SELECT u.username, u.username AS user FROM users u JOIN resources r ON (u.person_id=r.person_id) WHERE r.project_id=' . $db->qstr($id) . ' AND r.project_manager=\'t\'';
		$usernames = $db->GetAssoc($query);
		return $usernames;
	}
}
?>
