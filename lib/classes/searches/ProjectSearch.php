<?php
class ProjectSearch extends BaseSearch {
	protected $fields=array();
		
	public static function useDefault($search_data=null,&$errors) {
		$search = new ProjectSearch();
		$search->addSearchField(
			'completed',
			'show_completed',
			'show'
		);
		$search->addSearchField(
			'name',
			'name_contains',
			'contains'
		);
		
		$search->addSearchField(
			'company',
			'company_name',
			'begins',
			null,
			'advanced'
		);
		$search->addSearchField(
			'category_id',
			'category',
			'select',
			'all',
			'advanced'
		);
		$cat = new ProjectCategory();
		$cats = $cat->getAll();
		$options=array(''=>'all');
		$options += $cats;
		
		$search->setOptions('category_id',$options);
		$search->setSearchData($search_data,$errors);
		return $search;
	}
	
	public static function issues($search_data,&$errors) {
		$search = new ProjectSearch();
		$search->addSearchField(
			'problem_description',
			'description_contains',
			'contains'
		);
		$search->addSearchField(
			'closed',
			'show_closed',
			'show'
		);
		$search->addSearchField(
			'project',
			'project_name',
			'begins'
		);
		$search->addSearchField(
			'assigned_to',
			'assigned_to_me',
			'hide',
			false
		);
		$search->setOnValue('assigned_to',EGS_USERNAME);
		$search->setSearchData($search_data,$errors);
		return $search;
	}
	
}
?>