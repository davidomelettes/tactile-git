<?php
function smarty_function_link_last($params,&$smarty) {

    require_once $smarty->_get_plugin_filepath('function','link_to');
	$self=$smarty->get_template_vars('self');
	$num_pages=$smarty->get_template_vars('num_pages');
	$additional=array('page'=>$num_pages,'value'=>'>>');
	$array=$self+$additional;
	if(is_array($smarty->get_template_vars('paging_link'))) {
		$array=array('data'=>$smarty->get_template_vars('paging_link'))+$additional;
	}
	return smarty_function_link_to($array,$smarty);

}
?>
