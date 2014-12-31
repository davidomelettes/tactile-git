<?php

class Tactile_TemplatesController extends Controller {
	
	protected $emailtemplate;
	
	function __construct($module=null,$view=null) {
		parent::__construct($module,$view);
		$this->uses('EmailTemplate');
	}
	
	public function index() {
		sendTo('admin');
	}
	
	public function _new() {
		
	}
	
	public function save() {
		$template_data = isset($this->_data['EmailTemplate']) ? $this->_data['EmailTemplate'] : array();
		$saver = new ModelSaver();
		$errors = array();
		$template = $saver->save($template_data, 'EmailTemplate', $errors);
		
		if (FALSE !== $template) {
			// Yay
			sendTo('setup', 'email_templates');
			return;
		} else {
			// Boo
			if (!empty($template_data['id'])) {
				sendTo('setup', 'email_templates');
				//sendTo('templates', 'edit', 'admin', array('id'=>$template_data['id']));
				return;
			} else {
				sendTo('setup', 'email_templates');
				//sendTo('templates', 'new', 'admin');
			}
		}
	}
	
	public function delete() {
		ModelDeleter::delete($this->_uses['EmailTemplate'],'Template',array('setup', 'email_templates'));
	}
	
	public function view() {
		if (!$this->view->is_json) {
			sendTo('admin');
			return;
		}
		
		$template = new EmailTemplate();
		$id = empty($this->_data['id']) ? '' : $this->_data['id'];
		if (FALSE === $template->load($id)) {
			Flash::Instance()->addError("You don't have permission to do that");
			sendTo('admin');
			return;
		} else {
			 $this->view->set('template', $template);
		}
	}
}
