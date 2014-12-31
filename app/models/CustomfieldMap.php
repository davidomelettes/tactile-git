<?php
class CustomfieldMap extends DataObject {

	protected $defaultDisplayFields = array('id','field_id','organisation_id','person_id','opportunity_id','activity_id','hash','value','value_numeric','enabled','option');
	
	public function __construct() {

		parent::__construct('custom_field_map');
		$this->setAdditional('type','varchar');
		$this->setAdditional('name','varchar');
		$this->setAdditional('option_name','varchar');
	}

	public function asJson() {
		return json_encode(array('custom_field_map'=>$this->toArray()));
	}
	
	public function toArray(){
		$data = array(
			"id"=>$this->id,
			"field_id"=>$this->field_id,
			"organisation_id"=>$this->organisation_id,
			"person_id"=>$this->person_id,
			"activity_id"=>$this->activity_id,
			"value"=>$this->value,
			"value_numeric"=>$this->value_numeric,
			"enabled"=>$this->enabled,
			"option"=>$this->option,
			"hash"=>$this->hash,
			"option_name"=>$this->option_name,
			"name"=>$this->name,
			"type"=>$this->type
		);		
		
		return $data;
	}
	
	protected function _touchParent() {
		$id_fields = array('organisation_id', 'person_id', 'opportunity_id', 'activity_id');
		foreach ($id_fields as $model => $field) {
			$id = $this->$field;
			if (!empty($id)) {
				$model = 'Tactile_' . ucfirst(preg_replace('/_id$/', '', $field));
				$parent = new $model();
				$fields = array('lastupdated', 'alteredby');
				$values = array('now()', EGS::getUsername());
				return $parent->update($id, $fields, $values);
			}
		}
		return false;
	}
	
	public function save() {
		if (parent::save()) {
			return $this->_touchParent();
		} else {
			return false;
		}
	}
		
}
