<?php
/**
 * Form modifiers
 */
 
 function smarty_block_form($params,$content,&$smarty,$repeat) {
 	 if(!empty($content)) {
		$method="post";
		$modules = $smarty->get_template_vars('modules');
		if(!empty($modules))
		{
			$module='';
			$prefix='module=';
			foreach($modules as $mod)
			{
				$module.=$prefix.$mod.'&amp;';
				$prefix='sub'.$prefix;
			}
		}
		if(isset($params['target']))
			$action=$params['target'];
		else
			$action='/?'.$module.'controller='.$params['controller'].'&amp;action='.$params['action'];
		if(isset($params['subfunction'])){
			$action= $action.'&amp;subfunction='.$params['subfunction'];
			if(isset($params['subfunctionaction'])){
				$action= $action.'&amp;subfunctionaction='.$params['subfunctionaction'];
			}
		}
		if(isset($params['id'])) {
			$action .= '&amp;id=' . $params['id'];
		}
		foreach($params as $name=>$value) {
			if($name[0]=='_') {
				$action.='&amp;'.substr($name,1).'='.$value;
			}
		}
		$return= '<form enctype="multipart/form-data" id="save_form" action="'.$action.'" method="'.$method.'">';
		if(!isset($params['notags'])) {
			$return.='<div id="view_page" class="clearfix">'.$content.'</div></form>';
		}
		else {
			$return.=$content.'</form>';
		}
		echo $return;
	 }
 }
 ?>
