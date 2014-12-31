<?php

function smarty_block_heading_row($params,$content,&$smarty,$repeat) {

	if(!empty($content)) {
		$html='<thead><tr>%s</tr></thead>'."\n";
		return sprintf($html,$content);	
	}
}
?>
