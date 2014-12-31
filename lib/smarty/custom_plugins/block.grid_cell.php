<?php
function smarty_block_grid_cell($params,$content,&$smarty,$repeat) {
	if(!empty($content)) {
		$html='<td%s>%s</td>'."\n";
		$class_string='';
		$id=$smarty->get_template_vars('gridrow_id');
		$model=$params['model'];
		
		$content=h($content);
		if($params['cell_num']==1) {
			$link = array();
			$self = $smarty->get_template_vars('self');
			$link['modules'] = $self['modules'];

			if($smarty->get_template_vars('clickcontroller')) {
				$clickcontroller = $smarty->get_template_vars('clickcontroller');
			}
			else {
				$clickcontroller = $self['controller'];
			}
			if($params['collection']->clickcontroller) {
				$clickcontroller = $params['collection']->clickcontroller;
			}
			$link['controller']=$clickcontroller;
			if($params['collection']->editclickaction) {
				$link['action'] = $params['collection']->editclickaction;
			}
			else {
				$link['action'] = $smarty->get_template_vars('clickaction');
			}
if($smarty->get_template_vars('linkfield')) {
    $linkfield=$smarty->get_template_vars('linkfield');
} else {
    $linkfield=$model->idField;
}
if($smarty->get_template_vars('linkvaluefield')) {
    $link[$linkfield]=$model->{$smarty->get_template_vars('linkvaluefield')};
} else {
    $link[$linkfield]=$model->$linkfield;
}
			$link['value'] = $content;
			if ($smarty->get_template_vars('clickaction') <> 'none') {
				$content = link_to($link,$data=true);
			}
		}
		$class_string=' class="';
		if($params['field']==$smarty->get_template_vars('wide_column'))
			$class_string.='wide_column';
		if($model->getField($params['field'])->type=='bool') {
			$class_string.=' icon';
			$content='<img src="/themes/default/graphics/'.(($model->{$params['field']}=='t')?'true':'false').'.png" alt="'.(($model->{$params['field']}=='t')?'true':'false').'" />';
		}
		$class_string.='"';
		if ($class_string = ' class=""')
			$class_string = '';
		if($params['field'] == 'email') {
			$content = '<a href="mailto:'.$model->{$params['field']}.'">'.$content.'</a>';
		}
		if($params['field'] == 'website') {
			$content = '<a href="http://'.str_replace('http://', '', $model->{$params['field']}).'">'.$content.'</a>';
		}
		if($params['field'] == 'company' || $params['field'] == 'person') {
			$content = sprintf('<a href="/?module=contacts&controller=%s&action=view&id=%s">%s</a>',$params['field'].'s',$model->{$params['field'].'_id'},$content);
		}
		if (substr($params['field'],-2) == 'id')
			return '';
		return sprintf($html,$class_string,$content);
	}
}
?>
