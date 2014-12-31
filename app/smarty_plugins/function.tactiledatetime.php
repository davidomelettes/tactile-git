<?php
function smarty_function_tactiledatetime($params,&$smarty) {

	$smarty->clear_assign(array('name','hour_name','minute_name','date_id','hour_id','minute_id'));
	$smarty->clear_assign(array('label','date_value','hour_value','minute_value','is_today','is_tomorrow'));
	
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
	
	$basename=$params['attribute'];
	$model_name = $model->get_name();
	
	$field = $model->getField($basename);
	
	$smarty->assign('name',$model_name.'['.$basename.']');
	$smarty->assign('hour_name',$model_name.'['.$basename.'_hours]');
	$smarty->assign('minute_name',$model_name.'['.$basename.'_minutes]');
	
	$smarty->assign('date_id',strtolower($model_name.'_'.$basename));
	$smarty->assign('hour_id',strtolower($model_name.'_'.$basename.'_hours'));
	$smarty->assign('minute_id',strtolower($model_name.'_'.$basename.'_minutes'));
	
		
	$hidden=false;
	if(isset($controller_data[$basename])) {
		$value = $controller_data[$basename];
	}
	elseif(!empty($_POST[$model->get_name()][$params['alias']][$attribute])) {
		$value = $_POST[$model->get_name()][$params['alias']][$attribute];
	}
	else if(!empty($_SESSION['_controller_data'][$model_name][$attribute])) {
		$value=$_SESSION['_controller_data'][$model_name][$attribute];
	}
	else {
		$value = $field->value;
		if(empty($value) && $field->has_default==1) {
			$value=date(DATE_TIME_FORMAT,$field->default_value);
		}
	}
	$label = $field->tag;
	$additional_class='';
	if($field->not_null==1) {
		$label.='*';
		$additional_class.=' required';
	}

	$smarty->assign('label',$label);
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
		$smarty->assign('date_value',$date_value);
		$smarty->assign('hour_value',$hour_value);
		$smarty->assign('minute_value',$minute_value);
		
		if(date(DATE_FORMAT)==$date_value) {
			$smarty->assign('is_today',true);			
		}
		elseif(date(DATE_FORMAT,strtotime('tomorrow'))==$date_value) {
			$smarty->assign('is_tomorrow',true);
		}
		elseif(date(DATE_TIME_FORMAT,strtotime('friday 5:30pm'))) {
			$smarty->assign('this_week',true);
		}
	}
		
	return $smarty->fetch('elements/datetime.tpl');
	
}
?>