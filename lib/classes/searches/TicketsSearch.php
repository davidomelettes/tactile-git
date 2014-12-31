<?php
class TicketsSearch extends BaseSearch {

	public static function useDefault($search_data=null,&$errors=array()) {
		$search = new TicketsSearch();
		$search->addSearchField(
			'id',
			'ticket_#',
			'equal'
		);
		$search->addSearchField(
			'internal_status_code',
			'status_is',
			'ticket_status',
			array('NEW','OPEN')
		);
		$user = new User();
		$user->loadBy('username', EGS_USERNAME);
		$search->addSearchField(
			'originator_person_id',
			'my_tickets_only',
			'hide',
			false,
			'advanced'
		);
		$search->setOnValue('originator_person_id',$user->person_id);
		$search->addSearchField(
			'summary',
			'summary_contains',
			'contains'
		);
		$search->addSearchField(
			'assigned_to',
			'assigned_to',
			'select',
			'all'
		);
		$options=array(''=>'all',EGS_USERNAME=>'me','NULL'=>'noone');
		
		if(isModuleAdmin()) {
			$users = User::getOtherUsers();
			$options=array_merge($options,$users);
		}
		$search->setOptions('assigned_to',$options);
		$search->addSearchField(
			'originator_company',
			'company_name',
			'begins',
			null,
			'advanced'
		);
		$search->addSearchField(
			'created',
			'created_today',
			'hide',
			false,
			'advanced'
		);
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('created','>',date('Y-m-d',strtotime('yesterday'))));
		$cc->add(new Constraint('created','<',date('Y-m-d',strtotime('tomorrow'))));
		$search->setConstraint('created',$cc);
		$search->setSearchData($search_data,$errors);
		return $search;
	}
	public static function useClient($search_data=null,&$errors) {
		$search=self::useDefault($search_data,$errors);
		$search->removeSearchField('originator_person_id');
		$search->removeSearchField('originator_company');
		return $search;
	}
		
	public static function mytickets() {
		$search = new TicketsSearch();
		$search->setHidden();
		$field = new SelectSearchField('originator_person_id');
		$user = new User();
		$user->loadBy('username', EGS_USERNAME);
		$field->setValue($user->person_id);
		$search->addField('originator_person_id',$field);
		return $search;
	}
	

}
?>