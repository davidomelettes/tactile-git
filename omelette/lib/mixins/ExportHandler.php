<?php
class ExportHandler {
	
	function export($args) {
		$type = $args[0];
		$redirect = $args[1];
		
		$permission_export_enabled = Tactile_AccountMagic::getAsBoolean('permission_export_enabled', 't', 't');
		if (!isModuleAdmin() && !$permission_export_enabled) {
			Flash::Instance()->addError('Contact exporting is disabled for non-admin users on your account');
			sendTo($redirect);
		}
	
		$task = new DelayedExport();
		$task->setType($type);
		//for the 'query=by_town&q=Manchester' type of filters
		if(!empty($this->_data['query']) && !empty($this->_data['q'])) {
			$key = str_replace('by_','',$this->_data['query']);
			if(in_array($key, DelayedExport::$allowed_query_keys)) {
					$task->setQuery($key, $this->_data['q']);
			}
			else {
				Flash::Instance()->addError("Invalid query");
				sendTo($redirect);
				return;
			}
				
		}
		//for filters not suitable for formatting as above, such as 'mine'
		if(!empty($this->_data['restriction'])) {
			switch($this->_data['restriction']) {
				case 'mine':
					$task->setQuery('assigned_to', EGS::getUsername());
					break;
				case 'open':
					$task->setQuery('open', 'true');
					break;
			}
		}
		if(!empty($this->_data['tag'])) {
			$task->setTags(array_map('urldecode',$this->_data['tag']));
		}
		$task->save();
		
		Flash::Instance()->addMessage('Your request has been queued, we will send you an email with your data shortly');
		sendTo($redirect);
		return;
	}
}
?>