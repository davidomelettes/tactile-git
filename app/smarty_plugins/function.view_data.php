<?php

function smarty_function_view_data($params,&$smarty) {

	$extra_class = '';
	$template_html = '<span class="view_label"><span class="label">%s</span></span><span class="view_data"><span class="data">%s</span></span>';
	$with = $smarty->get_template_vars('with');
	
	if (!empty($params['model'])) {
		$model = &$params['model'];
	} else {
		$model = $with['model'];
	}
		
	$attribute = $params['attribute'];
	if (isset($params['value'])) {
		$value = $params['value'];
	} else {
		if ($model->isField($attribute)) {
			$value = $model->getFormatted($attribute);
		}
		if (empty($value)) {
			$value = $model->$attribute;
		}
		if (substr($attribute,-2) == '()') {
			$attribute=substr($attribute, 0, -2);
			$field->is_safe = true;
			$value = call_user_func(array($model,$attribute));
		}
		if (method_exists($value,'__toString')) {
			$value = $value->__toString();
		}		
	}	
	
	if (isset($params['label'])) {
		$tag = prettify($params['label']);
	} else {
		if ($model->isField($attribute)) {
			$field=$model->getField($attribute);
			$tag = $field->tag;
		}	
		if (empty($tag)) {
			$tag = prettify($attribute);
		}
	}
	
	switch ($attribute) {
		case 'organisation': {
			$value = $model->organisation;
			$key = $model->organisation_id;
			$link = 'organisations';
			break;	
		}
	case 'parent': {
			$value = $model->parent;
			$key = $model->parent_id;
			$link = 'organisations';
			break;	
		}
		case 'person': {
			$value = $model->person;
			$key = $model->person_id;
			$link = 'people';
			break;
		}
		case 'opportunity': {
			$value = $model->opportunity;
			$key = $model->opportunity_id;
			$link = 'opportunities';
			break;			
		}
		case 'company_status': {
			$value = $model->$attribute;
			$search = 'by_status';
			$search_id = $model->status_id;
			$link = 'organisations';
			break;			
		}
		case 'company_source': {
			$value = $model->$attribute;
			$search = 'by_source';
			$search_id = $model->source_id;
			$link = 'organisations';
			break;			
		}
		case 'company_classification': {
			$value = $model->$attribute;
			$search = 'by_classification';
			$search_id = $model->classification_id;
			$link = 'organisations';
			break;			
		}
		case 'company_rating': {
			$value = $model->$attribute;
			$search = 'by_rating';
			$search_id = $model->rating_id;
			$link = 'organisations';
			break;			
		}
		case 'company_industry': {
			$value = $model->$attribute;
			$search = 'by_industry';
			$search_id = $model->industry_id;
			$link = 'organisations';
			break;			
		}
		case 'company_type': {
			$value = $model->$attribute;
			$search = 'by_type';
			$search_id = $model->type_id;
			$link = 'organisations';
			break;			
		}
		case 'status': {
			$value = $model->$attribute;
			$filter = 'by_status';
			$link = 'opportunities';
			break;			
		}
		case 'source': {
			$value = $model->$attribute;
			$filter = 'by_source';
			$link = 'opportunities';
			break;			
		}
		case 'type': {
			$value = $model->$attribute;
			$filter = 'by_type';
			$link = $model->get_name() == 'Opportunity' ? 'opportunities' : 'activities';
			break;			
		}
		
	}
		
	if (trim($value) == '') {
		$value = '<span class="blank">-</span>';
		$extra_class = ' blank';
		
	} else if (!empty($search)) {
		$value = "<a href='/$link/$search/?q=".urlencode($search_id)."&t=".urlencode($value)."'>".h($value).' &raquo;</a>';
		
	} else if (!empty($filter)) {
		$value = "<a href='/$link/$filter/?q=".urlencode($value)."'>".h($value).' &raquo;</a>';
		
	} else if (!empty($key)) {
		$value = "<a href='/$link/view/$key'>".h($value).' &raquo;</a>';
		 
	} else if ($attribute == 'email') {
		$link = '<a class="mailto" href="mailto:'.$value.'">%s</a>';
		$value = sprintf($link, $value);
		
	} else if ($attribute == 'probability') {
		$value=h($value).'%';
		
	} else {
		if (($field !== null && !$field->is_safe)) {
			$value = h($value);
		}
	}
	
	return sprintf($template_html, $tag, $value);
}
