<?php
class OpportunitySearch extends BaseSearch {
	protected $fields=array();
		
	public static function useDefault($search_data=null,&$errors) {
		$search = new OpportunitySearch();
		$search->addSearchField(
			'open',
			'open_only',
			'hide',
			'checked'
		);
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
		$search->setOnValue('assigned',EGS_USERNAME);
		
		$search->addSearchField(
			'cost',
			'cost_>',
			'greater',
			0,
			'advanced'
		);
		
		
		$search->setSearchData($search_data,$errors);
		return $search;
	}
	
}
?>