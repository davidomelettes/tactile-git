<?php
class PeopleSearch extends BaseSearch {
	protected $fields=array();
		
	public static function useDefault($search_data=null,&$errors) {
		$search = new CompanySearch();
		$search->addSearchField(
			'firstname',
			'firstname',
			'contains'
		);
		$search->addSearchField(
			'surname',
			'surname',
			'begins'
		);
		$search->addSearchField(
			'company',
			'company_name',
			'begins'
		);
		$search->addSearchField(
			'assigned_to',
			'assigned_to_me',
			'hide',
			false,
			'advanced'
		);
		
		$search->setOnValue('assigned_to',EGS_USERNAME);
		
		$search->addSearchField(
			'phone',
			'phone_number',
			'begins',
			'',
			'advanced'
		);
		$search->setSearchData($search_data,$errors);
		return $search;
	}
	
}
?>