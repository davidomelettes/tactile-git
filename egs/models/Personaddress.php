<?php
class Personaddress extends DataObject {
	protected $defaultDisplayFields=array('name'=>'Name','address'=>'Address','main'=>'Main','billing'=>'Billing','shipping'=>'Shipping','payment'=>'Payment','technical'=>'Technical');
	function __construct() {
		parent::__construct('personaddress');
		$this->idField='id';

		$this->identifierField='name';

 		$this->belongsTo('Country', 'countrycode', 'country');
 		$this->belongsTo('Person', 'person_id', 'person');
		$this->setConcatenation('address',array('street1','street2','street3','town','county','postcode','country'),',');
		$this->assignAutoHandler('main',new AddressHandler('person_id'));
		$this->getField('name')->setDefault('Main');
	}


}
?>
