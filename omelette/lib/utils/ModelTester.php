<?php
class ModelTester {
	
	function test($form_data,$modelname,&$errors=array()) {
		if($errors==null) {
			$errors=array();
		}
		$model = DataObject::Factory($form_data,$errors,$modelname);
		
		if($model===false) {
			$flash->addErrors($errors,strtolower($modelname).'_');
			return false;
		}
		
		return $model;
	}
	
	function testAliases($form_data,$model,&$errors=array()) {
		if($errors==null) {
			$errors=array();
		}
		$tested_aliases=array();
		$aliases = $model->aliases;
		foreach($aliases as $name=>$info) {
			$modelname = $info['modelName'];
			if(!isset($form_data[$name])) {
				continue;
			}
			$alias_data = $form_data[$name];
			
			$req_field = $info['requiredField'];
			if(empty($alias_data[$req_field])) {
				continue;
			}
			$cc = $info['constraints'];
			//use the constraint for the default values:
			$values = $cc->useAsValues();
			$alias_data += $values;
			
			$alias = $this->test($alias_data,$modelname,$errors);
			if($alias!==false) {
				$tested_aliases[]=$alias;
			}
		}
		return $tested_aliases;
	}
	
}
?>