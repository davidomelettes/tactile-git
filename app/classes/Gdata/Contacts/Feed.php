<?php
/**
 * @see Zend_Gdata_Feed
 */
require_once 'Zend/Gdata/Feed.php';

/**
 * @see Gdata_Contacts_Entry
 */
require_once 'Gdata/Contacts/Entry.php';

/**
 * A representation of the feed. Specifies that entries should be instances of Gdata_Contacts_Entry
 * 
 * @author gj
 * @package Gdata
 * @subpackage Contacts
 *
 */
class Gdata_Contacts_Feed extends Zend_Gdata_Feed {
	
    /**
     * The classname for individual item feed elements.
     *
     * @var string
     */
    protected $_entryClassName = 'Gdata_Contacts_Entry';
}
?>