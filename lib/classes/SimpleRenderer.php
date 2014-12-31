<?php
class SimpleRenderer implements Renderer {

	public function render(EGlet &$eglet,&$smarty) {
		if($eglet->should_render) {
			$smarty->assign('content',$eglet->getContents());
			//the str_replace is for backwards compatibility
			$smarty->cache_lifetime=200;
			$smarty->display('eglets/'.str_replace('eglets/','',$eglet->getTemplate()),$eglet->getCacheID());
		}
	}
}
?>