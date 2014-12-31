<?php
class Companyaddress extends DataObject {
	protected $defaultDisplayFields=array('name'=>'Name','address'=>'Address','main'=>'Main','billing'=>'Billing','shipping'=>'Shipping','payment'=>'Payment','technical'=>'Technical');
	function __construct() {
		parent::__construct('companyaddress');
		$this->idField='id';

		$this->identifierField='name';
		$this->indestructable = array('main'=>'t');
 		$this->belongsTo('Country', 'countrycode', 'country');
 		$this->belongsTo('Organisation', 'organisation_id', 'organisation');
		$this->setConcatenation('address',array('street1','street2','street3','town','county','postcode','country'),',');
		$this->assignAutoHandler('main',new AddressHandler('company_id'));
		$this->getField('name')->setDefault('Main');
	}


}
?>
