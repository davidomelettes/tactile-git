<?php
/**
 * This class deals with the deletion of models, and needs to be called statically (::) from within a controller so that $this points at the right thing.
 * @see DataObject::canDelete()
 */
class ModelDeleter {
	/**
	 * Checks for the existance of, and delete-access to a model and if both pass, carries out the deletion
	 * @param $model DataObject an unloaded model of the type to be deleted
	 * @param $human_name string The word(s) to use in error/success messages
	 * @param $send_to array the url (in array-form) to sendTo() when deleted/error'd
	 * @param $admin Pass a User in if we want to use it's canDelete()
	 * @return void
	 */
	function delete($model,$human_name,$send_to, $user = null) {
		if(empty($this->_data['id'])||$model->load($this->_data['id'])===false) {
			Flash::Instance()->addError('The '.$human_name.' you tried to delete doesn\'t exist');
			call_user_func_array('sendTo',$send_to);
			return;
		}
		if(!is_null($user)) {
			$can_delete = $user->canDelete($model);
		}
		else {
			$can_delete = $model->canDelete();
		}
		if(!$can_delete) {
			Flash::Instance()->addError('You don\'t have permission to delete that '.$human_name);
			call_user_func_array('sendTo',$send_to);
			return;
		}
		$success = $model->delete();
		if($success!==false) {
			Flash::Instance()->addMessage($human_name.' successfully deleted');
			call_user_func_array('sendTo',$send_to);
			return;
		}
		else {
			Flash::Instance()->addError('There was an error deleting that '.$human_name);
			call_user_func_array('sendTo',$send_to);
			return;
		}
	}	
}
?>