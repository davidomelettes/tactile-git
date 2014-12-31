<?php
/**
 * Responsible for storing the data used for template-rendering, specifically those used for emails
 * @author gj
 * @package Views
 */
class MailView extends BaseView {
	private $template;
	private $hasHTML = false;
	/**
	 * Constructor
	 * @param Phemto [$injector]
	 */
	public function __construct($injector=null) {
		parent::__construct($injector);
		$this->set('layout','blank');
		
		$this->smarty->compile_check = true;
		$this->smarty->compile_dir=DATA_ROOT.'mail_templates_c';
		$this->smarty->clear_compiled_tpl();
		
		
		$this->register_resource('text', array(
			array($this, 'text_get_template'),
			array($this, 'text_get_timestamp'),
			array($this, 'text_get_secure'),
			array($this, 'text_get_trusted')
		));
	}
	
	public function fetch() {
		return parent::fetch($this->getTemplateFilename());
	}
	
	public function fetchHTML() {
		if ($this->hasHTML()) {
			return parent::fetch($this->getHTMLTemplateFilename());
		} else {
			return '';
		}
	}
	
	public function hasHTML() {
		return $this->hasHTML;
	}
	
	public function getTemplateName($module,$controller,$action) {
		return $this->template;
	}
	
	public function setMailTemplate($name) {
		$this->template = $name;
		
		$this->hasHTML = file_exists(APP_ROOT.'templates/mails/' . $this->template . '.html.tpl');
	}
	
	public function text_get_template($tpl_name, &$source, Smarty $smarty) {
		$source = $smarty->get_template_vars('__source');
		return true;
	}
	
	public function text_get_timestamp($tpl_name, &$timestamp, $smarty) {
		$timestamp = time();
		return true;
	}
	public function text_get_secure($tpl_name, $smarty) {
		return true;
	}
	
	public function text_get_trusted($tpl_name, $smarty) {}
	
	private function getTemplateFilename() {
		return 'mails/' . $this->template . '.tpl';
	}
	
	private function getHTMLTemplateFilename() {
		if ($this->hasHTML()) return 'mails/' . $this->template . '.html.tpl';
	}
}
?>