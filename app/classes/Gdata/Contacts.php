<?php

/**
 * @see Zend_Gdata
 */
require_once 'Zend/Gdata.php';

/**
 * Class for interatcing with the Contacts feed that Google provide
 *
 * @author gj
 * @package Gdata
 * @subpackage Contacts
 */
class Gdata_Contacts extends Zend_Gdata {
	
	/**
	 * The Base-URL of the contacts feed, also the 'scope' for authentication
	 *
	 */
	const CONTACTS_FEED_URI = 'http://www.google.com/m8/feeds/';
	
	/**
	 * The name of the service
	 *
	 */
	const AUTH_SERVICE_NAME = 'cp';
	
	protected $_defaultPostUri = self::CONTACTS_FEED_URI;
	
	protected $_registeredPackages = array(
            'Zend_Gdata_Kind',
            'Zend_Gdata_Extension',
            'Zend_Gdata',
            'Zend_Gdata_App_Extension',
            'Zend_Gdata_App');
	
	/**
	 * The namespaces used by the returned feeds
	 *
	 * @var Array
	 */
	public static $namespaces = array(
        'openSearch' => 'http://a9.com/-/spec/opensearchrss/1.0/',
        'gd' => 'http://schemas.google.com/g/2005');
	
	/**
	 * @see Zend_Gdata::__construct
	 * @param Zend_Http_Client $client
	 */
	public function __construct($client = null) {
		$this->registerPackage('Gdata_Contacts_Extension');
        parent::__construct($client);
    }
    
    /**
     * Gets the Contacts Feed from Google
     *
     * @param Mixed $location
     * @return Gdata_Contacts_Feed
     */
    public function getContactsFeed($location = null) {
        if ($location === null) {
            $uri = self::CONTACTS_FEED_URI;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Gdata_Contacts_Feed');
    }
}
?>