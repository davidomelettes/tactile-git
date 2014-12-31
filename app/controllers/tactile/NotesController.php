<?php
/**
 *
 * @author gj
 */
class NotesController extends Controller {
	
	public function __construct($module=null,$view=null) {
		parent::__construct($module,$view);
		$this->uses('Note');
	}
	
	public function delete() {
		parent::delete('Note');
	}
	
	
	public function save() {
		NoteSaver::save_note();
	}
}
?>