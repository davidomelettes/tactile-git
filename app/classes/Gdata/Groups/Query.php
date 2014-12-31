<?php
/**
 * @see Zend_Gdata_Query
 */
require_once 'Zend/Gdata/Query.php';

/**
 * Extended query object to support the extra parameters that the Contacts API supports
 * 
 * @author gj
 * @package Gdata
 * @package Contacts
 *
 */
class Gdata_Groups_Query extends Zend_Gdata_Query {
	
	const SORT_DESCENDING = 'descending';
	
	const SORT_ASCENDING = 'ascending';
	
	/**
     * Path to the contacts feed, placeholder for username.
     */
    const CONTACTS_FEED_URI = 'https://www.google.com/m8/feeds/groups/%s/full';
	
	protected $_defaultFeedUri = self::CONTACTS_FEED_URI;
	
	protected $_username;
	
	/**
	 * The Contacts API supports an orderby parameter
	 *
	 * @param String $value
	 * @return Gdata_Contacts_Query
	 */
	public function setOrderBy($value) {
		if($value != null) {
			$this->_params['orderby'] = $value;
		}
		else {
			unset($this->_params['orderby']);
		}
		return $this;
	}
	
	/**
	 * Set the showdeleted parameter, 
	 * that causes the id of recently deleted contacts to be included in results
	 *
	 * @param string $value
	 * @return Gdata_Contacts_Query
	 */
	public function setShowDeleted($value = 'true') {
		$this->_params['showdeleted'] = $value;
		return $this;
	}
	
	/**
	 * Set the order to sort by - "ascending" or "descending", default is no order
	 *
	 * @param string $value
	 * @return Gdata_Contacts_Query
	 */
	public function setSortOrder($value) {
		if($value != null) {
			$this->_params['sortorder'] = $value;
		}
		else {
			unset($this->_params['sortorder']);
		}
		return $this;
	}
	
	public function setUsername($value) {
		$this->_username = $value;
	}
	
	/**
     * @return string url
     */
    public function getQueryUrl() {
        if ($this->_url == null) {
        	if($this->_username == null) {
        		return false;
        	}
            $url = sprintf($this->_defaultFeedUri, $this->_username);
        } else {
            $url = $this->_url;
        }
        if ($this->getCategory() !== null) {
            $url .= '/-/' . $this->getCategory();
        }
        $url .= $this->getQueryString();
        return $url;
    }
	
}
?>