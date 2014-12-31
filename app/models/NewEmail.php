<?php
/**
 *
 * @author gj
 */
class NewEmail extends DataObject {
	
	public function __construct() {
		parent::__construct('person_contact_methods');
		$this->belongsTo('NewPerson', 'person_id', 'person');
		$this->assignAutoHandler('main',new ContactMethodHandler('person_id'));
		$this->getField('name')->setDefault('Main');
	}
	
}
?>