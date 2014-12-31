<?php
function smarty_function_js($params, &$smarty) {
	$with = $smarty->get_template_vars('with');
	$dir = '/app/js/';
	if(isset($params['dir'])) {
		$dir.=$params['dir'].'/';
	}
	if(isset($with['dir'])) {
		$dir.=$with['dir'].'/';
	}
	$file = $params['file'].'.js';
	
	if(isset($params['load'])) {
		$file.='?load='.$params['load'];
	}
	
	$html = '<script type="text/javascript" src="'.$dir.$file.'"></script>';
	
	return $html;
}
?>