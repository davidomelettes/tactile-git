<?php
function smarty_function_link_to($params,&$smarty) {

	with($params,$smarty);
	if(isset($params['data'])&&is_array($params['data'])) {
		$params=$params+$params['data'];		
		unset($params['data']);
	}
	$link = link_to($params);
	return $link;
}
?>
