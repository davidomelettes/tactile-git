<?php

function smarty_function_select($params,&$smarty) {
		$attribute=$params['attribute'];
		$controller_data = &$smarty->get_template_vars('controller_data');
		//print_r($controller_data);
		if(isset($controller_data[$attribute]) || !empty($params['hidden'])) {
			$html = smarty_function_input($params,$smarty);
			return $html;
		}
		$with=&$smarty->get_template_vars('with');
		
		if(!empty($params['use_collection'])) {
			$use_collection = true;
		}
		
		if(!empty($params['model'])) {
			$model=&$params['model'];
		}
		else {
			$model=$with['model'];
		}
		$cc = new ConstraintChain();
		if (!empty($params['constraint'])) {
			$constraint=$params['constraint'];
			if(class_exists($constraint.'Constraint')) {
				$cname=$constraint.'Constraint';
				$constraint = new $cname($attribute);
				if(!($constraint instanceof Constraint)) {
					throw new Exception($cname.' is not a valid Constraint');
				}
			}
			else {
				$exp = explode(',',$constraint);
				$constraint=new Constraint($exp[0],$exp[1],$exp[2]);
			}
			$cc->add($constraint);
		}
		
		if (empty($params['alias'])) {
			$params['alias'] = isset($with['alias'])?$with['alias']:'';
		}
		if(!empty($params['alias'])) {
			$alias = $model->getAlias($params['alias']); 
			$aliasModelName = $alias['modelName']; 
			$newmodel = new $aliasModelName; 
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
			$model = $newmodel;
		}
		
		//$get_options=$model->getOptions($attribute);
		if(isset($params['options'])) {
			$get_options = $params['options'];
		}
		$field=$model->getField($attribute);

		if(!empty($params['label'])){
			$label=$params['label'];
		}
		else {
			$label=$field->tag;
		}
		if(isset($params['tags'])&& $params['tags'] == 'none'||isset($with['tags'])&&$with['tags']=='none')
		{
			$notags = true;
		}
		else $notags = false;


		if (isset($params['number'])) {
			$name = $model->get_name().'['.$params['number'].']['.$attribute.']';
		}
		else if(!isset($name)) {
			$name=$model->get_name().'['.$attribute.']';
		}
		$id=$model->get_name().'_'.(isset($params['number'])?($params['number'].'_'):'').$attribute;

		if(!isset($name)) {
			$name=$model->get_name().'['.$attribute.']';
			$id=strtolower($model->get_name().'_'.(isset($params['number'])?($params['number'].'_'):'').$attribute);
		}
		if(isset($params['postfix'])) {
			$name.=$params['postfix'];
		}
		
		if (isset($params['value'])) {
			$selected = $params['value'];
		}
		else if(!empty($value)) {
			$selected=$value;
		}
		else {
			$selected='';
		}
		if($model->isLoaded()) {
			$selected=$model->$attribute;
		}
		$fallback=true;
		$use_autocomplete=false;
		if(isset($model->belongsToField[$attribute])) {
			if(isset($params['data'])) {
				if($params['data'] instanceof DataObjectCollection) {
					$options = $params['data']->getAssoc();
				}
				else if(is_array($params['data'])) {
					$options=$params['data'];
				}
				else {
					throw new Exception('"data" paramater should be an associative array, or a DataObjectCollection');
				}
			}
			else {
				define('EGS_SELECT_LIMIT',10);
				$x = $model->belongsTo[$model->belongsToField[$attribute]]["model"];
				if( (($x=='Company') || ($x=='Person'))&&$model->getOptionsCount($attribute)>EGS_SELECT_LIMIT&&(!isset($params['forceselect']))) {
					$use_autocomplete=true;
					$text_value=$model->{$model->belongsToField[$attribute]};
					if (trim($text_value) == '') {
						$temp = new $x;
						$temp->load($selected);
						if ($x == 'Company')
							$text_value = $temp->name;
						else
							$text_value = $temp->firstname . ' ' . $temp->surname;
					}
					$auto_complete_type=strtolower($x).'s';
				}
				else {
					
					$x = DataObject::Construct($x);
					if($model->checkUniqueness($attribute)) {
						$cc->add(new Constraint($x->idField,'NOT IN','(SELECT '.$attribute.' FROM '.$model->getTableName().')'));
						$options=$x->getAll($cc,true,$use_collection);
						$fallback=false;
					}
					else {
						$options = $x->getAll($cc,false,$use_collection);
					}
				}
			}
		}
		else if($model->hasParentRelationship($attribute)&&!isset($params['ignore_parent_rel'])) {
			$db=DB::Instance();
			$x= clone $model;
			if($model->isLoaded())
				$cc->add(new Constraint($model->idField,'<>',$model->{$model->idField}));
			$options = $x->getAll($cc,false,$use_collection);

		}
		//enumeration
		elseif($model->isEnum($attribute)) {
			$options=$model->getEnumOptions($attribute);
		}
		if(!$notags)
		{
			$html = '<div class="row">';
		}
		$html .= '<label for="'.$id.'">'.$label.'</label>';


		if(!empty($_POST[$model->get_name()][$attribute])) {
			$selected=$_POST[$model->get_name()][$attribute];
		}
		else if(!empty($_SESSION['_controller_data'][get_class($model)][$attribute])) {
			$selected = $_SESSION['_controller_data'][get_class($model)][$attribute];
		}
		
		if(empty($selected)&&$field->has_default && !$model->isLoaded()) {
			$selected=$field->default_value;
			if(!isset($params['clasee'])) {
				$params['class'] = '';
			}
			$params['class'] = trim($params['class'].=' using_default');
		}
		if(!$use_autocomplete) {
			$html.='<select name="'.$name.'" id="'.$id.'"';
			if (isset($params['class']))
				$html .= ' class="'.$params['class'].'"';
			if(isset($params['onchange']))
				$html.=' onchange="'.$params['onchange'].'"';
			$html .= '>';
			//check whether required field
			if(!$field->not_null==1 && !(isset($params['nonone']) && $params['nonone'] != 'true'))
				$html .= '<option value="">None</option>';
			if(isset($params['start']))
				$html .= '<option value="">'.$params['start'].'</option>';
			//fallback is a horrible hack for now (for uniqueness constraints on dropdowns)
			if($fallback&&is_array($get_options)) {
				$options=$get_options;
			}
			if(!empty($options)){
				foreach($options as $key=>$value){
					$html .='<option value="'.$key.'"';
					if($selected == $key) {
						$html.=' selected="selected" ';
					}
					$html .='>'.h($value).'</option>';


				}
			}
			$html.='</select>';
			if(isset($params['cascades'])) {
				$html.='<script type="text/javascript">new Cascade("'.$id.'","'.$model->get_name().'_'.$params['cascades'].'");</script>';			
			}
		}
		else {
			$html.='<input type="hidden" name="'.$name.'" id="'.$id.'" value="'.$selected.'" />';
			$html.='<input type="hidden" id="'.$id.'_text" value="'.$text_value.'" />';
			$html.='<script type="text/javascript">new Ajax.EGSAutocompleter(\''.$id.'\',\''.$auto_complete_type.'\');</script>';
		}
		if(!$notags)
		{
			$html .= '</div>';
		}
		if(prettify($params['attribute'])=='EGS_HIDDEN_FIELD') {
			return '';
		}
		 return $html;
}
