<?php
/**
 * Saves custom field maps
 * @author pb
 * @package Mixins
 */
class CustomFieldActions {

	
	function save_custom_multi($args) {
		$customfieldsMapCollection = new CustomfieldMapCollection();
		$sh = new SearchHandler($customfieldsMapCollection, false);
		
		$errors = array();
		$global = array();
		$type="";
		if(isset($this->_data['organisation_id'])){
			$global['organisation_id']=$this->_data['organisation_id'];
			$global['hash']='org'.$this->_data['organisation_id'];
			$sh->addConstraint(new Constraint('organisation_id','=',$global['organisation_id']));
			$type="organisations";
		} else if(isset($this->_data['person_id'])){
			$global['person_id']=$this->_data['person_id'];
			$global['hash']='per'.$this->_data['person_id'];
			$sh->addConstraint(new Constraint('person_id','=',$global['person_id']));
			$type="people";
		} else if(isset($this->_data['activity_id'])){
			$global['activity_id']=$this->_data['activity_id'];
			$global['hash']='act'.$this->_data['activity_id'];
			$sh->addConstraint(new Constraint('activity_id','=',$global['activity_id']));
			$type="activities";
		} else if(isset($this->_data['opportunity_id'])){
			$global['opportunity_id']=$this->_data['opportunity_id'];
			$global['hash']='opp'.$this->_data['opportunity_id'];
			$sh->addConstraint(new Constraint('opportunity_id','=',$global['opportunity_id']));
			$type="opportunities";
		} else {
			Flash::Instance()->addError('Unknown object type!');
			return;
		}
		
		foreach($this->_data['custom_field'] as $id=>$field){
			
			$data = array(
				'field_id'=>$field['field_id'],
			);
		
			$customField = new Customfield();
			if(!$customField->load($field['field_id'])){
				Flash::Instance()->addError('Unable to find custom field.');
				continue;
			}
			if($customField->$type!='t'){
				Flash::Instance()->addError($customField->name.' is not enabled for '.$type);
				continue;
			}
			
			switch($customField->type){
				case 'n':
					$data['value_numeric'] = (float)$field['value_numeric'];
					break;
				case 't':
					$data['value'] = $field['value'];
					break;
				case 's':
					$data['option'] = $field['option'];
					break;
				case 'c':
					$data['enabled'] = (isset($field['enabled']) && $field['enabled'] == 'on');
					break;
			}
			
			if (substr($id,0,1)!='x'){
				$data['id']=$id;
			} else {
				$data = array_merge($data, $global);
			}
			
			$mapModel = DataObject::Factory($data, $errors, "CustomfieldMap");
			if ($mapModel){
				$mapModel->save();
			}
		}
		
		$sh->extractOrdering();
		$customfieldsMapCollection->load($sh);
		$this->view->set('existing_custom_fields_json',$customfieldsMapCollection->asJson());
	}
	
	function delete_custom($args) {
		$model = new CustomfieldMap();
		$model->load($this->_data['id']);
		
		$success = ModelDeleter::delete($model,"Custom Field",array());
		if($success!==false) {
			Flash::Instance()->clearMessages(); // Going to set one in the template
			$this->view->set('success',true);
		}
		else {
			$this->view->set('sucess',false);
			Flash::Instance()->addError('Deleting the item failed');
		}
		$this->setTemplatename('delete_custom');
		
		// Get remaining custom fields
		$customfieldsMapCollection = new CustomfieldMapCollection();
		$sh = new SearchHandler($customfieldsMapCollection, false);
		
		if ($model->organisation_id) {
			$sh->addConstraint(new Constraint('organisation_id','=',$model->organisation_id));
		} elseif ($model->person_id) {
			$sh->addConstraint(new Constraint('person_id','=',$model->person_id));
		} elseif ($model->opportunity_id) {
			$sh->addConstraint(new Constraint('opportunity_id','=',$model->opportunity_id));
		} elseif ($model->activity_id) {
			$sh->addConstraint(new Constraint('activity_id','=',$model->activity_id));
		}
		
		$sh->extractOrdering();
		$sh->extractPaging();
		$customfieldsMapCollection->load($sh);
		$this->view->set('existing_custom_fields_json',$customfieldsMapCollection->asJson());
	}
}
