<?php
function smarty_block_grid_row($params,$content,&$smarty,$repeat) {
	static $on_off='on';
	if(!empty($content)) {
		$html='<tr class="gridrow_%s">%s</tr>'."\n";
		$on_off=($on_off=='on')?'off':'on';
		return sprintf($html,$on_off,$content);
		$smarty->clear('gridrow_id');
	}
	else {
		$model=$params['model'];
		$smarty->assign('gridrow_id',$model->{$model->idField});

	}
}
?>
