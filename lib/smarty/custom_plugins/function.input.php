<?php

function smarty_function_input($params,&$smarty) {
		$value='';
		$maxlength = '';
		$additional = '';
		$disable = false;
		$controller_data = &$smarty->get_template_vars('controller_data');
		$attribute=$params['attribute'];
		$with=&$smarty->get_template_vars('with');
		if(isset($params['tags'])&& $params['tags'] == 'none'||isset($with['tags'])&&$with['tags']=='none') {
			$notags = true;
		}
		else {
			$notags = false;
		}

		

		if (empty($params['alias'])) {
			$params['alias'] = isset($with['alias'])?$with['alias']:'';
		}
			
		if(!empty($params['model'])) {
			$model=&$params['model'];
		}
		else {
			$model=$with['model'];
		}
		if (!empty($params['hidden'])) {
			$params['type'] = 'hidden';
		}
		if(empty($params['alias'])) {
			if(isset($controller_data[$attribute])) {
				$value=$controller_data[$attribute];
				$params['type'] = 'hidden';
			}
		}
		if (!empty($params['label']))
			$label=prettify($params['label']);
		if (!empty($params['value']))
			$value =$params['value'];
		if(isset($model)){
			if(!empty($params['alias'])) {
				$alias = $model->getAlias($params['alias']); 
				$aliasModelName = $alias['modelName'];
				$newmodel = new $aliasModelName; 
				$field = $newmodel->getField($attribute);
				if(empty($params['label']))
					$label=$field->tag;
				$name = $model->get_name().'['.$params['alias'].']['.$attribute.']';
				$id = $model->get_name().'_'.$params['alias'].'_'.$attribute;
				if (!empty($_POST[$model->get_name()][$params['alias']][$attribute])) {
					$value = $_POST[$model->get_name()][$params['alias']][$attribute];
				}
				else if(!empty($_SESSION['_controller_data'][$model->get_name()][$params['alias']][$attribute])) {
					$value=$_SESSION['_controller_data'][$model->get_name()][$params['alias']][$attribute];
				}
				else if($model->isLoaded()) {
					$newmodel = $model->$params['alias'];
					$value = $newmodel->$attribute;
				}
				if(!isset($value)&&isset($params['value'])) {
					$value=$params['value'];
				}
			}
			else {
				$field=$model->getField($attribute);
				if($field!==false) {
					//Set "not editable" fields to DISABLED, but only if not already hidden
					if($model->isNotEditable($field->name) && $model->isDisplayedField($field->name)) {
						$additional.=' disabled="disabled"';
					}

					if(empty($params['label'])) {
						$label=$field->tag;
					}

					$name=$model->get_name().'['.$attribute.']';
					if(isset($params['postfix'])) {
						$name.=$params['postfix'];
					}
					$id =strtolower($model->get_name().'_'.(isset($params['number'])?($params['number'].'_'):'').$attribute);
					if (isset($params['number'])) {
						$name = $model->get_name().'['.$params['number'].']['.$attribute.']';
					}
					else {
						$name=$model->get_name().'['.$attribute.']';
					}
					if(isset($params['postfix'])) {
						$name.=$params['postfix'];
					}
					$id =strtolower($model->get_name().'_'.(isset($params['number'])?($params['number'].'_'):'').$attribute);
					if(!empty($_POST[$model->get_name()][$attribute])) {
						$value=$_POST[$model->get_name()][$attribute];
					}
					else if(!empty($_SESSION['_controller_data'][$model->get_name()][$attribute])) {
						$value=$_SESSION['_controller_data'][$model->get_name()][$attribute];
					}
					else if($model->isLoaded()){
							$value=$model->$attribute;
					}
					if(empty($value) && $field->has_default==1 && $field->name!=$model->idField && !$model->isLoaded()){
						
						$value=$field->default_value;
						$pattern="#'(.*)'::(?:.*)#";
						if(preg_match($pattern,$value,$matches)) {
							$value=$matches[1];
						}
						
					}
				}
				else {
					$name=$model->get_name().'['.$attribute.']';
				}
			}
		}
		else {
			$name=$attribute;
			$id=$attribute;
		}

		//check whether required field
		$additional = $smarty->get_template_vars('additional_compulsory');
		if(($field->not_null==1 && $params['type']!=='checkbox' && empty($params['alias']))||(is_array($additional)&&in_array($attribute,$additional))) {
			$not_null = " *";
			$additional.=' class="required" ';
		}
		if(strpos($attribute,'confirmation_')===0) {
			if(empty($label)) {
				$label = prettify($attribute);
			}
			$additional=' class="confirmation"';
			
		}

		if(isset($params['readonly']) && $params['readonly']!=false){
			$additional.='readonly="readonly"';
		}
		$label='<label for="'.$id.'">'.$label.$not_null.'</label>';
		$field_template='<input type="%s" name="%s" id="%s" value="%s" %s %s/>';
		if(!empty($params['class'])&&$params['class']!=='compulsory')
			$additional=' class="'.$params['class'].'"';
		if(prettify($attribute)=='EGS_HIDDEN_FIELD') {
			return '';
		}

		#Looks for type
		switch($params['type']) {
			case 'hidden' :
				//print_r($model);
				//if(!empty($value)||isset($params['cascades'])||isset($params['cascadesfrom'])) {
					$field=sprintf($field_template,'hidden',$name,$id,$value,'','');
					if (isset($params['cascades'])) {
						$js = '<script type="text/javascript">cascade(\''.$id.'\',\''.$model->get_name().'_'.$params['cascades'].'\')</script>';
						$smarty->assign('append',$js);
					}
					if (isset($params['cascadesfrom'])) {
						$js = '<script type="text/javascript">filterup(\''.$id.'\',\''.$params['cascadesfrom'].'\',\''.$model->get_name().'_'.$params['cascadesfrom'].'\')</script>';
						$smarty->assign('append',$js);
					}
					return $field;
				//}
			//	else return "";

			case 'checkbox' :
				if($value === 't' || $value=='true') {
					$additional .= ' checked="checked" ';
				}
				if (isset($params['disabled']) && $params['disabled']) {
					$additional .= ' disabled="disabled" ';
				}
				$field=sprintf($field_template,'checkbox',$name,$id,'on',$additional,'class="checkbox"');
				$field.='<input type="hidden" name="'.str_replace('[','[_checkbox_exists_',$name).'" value="true" />';
				break;
			case 'date' :
					if(is_numeric($value)) {
						$value = date(EGS::getDateFormat(),$value);
					} else {
						$modelvalue = $model->$attribute;
						if(!empty($modelvalue)){
							$value = date(EGS::getDateFormat(),strtotime($modelvalue));
						}
					}
					
					if($params['onChange']=='count_days'){								
						$field=sprintf($field_template,'text',$name,$id,$value,'autocomplete="off" class="datefield"','onchange="javascript:countDays(\'holidayrequest_start_date\',\'holidayrequest_end_date\',\'holidayrequest_num_days\')"');
					}
					else
						$field=sprintf($field_template,'text',$name,$id,$value,'autocomplete="off" class="datefield"','');
					break;
			case 'datetime' :
					switch($field->type) {
						case 'timestamp':
							if(is_numeric($value)) {
								$value=date(EGS::getDateTimeFormat(),$value);
							}
							break;
						default:
							if(is_numeric($value)) {
								$value = date(EGS::getDateFormat(),$value);
							}
					}
				$field=sprintf($field_template,'text',$name,$id,$value,'class="datetimefield"','');
				break;
			case 'file' :
			    $field=sprintf($field_template,'file',$name,$id,$value,'class="file"','');
			    break;
			default :
				$field=sprintf($field_template,$params['type'],$name,$id,h(trim($value), ENT_QUOTES),$maxlength,$additional);
				break;
		}
		if($notags)
		{
			return $label.$field;
		}
		//then put together the bits
		$label_container='<dt>'.$label.':</dt>';
		$field_container='<dd>'.$field.'</dd>';
		$row_container=$label_container.$field_container;
		return '<div class="row">'.$label.$field.'</div>';//$row_container;

}

