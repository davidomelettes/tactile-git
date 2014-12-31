<?php
/**
 * @see Zend_Gdata_Entry
 */
require_once 'Zend/Gdata/Entry.php';
require_once 'Gdata/Contacts/Extension/Email.php';
require_once 'Gdata/Contacts/Extension/PhoneNumber.php';
require_once 'Gdata/Contacts/Extension/Organization.php';
require_once 'Gdata/Contacts/Extension/PostalAddress.php';

/**
 * Represents an Entry in the Google Contacts Feed
 * 
 * @author gj
 * @package Gdata
 * @subpackage Contacts
 *
 */
class Gdata_Contacts_Entry extends Zend_Gdata_Entry {
	
	/**
	 * An Array of Gdata_Contacts_Extension_Email entries representing email addresses and their metadata
	 *
	 * @var Array
	 */
	protected $_emails = array();
	
	/**
	 * An Array of Gdata_Contacts_Extension_PhoneNumber entries representing phone-numbers and their metadata
	 *
	 * @var Array
	 */
	protected $_phoneNumbers = array();
	
	/**
	 * An Array of Gdata_Contacts_Extension_PostalAddress entries representing phone-numbers and their metadata
	 *
	 * @var Array
	 */
	protected $_postalAddresses = array();
	
	/**
	 * A Gdata_Contacts_Extension_Organization entry representing the person's organization and job-title
	 *
	 * @var Gdata_Contacts_Extension_Organization
	 */
	protected $_organization;
	
 	public function __construct($element = null) {
        foreach (Gdata_Contacts::$namespaces as $nsPrefix => $nsUri) {
            $this->registerNamespace($nsPrefix, $nsUri);
        }
        parent::__construct($element);
    }
    
    /**
     * Adds extension-entries for email, phonenumber and organization. Passes others up, notably
     * the Contact's name ends up in 'title'
     *
     * @param DOMElement $child
     */
	protected function takeChildFromDOM($child) {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
		switch ($absoluteNodeName) {
	        case $this->lookupNamespace('gd') . ':' . 'email':
	            $email = new Gdata_Contacts_Extension_Email();
	            $email->transferFromDOM($child);
	            $this->_emails[] = $email;
	            break;
	        case $this->lookupNamespace('gd') . ':' . 'phoneNumber':
	            $phone = new Gdata_Contacts_Extension_PhoneNumber();
	            $phone->transferFromDOM($child);
	            $this->_phoneNumbers[] = $phone;
	            break;
			case $this->lookupNamespace('gd') . ':' . 'postalAddress':
	            $address = new Gdata_Contacts_Extension_PostalAddress();
	            $address->transferFromDOM($child);
	            $this->_postalAddresses[] = $address;
	            break;
	        case $this->lookupNamespace('gd') . ':' . 'organization':
	        	$org = new Gdata_Contacts_Extension_Organization();
	        	$org->transferFromDOM($child);
	        	$this->_organization = $org;
	        	break;
	        default:
	            parent::takeChildFromDOM($child);
	            break;
        }
    }
	
	public function getDOM($doc = null) {
		/* @var $element DOMElement */
        $element = parent::getDOM($doc);
        
        $emails = $this->emails; 
        foreach($emails as $email) {
        	$element->appendChild($email->getDOM($element->ownerDocument));
        }
        $numbers = $this->phoneNumbers;
		foreach($numbers as $number) {
        	$element->appendChild($number->getDOM($element->ownerDocument));
        }
        $addresses = $this->postalAddresses;
		foreach($addresses as $address) {
        	$element->appendChild($address->getDOM($element->ownerDocument));
        }
        if($this->_organization != null) {
        	$org = $this->_organization;
        	/*@var $org_el DOMElement */
        	$d = new DOMDocument();
        	$org_el = $org->getDOM($doc);
        	$element->appendChild($org_el);
        }
       
        return $element;
    }
}
?>