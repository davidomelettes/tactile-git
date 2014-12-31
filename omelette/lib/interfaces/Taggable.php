<?php
/**
 *
 * @author gj
 */
interface Taggable {
	
	/**
	 * Return a QueryBuilder instance that can be used to load the right collection
	 *
	 * @param String $tag_string
	 * @param Integer $count
	 * @return QueryBuilder
	 */
	public function getQueryForTagSearch($tag_string,$count);
	
	public function getQueryForTagDeletion($tag_string,$count);
	
	public function getQueryForRestrictedTagList($tag_string,$count);
	
	public function getQueryForFullTagList();
	
}
?>