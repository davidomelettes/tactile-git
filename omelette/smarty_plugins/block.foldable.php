<?php
function smarty_block_foldable($params,$content,&$smarty,$repeat) {
 	 if(!empty($content)) {
		$key = $params['key'];
		$title = $params['title'];
		$extra_class ='';
		if(!empty($params['extra_class'])) {
			$extra_class = ' '.$params['extra_class'];
		}
		$add_html = '';
		if (!empty($params['add_url']) || !empty($params['add_url_text'])) {
			$add_url_text = !empty($params['add_url_text']) ? $params['add_url_text'] : 'Add';
			if (!empty($params['add_url'])) {
				$add_html = sprintf('<a href="%s" class="add_related">%s</a>', $params['add_url'], $add_url_text);
			} else {
				$add_html = sprintf('<a class="add_related">%s</a>', $add_url_text);
			}
		}
		$closed = Omelette_Magic::getAsBoolean(
			$key,
			CurrentlyLoggedInUser::Instance()->getRawUsername(),
			'closed',
			'open'
		);
		
		$heading_class = ($closed)?' class="closed"':'';
		
		$div_style = (!empty($title) && $closed)?' style="display:none;"':'';
		
		if (empty($title)) {
			$prefix = '<div class="foldable"><div%s>';
			$prefix = sprintf($prefix, $div_style);
		} else { 
			$prefix =	'<div class="foldable'.$extra_class.'" id="%s">'.
					'<h3%s><a>%s</a>%s</h3>'.
					'<div%s>';
			$prefix = sprintf($prefix, $key, $heading_class, $title, $add_html, $div_style);
		}
		
		$suffix='</div></div>';
		return $prefix.$content.$suffix;
	}
}
?>