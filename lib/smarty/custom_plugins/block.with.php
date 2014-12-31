<?php
function smarty_block_with($params,$content,&$smarty,&$repeat) {
	if(empty($content)) {
		$with=$smarty->get_template_vars('with');
		foreach($params as $key=>$val) {
			$with[$key]=$val;
			$smarty->assign($key,$val);
		}
		$smarty->assign('with',$with);
	}
	else {
		$return ='';
		$with = $smarty->get_template_vars('with');
		foreach($params as $key=>$val) {
			$smarty->clear_assign($key);
			unset($with[$key]);
		}
		$smarty->assign('with',$with);	
		
		
		return $return.$content;
	}
}
?>
