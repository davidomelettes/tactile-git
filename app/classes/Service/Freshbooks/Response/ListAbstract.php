<?php
require_once 'Service/Freshbooks/Response.php';

/**
 * Abstract class for .list queries, mainly to help facillitate paging
 *
 */
abstract class Service_Freshbooks_Response_ListAbstract extends Service_Freshbooks_Response {
	
	/**
	 * Return the root-element of the list items ('clients', 'invoices' etc.)
	 *
	 */
	abstract function getListElement();
	
	/**
	 * Returns true iff there are pages after the current one
	 *
	 * @return boolean
	 */
	public function hasMorePages() {
		if($this->_xmlElement === false) {
			return false;
		}
		return  $this->getPage() < $this->getTotalPages();
	}
	
	/**
	 * Return the page number ('page' attribute of the root element)
	 *
	 * @return int
	 */
	public function getPage() {
		$el = $this->getListElement();
		return (int)$el['page'];
	}
	
	/**
	 * Returns the total number of pages ('pages' attribute of root element)
	 *
	 * @return int
	 */
	public function getTotalPages() {
		$el = $this->getListElement();
		return (int)$el['pages'];
	}
	
	/**
	 * Returns the total number of records that FreshBooks claim exist
	 *
	 * @return int
	 */
	public function getTotalRecords() {
		$el = $this->getListElement();
		return (int)$el['total'];
	}
	
	/**
	 * Return the 'per_page' attribute from the returned XML
	 *
	 * @return int
	 */
	public function getPerPage() {
		$el = $this->getListElement();
		return (int)$el['per_page'];
	}
}
