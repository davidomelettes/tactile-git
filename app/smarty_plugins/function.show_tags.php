<?php
/**
 *
 * @author gj
 */
function smarty_function_show_tags($params, $smarty) {
	$type = $params['type'];
	$model = $params['model'];
	$user = CurrentlyLoggedInUser::Instance();
	$edit_locked = !$user->canEdit($model);
	$item = new TaggedItem($model);
	$tags = $item->getTags();
	
	switch ($type) {
		case 'organisations':
			$name = 'Organisation';
			break;
		case 'people':
			$name = 'Person';
			break;
		case 'opportunities':
			$name = 'Opportunity';
			break;
		case 'activites':
			$name = 'Activity';
			break;
	}
	
	$html = '<div class="tag_list" id="' . $type . '_tags">';
	$tag_html = '<li class="tag"><a href="/%s/by_tag/?tag=%s" title="See all ' . ucfirst($type) . ' tagged \'%s\'">%s</a> </li>';
	
	$html .= '<ul>';
	if (!$edit_locked) {
		$html .= '<li class="edit'.(count($tags)>0 ? '' : ' no_tags').'"><a class="action">Remove Tags</a> </li>';
	}
	foreach ($tags as $tag) {
		$html .= sprintf($tag_html, $type, h(urlencode($tag)), h($tag), h($tag));
	}
	if (!$edit_locked) {
		$html .= '<li class="add"><a class="action">Add Tags</a> </li>';
	}
	$html .= '</ul>';
	
	$html .= '<div class="c-left"></div></div>';
	return $html;
}
