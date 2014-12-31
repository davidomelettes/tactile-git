<?php
/**
 * Responsible for carrying out the save() operation on models
 * Treats the saving of defined 'aliases' specially, which makes things a bit easier.
 */
class ModelSaver {
	/**
	 * Takes an array of form data and a modelname and returns a saved model (or false)
	 * 
	 * Essentially a wrapper around DO::Factory and DO::save that takes care of transactions,
	 * error handling, error messages and access control
	 * 
	 * @param Array $form_data
	 * @param String $modelname
	 * @param Array &$errors
	 * @return DataObject
	 */
	function save(Array $form_data,$model,&$errors=array(), $user = null) {
		$db = DB::Instance();
		$flash = Flash::Instance();
		$db->StartTrans();
		if($errors==null) {
			$errors=array();
		}
		if($model instanceof DataObject) {
			$name=$model->get_name();
		}
		else {
			$name=$model;
			$model = DataObject::Construct($name);
		}
		if(!empty($form_data['id'])) {
			if(false===$model->load($form_data['id'])) {
				$db->FailTrans();
				$db->CompleteTrans();
				$flash->addError('Invalid ID specified');
				return false;
			}			
			if(!is_null($user)) {
				$can_edit = $user->canEdit($model);
			}
			else {
				$can_edit = $model->canEdit();
			}
			if(!$can_edit) {
				$db->FailTrans();
				$db->CompleteTrans();
				$flash->addError('You don\'t have permission to edit this item');
				return false;
			}
		}
		$model = DataObject::Factory($form_data,$errors,$model);
		if($model===false || $model->save()===false) {
			$db->FailTrans();
			$db->CompleteTrans();
			$flash->addErrors($errors,strtolower($name).'_');
			return false;
		}
		
		$flash->addMessage('Saved Successfully');
		$db->CompleteTrans();
		return $model;
	}	
	
	/**
	 * Convenience function for saving all of the aliases of a particular model, if the data is available
	 * Uses ModelSaver::save (above) to do the actual saving
	 * Uses the Constraints that make up the alias to 'guess' the defaults for missing values
	 * @param Array $form_data
	 * @param DataObject $model
	 * @param Array &$errors
	 * @return void
	 */
	function saveAliases($form_data,$model,&$errors=array()) {
		$db = DB::Instance();
		$flash = Flash::Instance();
		$db->StartTrans();
		if($errors==null) {
			$errors=array();
		}
		
		$aliases = $model->aliases;
		foreach($aliases as $name=>$info) {
			$modelname = $info['modelName'];
			if(!isset($form_data[$name])) {
				continue;
			}
			$alias_data = $form_data[$name];
			if (!is_array($alias_data)) {
				$alias_data = array(
					'id'		=> (!empty($form_data[$name.'_id']) ? $form_data[$name.'_id'] : null),
					'contact'	=> $form_data[$name],
				);
			}
			
			$req_field = $info['requiredField'];
			if(!empty($req_field) && empty($alias_data[$req_field])) {
				$current = $model->$name->$req_field;
				if(!empty($current)) {
					//it means we've cleared the value
					$model->$name->delete();
				}		
				continue;
			}
				
			$cc = $info['constraints'];
			//use the constraint for the default values:
			$values = $cc->useAsValues();
			$alias_data += $values;
			
			$alias_data[strtolower($model->get_name()).'_id'] = $model->{$model->idField};
			
			$alias = $this->save($alias_data,$modelname,$errors);
			if($alias===false) {
				$db->FailTrans();
				$db->CompleteTrans();
				return false;
			}
		}
		$db->CompleteTrans();
	}
}
?>