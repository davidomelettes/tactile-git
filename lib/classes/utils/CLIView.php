<?php
/**
 *
 * @author gj
 */
class CLIView extends BaseView {
	
	private $store = array();
	
	public function __construct(Phemto $injector) {
		
	}
	
	public function set($key,$val) {
		$this->store[$key] = $val;
	}
	
	public function getTemplateName($module,$controller,$action) {
		return 'cli';
	}
	
	public function display($template) {
		
		$flash = Flash::Instance();
		echo count($flash->errors)." Errors:\n";
		//@ signs needed due to bug in PHP 5.2.0, reading __get'd arrays in a foreach generates a notice(!?)
		foreach(@$flash->errors as $key=>$error) {
			echo $key.': '.$error."\n";
		}
		echo "\n";
		
		echo count($flash->messages)." Messages:\n";
		foreach(@$flash->messages as $message) {
			echo $message."\n";
		}
		echo "\n";
		echo "---\n";
		
	}
	
}
?>