<?php
class CompanySearch extends BaseSearch {
	protected $fields=array();
		
	public static function useDefault($search_data=null,&$errors) {
		$search = new CompanySearch();
		$search->addSearchField(
			'name',
			'name_contains',
			'contains'
		);
		$search->addSearchField(
			'accountnumber',
			'account_number_begins',
			'begins'
		);
		$search->addSearchField(
			'is_lead',
			'',
			'show',
			false,
			'hidden'
		);
		$search->addSearchField(
			'assigned',
			'assigned_to_me',
			'hide',
			false,
			'advanced'
		);
		$search->addSearchField(
			'phone',
			'phone_number',
			'begins',
			'',
			'advanced'
		);
		$search->addSearchField(
			'town',
			'town',
			'begins',
			'',
			'advanced'
		);
		$search->setOnValue('assigned',EGS_USERNAME);
		$search->setSearchData($search_data,$errors);
		return $search;
	}
	
	public static function leads($search_data=null,&$errors) {
		$search = self::useDefault($search_data,$errors);
		$search->removeSearchField('is_lead');
		$search->addSearchField(
			'is_lead',
			'',
			'hide',
			true,
			'hidden'
		);
		return $search;
	}
	
}
?>