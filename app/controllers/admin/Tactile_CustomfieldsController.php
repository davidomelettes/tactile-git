<?php
/**
 * Responsible for the displaying, creating and editing of the various user-configurable values used throughout Tactile
 * 
 * @author pb
 */
class Tactile_CustomfieldsController extends Controller {
	
	public function index() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		if ($account->is_free() && !$account->in_trial()) {
			Flash::Instance()->addError("Unfortunately your trial period for premium features has run out. You can instantly upgrade your account to access Custom Fields and more.");
			sendTo('account/change_plan');
			return;
		}
		
		$collection = new CustomfieldCollection();
		$sh = new SearchHandler($collection, false);
		$sh->extract(true);
		$sh->perpage = 0;
		$sh->setOrderBy('created');
		
		$collection->load($sh);
			
		$this->view->set('values', $collection);
	}
	
	
	public function save() {
		$account = CurrentlyLoggedInUser::Instance()->getAccount();
		if ($account->is_free() && !$account->in_trial()) {
			Flash::Instance()->addError("Unfortunately your trial period for premium features has run out.");
			sendTo('admin');
			return;
		}
		
		$errors=array();
		foreach($this->_data['custom'] as $id=>$custom){	
			
			if(isset($custom['type']) && is_array($custom['type'])){
				$custom['options']=$custom['type']['option'];
				$custom['type']='s';	
			} 
			$enabled = array('organisations','people','opportunities','activities');
			foreach($enabled as $e){
				if(!isset($custom[$e])){
					$custom[$e]='f';
				}
			}
			// Check for new custom field
			if(substr($id,0,1)!='x'){
				$custom['id']= $id;
			}
			
			$saver = new ModelSaver();
			if(false===($field=$saver->save($custom,"Customfield",$errors))){

			}
			if (false  !== $field) {
				if(!empty($custom['options'])){
					foreach($custom['options'] as $k=>$val){
						$data = array(
							'field_id'=>$field->id,
							'value'=>$val
						);
						if(substr($k,0,1)!='x'){
							$data['id']=$k;
						}
						$saver = new ModelSaver();
						if(false=== ($option = $saver->save($data,"CustomfieldOption",$errors))){
							//Flash::Instance()->addErrors($errors);
						}
					}
				}				
			} else {
				// @todo
			}	
		}
		sendto('customfields');
	}
	
	public function delete() {
		$model = new Customfield();
		if(!$model->load($this->_data['id'])){
			sendto('/customfields/');
			return;
		}
		
		$success = ModelDeleter::delete($model,"Custom Field",array());
		if($success!==false) {
			$this->view->set('success',true);
		}
		else {
			$this->view->set('sucess',false);
			Flash::Instance()->addError('Deleting the item failed');
		}
		$this->setTemplatename('delete');
	}
	
	public function delete_option() {
		$opt = new CustomfieldOption();
		if(!$opt->load($this->_data['id'])){
			sendto('/customfields/');
			return;
		}
		
		$success = ModelDeleter::delete($opt, "Option", array());
		if ($success!==false) {
			$this->view->set('success',true);
		} else {
			$this->view->set('sucess',false);
			Flash::Instance()->addError('Deleting the option failed');
		}
		$this->setTemplatename('delete');
	}
}
