<?php

/**
 * @see Zend_Gdata_App_Extension
 */
require_once 'Zend/Gdata/App/Extension.php';

/**
 * Represents the gd:email elements in the Contacts feed
 * 
 * @author gj
 * @package Gdata
 * @package Contacts
 *
 */
class Gdata_Contacts_Extension_Email extends Zend_Gdata_App_Extension {

    protected $_rootElement = 'email';
    
    protected $_rootNamespace = 'gd';

    protected $_address;
    
    protected $_rel;
    
    protected $_primary;
    
    public function __construct($address = '', $rel = 'other', $primary = false) {
        parent::__construct();
        $this->_address = $address;
        $this->_rel = $rel;
        $this->_primary = $primary;
    }
    
    /**
     * The email address, 'rel' (the type of address; 'work', 'home' etc.) and whether or not the address
     * is the contact's Primary address are all attributes of the element
     *
     * @param DOMElement $child
     */
    public function transferFromDOM(DOMElement $child) {
    	$this->_address = $child->getAttribute('address');
    	$this->_rel = $child->getAttribute('rel');
    	$this->_primary = $child->getAttribute('primary') == 'true' ? true : false;
    }
    
	public function getDOM($doc = null) {
        $element = parent::getDOM($doc);
        if ($this->_address != null) {
            $element->setAttribute('address', $this->_address);
        }
		if ($this->_rel != null) {
            $element->setAttribute('rel', $this->_rel);
        }
		if ($this->_primary) {
            $element->setAttribute('primary', 'true');
        }
        return $element;
    }
    
    /**
     * Returns the address part of the email element
     *
     * @return string
     */
    public function getAddress() {
    	return $this->_address;
    }
    
    /**
     * Returns the rel part of the email element ('home', 'work' etc., but prefixed with the gd namespace URI
     *
     * @return string
     */
    public function getRel() {
    	return $this->_rel;
    }
    
    /**
     * Returns whether or not the address is the contact's primary address
     *
     * @return boolean
     */
    public function getPrimary() {
    	return $this->_primary;
    }

}
