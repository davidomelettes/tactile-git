<?php

function smarty_block_data_table($params,$content,&$smarty,$repeat) {
	if(!empty($content)) {
		$html='<table cellspacing="0" cellpadding="0" class="datagrid" id="datagrid1">%s</table>'."\n";
		return sprintf($html,$content);
	}

}



?>
