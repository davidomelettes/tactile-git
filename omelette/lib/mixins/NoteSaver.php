<?php
/**
 * Responsible for saving notes attached to things
 * @author gj
 * @package Mixins
 */
class NoteSaver {
	
	/**
	 * Saves a note
	 * @return void
	 */
	function save_note() {
		$type='Note';
		$note_data = array();
		if(isset($this->_data[$type])) {
			$note_data=$this->_data[$type];
		}
		else {
			$note_data=$this->_data;
		}
		$note = new Note();
		if(isset($note_data['id'])&&($note->load($note_data['id'])===false || $note->owner!==EGS::getUsername())) {
			Flash::Instance()->addError('You can only edit your own notes','permission');
			sendTo();
			return;			
		}
		if(!isset($note_data['private'])) {
			$note_data['private']='false';
		}
		$errors=array();
		$note = DataObject::Factory($note_data,$errors,$type);
		if($note!==false&&$note->save()!==false) {
			$note = $note->load($note->id);
			$note_array = array(
				'id'=>$note->id,
				'title'=>$note->getFormatted('title'),
				'note'=>$note->getFormatted('note'),
				'owner'=>$note->getFormatted('owner'),
				'alteredby'=>$note->getFormatted('alteredby'),
				'lastupdated'=>$note->getFormatted('lastupdated'),
				'created'=>$note->getFormatted('created'),
				'attached_things'=>array(
					'organisation'=>h($note->organisation),
					'opportunity'=>h($note->opportunity),
					'person'=>h($note->person),
					'activity'=>h($note->activity)
				),
				'private'=>$note->private
			);
			$this->view->set('note_array',$note_array);
			$this->view->set('note',$note);
		}
		else {
			Flash::Instance()->addErrors($errors);
		}
	}
}
?>