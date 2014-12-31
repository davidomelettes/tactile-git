<?php
class OrderSearch extends BaseSearch {
	protected $fields=array();
		
	public static function useDefault($search_data=null,&$errors) {
		$search = new OrderSearch();
		$search->addSearchField(
			'customer',
			'customer_username',
			'begins'
		);
		$search->addSearchField(
			'status',
			'status_is',
			'order_status',
			array('approved')
		);
		$search->setSearchData($search_data,$errors);
		return $search;
	}
	
}
?>