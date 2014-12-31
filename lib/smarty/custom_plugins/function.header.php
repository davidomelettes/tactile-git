<?php

function smarty_function_header($params,&$smarty) {
	$self = $smarty->get_template_vars('self');
	$theme = $smarty->get_template_vars('theme');
		
	$title=$smarty->get_template_vars('page_title');
	
	$inflector = new Inflector();
	$item_name= prettify($inflector->singularize($smarty->get_template_vars('controller')));
	if(empty($title)||$title=='Index') {
	    switch($smarty->get_template_vars('action')) {
		case 'view': {
			$title=$item_name.' Details';
			break;
		}
		case 'edit': {
			$title='Editing '.$item_name.' Details';
			break;
		}
		case 'new': {
			$title='Create new '.$item_name;
			break;
		}
		case 'index':		//fall through
		default: {
			$title=$item_name;
			$subtitle='Index';
			break;
		}
	    }
	}
	$html='<h1 class="page_title">'.$title.'</h1>';
	return $html;
}


?>
