<?php
class Customfield extends DataObject {

	public function __construct() {
		parent::__construct('custom_fields');
	}

	public function asJson() {
		return json_encode(array('custom_field'=>$this->toArray()));
	}
	
	public function toArray(){
			$json = array(
			"id"=>$this->id,
			"name"=>$this->name,
			"type"=>$this->type,
			"enabled_for"=>array(
				"organisations"=>$this->organisations,
				"opportunities"=>$this->opportunities,
				"activities"=>$this->activities,
				"people"=>$this->people
			)
		);
		
		if($this->type =='s'){
			$collection = new CustomfieldOptionCollection();
			$sh = new SearchHandler($collection, false);
			$sh->addConstraint(new Constraint('field_id',"=",$this->id));
			$sh->extract();
			$sh->perpage = 100;
			$collection->load($sh);
			foreach($collection as $option){
				$json['options'][$option->id]=array('id'=>$option->id, 'value'=>$option->value);
			}
		}
		
		return $json;
	}
	
		
}
