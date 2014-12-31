<?php
class Holidayentitlement extends DataObject {

	protected $defaultDisplayFields=array('num_days'=>'Number of Days','start_date'=>'Start Date','end_date'=>'End Date','statutory_days'=>'Statutory Days','lastupdated'=>'Last Updated');
	function __construct() {
		parent::__construct('holiday_entitlements');
		$this->idField='id';
		$this->hasMany('HolidayExtraday','extra_days');
		$this->orderby='lastupdated';
 		$this->belongsTo('Employee', 'employee_id', 'employee'); 
		$this->belongsTo('Company', 'company_id', 'company');

	}

	/**
	 * Check any overlap Holiday Entitlement.
	 * 
	 * @param	string	the start date of the holiday entitlement.
	 * @param	string	the end date of the holiday entitlement.
	 * @param	string	the employee id
	 * @return      number of any days overlaping.
 	 *
	 */
	public function overlap_entitlement($starting_date,$ending_date,$employee_id){
//		var_dump(array($starting_date,$ending_date));
		/*Change the start and end date format*/		
		$startdate=explode("/",$starting_date);
		$enddate=explode("/",$ending_date);
		
		$array1=array($startdate[2],$startdate[1],$startdate[0]);
		$array2=array($enddate[2],$enddate[1],$enddate[0]);
		
		$start_date=implode("/",$array1);
		$end_date=implode("/",$array2);

		$db=DB::Instance();	
		$query= 'select id from holiday_entitlements where (('.$db->qstr($start_date).'>=start_date and '.$db->qstr($start_date).'<=end_date) or ('.$db->qstr($end_date).'>=start_date and '.$db->qstr($end_date).'<=end_date)) and statutory_days='.$db->qstr('t').' and employee_id='.$db->qstr($employee_id);
		
		return $overlap_entitlement = $db->GetOne($query);
	}

	/**
	 * Check the number of requested dates.
	 * 
	 * @param	string	the start date of the holiday request.
	 * @param	string	the end date of the holiday request.
	 * @param	string	the employee id
	 * @return	int 	the total number of requested days
 	 *
	 */
	public function get_total_days_left($starting_date,$ending_date,$employee_id){
		/*Change the start and end date format*/		
		$startdate=explode("/",$starting_date);
		$enddate=explode("/",$ending_date);
		
		$array1=array($startdate[2],$startdate[1],$startdate[0]);
		$array2=array($enddate[2],$enddate[1],$enddate[0]);
		
		$start_date=implode("/",$array1);
		$end_date=implode("/",$array2);
		
		$db=DB::Instance();	
		/* This query gets the entitlement_id depending on the start and end date of the request*/
		$query = 'select id from holiday_entitlements where statutory_days='.$db->qstr('t').' and start_date<='.$db->qstr($start_date).' and end_date>='.$db->qstr($end_date).'and employee_id='.$db->qstr($employee_id);
		$entitlement_id = $db->GetOne($query);
		
		/* Get the sum for any Extra Days*/
		 $query='select sum(ex.num_days) as extra_days from holiday_extra_days ex join holiday_entitlements e on(e.id=ex.entitlement_period_id) where ex.employee_id='.$db->qstr($employee_id).' and ex.entitlement_period_id='.$db->qstr($entitlement_id);
		$extra_days=$db->GetOne($query);

		/* This query checks if this is the first holiday request*/
		 $query='select count(r.id) from holiday_requests r join holiday_entitlements e on(e.employee_id=r.employee_id and e.start_date<=r.start_date and e.end_date>=r.end_date) where e.employee_id='.$db->qstr($employee_id).' and approved='.$db->qstr('t').' and e.id='.$db->qstr($entitlement_id);
		$first_holiday_request=$db->GetOne($query);
		
		if($first_holiday_request==0){//This is the first holiday request
			if(!isset($extra_days)){//There are no extra days
				 $query='select e.num_days as days_left from holiday_entitlements e where e.employee_id='.$db->qstr($employee_id).' and e.id='.$db->qstr($entitlement_id).' GROUP BY e.num_days';
			}
			else{
				 $query='select e.num_days +'.$extra_days.' as days_left from holiday_entitlements e join holiday_extra_days ex on(e.id=ex.entitlement_period_id) where e.employee_id='.$db->qstr($employee_id).' and e.id='.$db->qstr($entitlement_id).' GROUP BY e.num_days';
			}
		}
		else{
			$query='select e.num_days+'.$extra_days.'-sum(r.num_days) as days_left from holiday_entitlements e join holiday_requests r on(e.employee_id=r.employee_id and e.start_date<=r.start_date and e.end_date>=r.end_date) where e.employee_id='.$db->qstr($employee_id).' and e.id='.$db->qstr($entitlement_id).' and special_circumnstances='.$db->qstr('f').' and approved='.$db->qstr('t').' GROUP BY e.num_days';
		}
		return $days_left=$db->GetOne($query);
		
	}	


}
?>
