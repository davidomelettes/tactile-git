<?php
class FlashGraphRenderer implements Renderer {

	public function render(EGlet &$eglet, &$smarty) {
		if($eglet->should_render) {
			$smarty->assign('source',$eglet->getSource());
			$smarty->display('eglets/xml_swf_chart.tpl');
		}
	}
}
?>