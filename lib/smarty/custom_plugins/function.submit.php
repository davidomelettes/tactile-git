<?php
function smarty_function_submit($params,&$smarty) {
$action=&$smarty->get_template_vars('action');
$self=&$smarty->get_template_vars('self');
	$value=(empty($params['value']))?'Save':$params['value'];
	$name=(empty($params['name']))?'saveform':$params['name'];
	$id=(empty($params['id']))?'saveform':$params['id'];
	$html = '<input class="formsubmit" type="submit" value="'.$value.'" name="'.$name.'" id="'.$id.'"/>';
	if(isset($params['tags']) && $params['tags'] == 'none')
		return $html;
	$html='<dt class="submit">&nbsp;</dt><dd class="submit">'.$html;
	$vars = $smarty->get_template_vars();
	if ((!isset($params['another']) || $params['another'] == 'true') && ((substr($action,0,4)!='edit') && ($self['controller']!='Preferences')))
		$html .= '&nbsp;<input class="formsubmit" type="submit" value="Save and add another" name="saveAnother" />';
	if (isset($vars['append']))
		$html .= $vars['append'];
	$html .= '</dd>';
	return $html;

}
?>
