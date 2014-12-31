<?php

/**
 * @see Zend_Gdata_App_Extension
 */
require_once 'Zend/Gdata/App/Extension.php';

/**
 * Represents the gd:postalAddress elements
 * 
 * @author de
 * @package Gdata
 * @package Contacts
 *
 */
class Gdata_Contacts_Extension_PostalAddress extends Zend_Gdata_App_Extension {

	const GDATA_REL_HOME = 'http://schemas.google.com/g/2005#home';
	const GDATA_REL_OTHER = 'http://schemas.google.com/g/2005#other';
	const GDATA_REL_WORK = 'http://schemas.google.com/g/2005#work';

    protected $_rootElement = 'postalAddress';

    protected $_rootNamespace = 'gd';
    
    protected $_rel;
    
    protected $_primary;
    
    protected $_label;
    
    public function __construct($text = '', $rel = 'other') {
        parent::__construct();
        $this->_text = $text;
        $this->_rel = $rel;
    }
    
    /**
     * phone-number elements have a 'rel' and a flag for primary. the number itself is text inside the element
     *
     * @param DOMElement $child
     */
	public function transferFromDOM(DOMElement $child) {
    	$this->_rel = $child->getAttribute('rel');
    	$this->_primary = $child->getAttribute('primary') == 'true' ? true : false;
    	switch ($this->_rel) {
			case self::GDATA_REL_HOME:
				$this->_label = 'Home';
				break;
			case self::GDATA_REL_OTHER:
				$this->_label = $child->getAttribute('label');
				break;
			case self::GDATA_REL_WORK:
				$this->_label = 'Work';
				break;
			default:
				break;
    	}
    	$this->_text = $child->nodeValue;
    	
    }
    
	public function getDOM($doc = null) {
        $element = parent::getDOM($doc);
        
		if ($this->_rel != null) {
            $element->setAttribute('rel', $this->_rel);
        }
		if ($this->_primary) {
            $element->setAttribute('primary', 'true');
        }
        return $element;
    }
    
    /**
     * Returns the rel (type) of the number (namespace-URI with a #-part)
     *
     * @return string
     */
    public function getRel() {
    	return $this->_rel;
    }
    
    /**
     * Returns whether or not the number is the contact's primary number
     *
     * @return boolean
     */
    public function getPrimary() {
    	return $this->_primary;
    }
    
    public function getLabel() {
    	return $this->_label;
    }
    
}
