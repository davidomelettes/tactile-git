<?php
class ActivitySearch extends BaseSearch {
	protected $fields=array();
		
	public static function useDefault($search_data=null,&$errors) {
		$search = new ActivitySearch();
		$search->addSearchField(
			'completed',
			'show_completed',
			'show',
			'NULL'
		);
		$search->setOffValue('completed','NULL');
		$search->addSearchField(
			'name',
			'name_contains',
			'contains'
		);
		$search->addSearchField(
			'assigned',
			'assigned_to_me',
			'hide',
			false
		);
		$search->addSearchField(
			'enddate',
			'timeframe',
			'timeframe',
			''
		);
		$search->setOnValue('assigned',EGS_USERNAME);
		
		$search->addSearchField(
			'company',
			'company_name',
			'begins',
			'',
			'advanced'
		);
		$search->addSearchField(
			'person',
			'person',
			'contains',
			'',
			'advanced'
		);
		
		$search->setSearchData($search_data,$errors);
		return $search;
	}
	
}
?>