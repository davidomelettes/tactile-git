<?php

function smarty_block_view_section($params,$content,&$smarty,$repeat) {
 	 if(!empty($content)) {
		 //<dt class="heading">Organisation Details</dt>
		 $html = '<dt class="heading">%s</dt>%s';
		 $heading=prettify($params['heading']);
		 if($heading=='EGS_HIDDEN_SECTION') {
			return '';
		 }
		return sprintf($html,$heading,$content);		 
	 }
}
 ?>