<?php
function smarty_function_lazy_foldable($params,&$smarty) {
	$html = '<div id="%s" class="ajax-foldable">
		<h3 class="%s">
			<a href="%s">%s</a>
			%s
		</h3>
	</div>';
	
	extract($params);
	if(!empty($add_url)) {
		$add_html .= sprintf('<a href="%s" class="add_related">Add</a>', $add_url);
	}
	else {
		$add_html = '';
	}
	
	$classname = Omelette_Magic::getValue(
		$key,
		CurrentlyLoggedInUser::Instance()->getRawUsername(),
		'closed'
	);
	$html = sprintf($html,$key,$classname,$view_url,$title,$add_html);
	return $html;
}
?>