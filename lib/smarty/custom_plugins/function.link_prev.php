<?php
function smarty_function_link_prev($params,&$smarty) {

	require_once $smarty->_get_plugin_filepath('function','link_to');
	$self=$smarty->get_template_vars('self');
	$page_num=$params['page']-1;
	$additional=array('page'=>$page_num,'value'=>'<');
	$array=$self+$additional;
	if(is_array($smarty->get_template_vars('paging_link'))) {
		$array=array('data'=>$smarty->get_template_vars('paging_link'))+$additional;
	}
	return smarty_function_link_to($array,$smarty);

}
?>
