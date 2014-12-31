<?php
function smarty_function_interval($params,$smarty) {
	
	$with=&$smarty->get_template_vars('with');
	if(!empty($params['model'])) {
		$model=&$params['model'];
	}
	else {
		$model=$with['model'];
	}
	$controller_data = &$smarty->get_template_vars('controller_data');
	/*
		additional_class
		name
		id
		value
		hours_selected
		hours_hr
		days_selected
		days_hr
	*/
	$html = '
	<input type="text" class="interval%s" name="%s" id="%s" value="%s" />
	<select class="small" name="%s"><option value="hours" %s>%s</option><option value="days" %s>%s</option></select>
	';
	$modelname = get_class($model);
	$basename = $params['attribute'];
	
	$name = $modelname.'['.$basename.']';
	if(isset($params['postfix'])) {
		$name.=$params['postfix'];
	}	
	$select_name = $modelname.'['.$basename.'_unit]';
	if(isset($params['postfix'])) {
		$select_name.=$params['postfix'];
	}	
	$id = strtolower($modelname).'_'.$basename;
	$field = $model->getField($basename);
	$label = $field->tag;
	$hidden=false;
	if(isset($params['value'])) {
		$value = $params['value'];
	}
	else if(isset($controller_data[$basename])) {
		$value=$controller_data[$basename];
		$hidden=true;
	}
	else {
		$value = $field->value;
	}
	
	$hours_selected=$days_selected='';
	
	$additional_class='';
	$days_label=prettify('days');
	$hours_label=prettify('hours');
	if(!isset($value)&&$field->has_default==1) {
		$value=$field->default_value;
	}
	if(!empty($value)) {
		if(is_array($value)) {
			$units=$value[1];
			$value=$value[0];
		}
		else {
			$units = 'days';
			$value = to_working_days($value,false);
			$value = $value * SystemCompanySettings::DAY_LENGTH;
			$units = 'hours';
		}
		${$units.'_selected'}='selected="selected"';
	}
	if($hidden) {
		$wrapper_html='%s%s';
		$label_html='';
		$html = '<input type="hidden" class="interval%s" name="%s" id="%s" value="%s" />';
		$unit_value = (isset($days_selected))?'days':'hours';
		$html.='<input type="hidden" name="%s" value="'.$unit_value.'" />';
	
	}
	else {
		$wrapper_html = '<dt>%s</dt><dd>%s</dd>';
		$label_html = '<label for="%s">%s:</label>';
		$label_html=sprintf($label_html,$id,$label);
	}
	
	$html=sprintf($html,$additional_class,$name,$id,$value,$select_name,$hours_selected,$hours_label,$days_selected,$days_label);
	
	if(!isset($params['tags'])) {
		$html=sprintf($wrapper_html,$label_html,$html);
	}
	return $html;
}

?>