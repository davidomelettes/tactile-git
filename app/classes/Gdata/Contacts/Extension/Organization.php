<?php
/**
 * @see Zend_Gdata_App_Extension
 */
require_once 'Zend/Gdata/App/Extension.php';
require_once 'Gdata/Contacts/Extension/OrgName.php';
require_once 'Gdata/Contacts/Extension/OrgTitle.php';

/**
 * Represents the gd:organization elements
 * 
 * @author gj
 * @package Gdata
 * @package Contacts
 *
 */
class Gdata_Contacts_Extension_Organization extends Zend_Gdata_App_Extension {
	
	protected $_rootElement = 'organization';
	protected $_rootNamespace = 'gd';
	
	protected $_orgName;
	
	protected $_orgTitle;
	
	protected $_primary;
	
	protected $_label;
	
	/**
	 * A programmatic value that identifies the type of organization
	 *
	 * @var string
	 */
	protected $_rel;
	
	public function __construct() {
		parent::__construct();
		foreach (Gdata_Contacts::$namespaces as $nsPrefix => $nsUri) {
            $this->registerNamespace($nsPrefix, $nsUri);
        }        
	}
	
	/**
	 * 'Primary', 'label' and 'rel' are attributes of the element.
	 *  orgTitle and orgName are elements themselves
	 *
	 * @param DOMElement $child
	 */
	public function transferFromDOM(DOMElement $child) {
    	$this->_primary = $child->getAttribute('primary')=='true'?true:false;
		$this->_label = $child->getAttribute('label');
		$this->_rel = $child->getAttribute('rel');
    	
		$orgName = new Gdata_Contacts_Extension_OrgName();		
		$orgNames = $child->getElementsByTagNameNS($this->lookupNamespace('gd'), 'orgName');
		if($orgNames->length > 0) {	
			$orgName->transferFromDOM($orgNames->item(0));
			$this->_orgName = $orgName;
		}
		
		$orgTitle = new Gdata_Contacts_Extension_OrgTitle();
		$orgTitles = $child->getElementsByTagNameNS($this->lookupNamespace('gd'), 'orgTitle');
		if($orgTitles->length > 0) {
			$orgTitle->transferFromDOM($orgTitles->item(0));
	    	$this->_orgTitle = $orgTitle;
		}
    }
    
    
	public function getDOM($doc = null) {
        $element = parent::getDOM($doc);
                
        if ($this->_label != null) {
            $element->setAttribute('label', $this->_label);
        }
		if ($this->_orgName != null) {
            $element->appendChild($this->_orgName->getDOM($element->ownerDocument));
        }
		if ($this->_orgTitle != null) {
            $element->appendChild($this->_orgTitle->getDOM($element->ownerDocument));
        }
		if ($this->_primary) {
            $element->setAttribute('primary', 'true');
        }
        return $element;
    }
}
?>