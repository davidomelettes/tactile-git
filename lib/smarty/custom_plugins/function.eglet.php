<?php

function smarty_function_eglet($params,&$smarty) {
	
	$container=new EGletContainer();
	
	$eglet_name=$params['name'];
	$renderer=call_user_func(array($eglet_name,'getRenderer'));
	$eglet = new $eglet_name($renderer);
	if(isset($params['title']))
		$title=$params['title'];
	else
		$title=prettify($params['name']);
	$container->addEGlet($title,$eglet);
	
	if(isset($params['populate'])) {
		$container->populate();
		$container->render($params,$smarty);
	}
	
	
}


?>