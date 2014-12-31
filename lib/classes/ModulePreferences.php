<?php

class ModulePreferences {
	private $moduleName;
	private $preferences;
	private $additionalFields;
	private $handledPreferences;
	function __construct() {
		$this->preferences = array();
		$this->additionalFields=array();
		$this->handledPreferences=array();
	}
	
	protected function registerPreference($hash) {
		$this->preferences[$hash['name']] = $hash;
	}
	protected function registerField($hash) {
		$this->additionalFields[$hash['name']]=$hash;
	}
	protected function registerHandledPreference($hash) {
		$this->handledPreferences[$hash['name']]=$hash;
	}
	protected function setModuleName($moduleName) {
		$this->moduleName = $moduleName;
	}
	
	public function generateTemplate() {
		$template = sprintf('<input type="hidden" name="__moduleName" value="%s" />', $this->moduleName);
		$fields = array_merge($this->preferences,$this->additionalFields,$this->handledPreferences);
		usort($fields,array('ModulePreferences','sortOnPosition'));
		$template.='<dl id="view_data_left">';
		foreach ($fields as $preference) {
			switch($preference['type']) {
				case 'select_multiple':
					$template .= '<dt class="for_multiple">';
					$template .= sprintf(
						'<label for="%s">%s</label>:',
						$preference['name'],
						$preference['display_name']
					);
					$template .= '</dt><dd class="for_multiple">';
					$template .= sprintf(
						'<select name="%s[]" multiple="multiple">',
						$preference['name']
					);
					
					foreach ($preference['data'] as $option) {
						$template .= sprintf(
							'<option label="%s" value="%s"%s>%s</option>',
							$option['label'],
							$option['value'],
							isset($option['selected']) && $option['selected'] ? ' selected="selected"' : '',
							$option['label']
						);
					}
					
					$template .= '</select>';
					$template .= '</dd>';
					break;
				case 'select':
					$template .= '<dt>';
					$template .= sprintf(
						'<label for="%s">%s</label>:',
						$preference['name'],
						$preference['display_name']
					);
					$template .= '</dt>';
					$template .= sprintf(
						'<select name="%s">',
						$preference['name']
					);
					
					foreach ($preference['data'] as $option) {
						$template .= sprintf(
							'<option label="%s" value="%s"%s>%s</option>',
							$option['label'],
							$option['value'],
							(isset($preference['value']) && $preference['value'] ==$option['value'])? ' selected="selected"' : '',
							$option['label']
						);
					}
					
					$template .= '</select>';
					$template .= '</dd>';
					break;
				case 'checkbox':
					$template .= sprintf(
						'<dt><label for="%s">%s</label></dt>',
						$preference['name'],
						$preference['display_name']
					);
					$template .= sprintf(
						"<dd><input type='checkbox' class='checkbox' name='%s'%s/></dd>",
						$preference['name'],
						$preference['status'] == 'on' ? ' checked="checked"' : ''
					);
					break;
				case 'numeric':
				case 'password':
				case 'text':
					$label = sprintf(
						'<dt><label for="%s">%s</label></dt>',
						$preference['name'],
						$preference['display_name']
					);
					$field = sprintf(
						'<dd><input type="'.(($preference['type']=='password')?'password':'text').'" '.(($preference['type']=='numeric')?' class="numeric"':'').' name="%s" value="%s"</dd>',
						$preference['name'],
						$preference['value']
					);
					$template.=$label.$field;
			}
		}
		$template.='</dl>';
		return $template;
	}
	
	public function getPreferenceNames() {
		return array_keys($this->preferences);
	}
	public function getHandledPreferences() {
		return $this->handledPreferences;
	}
	public function getPreference($preferenceName) {
		if (isset($this->preferences[$preferenceName])) {
			return $this->preferences[$preferenceName];
		} else {
			return null;
		}
	}
	
	public function getPreferenceDefault($preferenceName) {
		if (isset($this->preferences[$preferenceName])) {
			return $this->preferences[$preferenceName]['default'];
		} else {
			return null;
		}
	}
	public static function sortOnPosition($a,$b) {
		if ( ( !isset($a['position'])&&!isset($b['position']) ) || $a['position']==$b['position'] ) {
			return 0;
		}
		if(!isset($a['position']) || $a['position'] > $b['position']) {
			return 1;
		}
		return -1;
	}
}

?>