<?php
function smarty_block_heading_cell($params,$content,&$smarty,$repeat) {
	if(!empty($content)) {
		$html='<th%s>%s%s%s</th>'."\n";
		$before='';
		$after='';
		$class_string='';
		if($smarty->get_template_vars('no_ordering')!==true) {
			$before='<a href="%s&amp;orderby=%s">';
			$module=$smarty->get_template_vars('module');
			$controller=$smarty->get_template_vars('controller');
			$action=$smarty->get_template_vars('action');
			if(empty($action))
				$action='index';
			if($params['field']==$smarty->get_template_vars('wide_column'))
				$class_string=' class="wide_column"';
			if(empty($class_string)&&$params['field']=='description')
				$class_string=' class="wide_column"';
			
			$self='/?module='.$module.'&amp;controller='.$controller.'&amp;action='.$action;
			$id = $smarty->get_template_vars('id');
			if(isset($id)) 
				$self .='&amp;id='.$id['value'];
			$before=sprintf($before,$self,$params['field']);
			$after='</a>';
		}
//		global $injector;
//		$translator=$injector->instantiate('Translation');
		//		$content=$translator->translate($params['field']);
		$content=prettify($content);
		if (substr($params['field'],-2) == 'id')
			return '';
		return sprintf($html,$class_string,$before,$content,$after);

	}
}
?>
