<?php
function smarty_function_datetime($params,&$smarty) {

	$with=&$smarty->get_template_vars('with');
	if(!empty($params['model'])) {
		$model=&$params['model'];
	}
	else {
		$model=$with['model'];
	}
	
	if(isset($params['tags'])&& $params['tags'] == 'none'||isset($with['tags'])&&$with['tags']=='none') {
		$notags = true;
	}
	else {
		$notags = false;
	}
	$controller_data = &$smarty->get_template_vars('controller_data');
	/*
		date name
		date id
		additional_class
		date value
		hour name
		hour id
		hour value
		minute name
		minute id
		minute value		
	*/
	$html = <<<EOT
<input type="text" name="%s" id="%s" class="datefield datetimefield%s" value="%s"/>&nbsp;
<input type="text" name="%s" id="%s" class="timefield" value="%s" /><input type="text" name="%s" id="%s" class="timefield" value="%s" />
EOT;

	$basename=$params['attribute'];
	$model_name = $model->get_name();
	
	$field = $model->getField($basename);
	
	$date_name = $model_name.'['.$basename.']';
	$hour_name = $model_name.'['.$basename.'_hours]';
	$minute_name = $model_name.'['.$basename.'_minutes]';
	
	$date_id = strtolower($model_name.'_'.$basename);
	$hour_id = strtolower($model_name.'_'.$basename.'_hours');
	$minute_id = strtolower($model_name.'_'.$basename.'_minutes');
	$hidden=false;
	if(isset($controller_data[$basename])) {
		$hidden=true;
		$value = $controller_data[$basename];
	}
	else {
		$value = $field->value;
		
		if(empty($value) && $field->has_default==1) {
			$value=date(DATE_TIME_FORMAT,$field->default_value);
		}
		$label = $field->tag;
		$additional_class='';
		if($field->not_null==1) {
			$label.='*';
			$additional_class.=' required';
		}
		$label_html = '<label for="%s">%s</label>';
	
		$wrapper_html = <<<EOT
<dt>%s</dt><dd>%s</dd>	
EOT;
		$label_html = sprintf($label_html,$date_id,$label);
		if(!$notags) {
			$html = sprintf($wrapper_html,$label_html,$html);
		}
		else {
			$html = $label_html.$html;
		}
	}
	if(!empty($value)) {
	$format=format_for_strptime(DATE_TIME_FORMAT);
	
		if(strptime($value,$format)!==false) {
			$date_value = array_shift(explode(' ',$value));
			$hour_value = array_shift(explode(':',array_pop(explode(' ',$value))));
			$minute_value = array_pop(explode(':',array_pop(explode(' ',$value))));
		}
		else {
			list($date_value,$rest) = explode(' ',$value,2);
			$date_value = date(DATE_FORMAT,strtotime($date_value));
			list($hour_value,$minute_value)=explode(':',$rest);
		}
	}
	if($hidden) {
		$html=str_replace('type="text','type="hidden',$html);
	}
	$html = sprintf($html,$date_name,$date_id,$additional_class,$date_value,$hour_name,$hour_id,$hour_value,$minute_name,$minute_id,$minute_value);
	return $html;
	
}
?>