<?php

function smarty_function_textarea($params,&$smarty) {
	$value='';
	$maxlength = '';
	$additional = '';
	$attribute=$params['attribute'];
	$name = $attribute;

	if(!empty($params['model'])) {
		$model=&$params['model'];
	}
	else {
		$with=&$smarty->get_template_vars('with');
		$model=$with['model'];
//		$model=&$smarty->get_template_vars('with');
	}
	if (empty($params['alias']))
		$params['alias'] = isset($with['alias'])?$with['alias']:'';

	if(isset($model)) {
		if(!empty($params['alias'])) {
			$alias = $model->getAlias($params['alias']); 
			$aliasModelName = $alias['modelName']; 
			$newmodel = new $aliasModelName; 
			$field = $newmodel->getField($attribute);
			$name = get_class($model).'['.$params['alias'].']['.$attribute.']';
			$id = get_class($model).'_'.$params['alias'].'_'.$attribute;
			if (!empty($_POST[get_class($model)][$params['alias']][$attribute]))
				$value = $_POST[get_class($model)][$params['alias']][$attribute];
			else if($model->isLoaded()) {
				$newmodel = $model->$params['alias'];
				$value = $newmodel->$attribute;
			}
		}
		else {
			if(!empty($_POST[get_class($model)][$attribute])) {
				$value=$_POST[get_class($model)][$attribute];
			}
			else if(!empty($_SESSION['_controller_data'][get_class($model)][$attribute])) {
				$value = $_SESSION['_controller_data'][get_class($model)][$attribute];
			}
			$field=$model->getField($attribute);
			$name=get_class($model).'['.$attribute.']';
			$id =strtolower(get_class($model).'_'.$attribute);
			if($model->loaded){
				$value=$model->$attribute;
			}
		}
		if($field->not_null==1)
			$not_null = "*";
	}

	if(!empty($params['label']))
		$label=$params['label'];
	else {
		$label=$field->tag;
	}
	if(!empty($params['value']))
		$value=$params['value'];

	//check whether required field
	$html='';
	if(!isset($params['notags'])) {
		$html.='<dt>';
	}
	$html.='<label for="'.$id.'">'.$label.$not_null.':</label>';
	if(!isset($params['notags'])) {
		$html.='</dt><dd class="for_textarea'.((isset($params['editor'])&&($params['editor'] == 'tinymce'))?' for_tinymce':'').'">';
	}
	$html.='<textarea cols="30" rows="5" name="'.$name.'" id="'.$id.'"'.((isset($params['editor'])&&($params['editor'] == 'tinymce'))?' class="tinymce"':'').'>'.trim($value).'</textarea>';
	if(!isset($params['notags'])) {
		$html.='</dd>';
	}
	return $html;
}
?>
