<?php

function smarty_function_view_data($params,&$smarty) {

	$template_html='<dt>%s</dt><dd>%s</dd>';
	$with=&$smarty->get_template_vars('with');
	if(!empty($params['model'])) {
		$model=&$params['model'];
	}
	else {
		$model=$with['model'];
	}
	if(!empty($params['modifier'])) {
		$modifier=$params['modifier'];
	}
	else {
		if(isset($with['modifier']))
			$modifier=$with['modifier'];
	}
	
	$attribute=$params['attribute'];
	if(isset($params['value'])) {
		$value=$params['value'];
	}
	else {
		//$value=$model->$attribute->formatted;
		if($model->isField($attribute)) {
			$field=$model->getField($attribute);
			$value=$field->formatted;
		}
		if (empty($value)) {
			$value = $model->$attribute;
		}
		if(substr($attribute,-2)=='()') {
			$attribute=substr($attribute,0,-2);
			$field->is_safe=true;
			$value=call_user_func(array($model,$attribute));
		}
		if(method_exists($value,'__toString')) {
			$value=$value->__toString();
		}		
	}
	if($attribute=='rag_status()') {
		var_dump($value);
	}
	if($model->isEnum($attribute)) {
		$values = $model->getEnumOptions($attribute);
		$value = $values[$value];
	}
	if($model->isField($attribute)) {
		$field=$model->getField($attribute);
		$tag=$field->tag;
	}
	if(empty($tag)) {
		$tag=prettify($attribute);
	}
	if (isset($params['label']))
		$tag = prettify($params['label']);
	if (isset($params['type']) && $params['type']=="percentage")
		$value .= "&#37;";
	
	$temp_lookups=array('employee'=>'hr','company'=>'contacts','person'=>'contacts','project'=>'projects','originator_person'=>'contacts','originator_company'=>'contacts','opportunity'=>'crm');
	if(str_replace(' ','',$value) == '') {
		$value='<span class="blank">-</span>';
	}
	else if(isset($params['link_to'])) {
		$link = $params['link_to'];
		$id_candidate = $attribute.'_id';
		if($model->isField($id_candidate)) {
			$id=$model->$id_candidate;
			$link = str_replace('__ID__',$id,$link);
		}
		/**
		 * Within the template this function can be called with the following parameters to make a link:
		 * 
		 * {view_data attribute="name attribute" link_to='"module":"X","controller":"Y","action":"Z","id":__ID__' link_id="id attribute"}
		 * to create a link to the ID, with the modules actions and such, with full html.
		 */
		elseif(isset($params['link_id']) && $model->isField($params['link_id'])) {
			$id=$model->{$params['link_id']};
			$link = str_replace('__ID__',$id,$link);
		}
		$link = json_decode('{'.$link.'}',true);
		$link['value']=h($value).' &raquo;';
		$value = link_to($link);
	}
	/* This auto links to people or accounts or projects */
	else if(isset($temp_lookups[$attribute]) || isset($temp_lookups[$params['fk']])) {
		if (isset($params['fk_field']))
			$fk_field = $params['fk_field'];
		else
			$fk_field = $attribute.'_id';
		if (isset($params['fk'])) {
			$att = $params['fk'];
			$module = $temp_lookups[$params['fk']];
		}
		else {
			$att = explode('_',$attribute);
			$att = $att[count($att)-1];
			$module = $temp_lookups[$attribute];
		}
		$value = link_to(array('module'=>$module,'controller'=>$att.'s','action'=>'view','id'=>$model->{$fk_field},'value'=>h($value).' &raquo;'));
	}	
	/* This auto links to emails */
	else if($attribute=='email') {
		$link='<a class="mailto" href="mailto:'.$value.'">%s</a>';
		
		$value=sprintf($link,$value);
	}
	// This auto links to google maps for postcodes
	else if($attribute=='postcode') {
		$link = '<a class="maps_link" href="http://maps.google.co.uk/maps?f=q&hl=en&q=%s">%s</a>';
		$value = sprintf($link,$value,$value);
	}
	else if(isset($modifier)) {
		$value=call_user_func($modifier,$value);
	}
	else {
		if(isset($with['formatter'])&&class_exists($with['formatter'].'Formatter')) {
			$classname = $with['formatter'].'Formatter';
			$formatter = new $classname;
			$value=$formatter->format($value);
			if(!$formatter->is_safe) {
				$value=h($value);
			}
		}
		if(($field!==null&&!$field->is_safe)) {
			$value=h($value);
		}
	}
	if(prettify($attribute)=='EGS_HIDDEN_FIELD') {
		return '';
	}
	return sprintf($template_html,$tag,$value);

}
?>
