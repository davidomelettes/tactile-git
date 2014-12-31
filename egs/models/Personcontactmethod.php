<?php
class Personcontactmethod extends DataObject {
	protected $defaultDisplayFields=array('name'=>'Name','contact'=>'Contact','main'=>'Main','billing'=>'Billing','shipping'=>'Shipping','payment'=>'Payment','technical'=>'Technical');
	function __construct() {
		parent::__construct('person_contact_methods');
		$this->idField='id';

		$this->identifierField='name';

 		$this->belongsTo('Person', 'person_id', 'person');
		$this->assignAutoHandler('main',new ContactMethodHandler('person_id'));
		$this->getField('name')->setDefault('Main');
	}

	function __toString() {
		$value=$this->_fields['contact']->value;
		
		if(empty($value))
			$value='';
		return $value;
	}
}
?>
