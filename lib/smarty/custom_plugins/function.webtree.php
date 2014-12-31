<?php
	
	function smarty_function_webtree($params,&$smarty) {
		$tree = $params['tree'];
		return getTree($tree,$smarty);
	}
	
	function getTree($tree,$smarty) {
		$excludeTags = array('shopsection');
		$output = '<ul id="webtree_top">';		
		$website = $smarty->get_template_vars('Website');			
		if ($tree instanceof DOMDocument) {
			$output .= '<li class="new">';
			$output	.= '<a href="/?module=websites&controller=webpages&action=new&website_id='.$website->id.'">[New Webpage]</a>&nbsp;-&nbsp;<a href="/?module=websites&controller=webpagecategorys&action=new&website_id='.$website->id.'">[New Category]</a>';
			$output .= '</li>';
		}
		if ($tree->hasChildNodes()) {
			$nodes = $tree->childNodes;
			foreach ($nodes as $node) {
				if (!($node instanceof DOMText) && (!in_array($node->tagName,$excludeTags))) {
					$id = $node->getAttribute('id');
					$output .= "<li class=\"drag $node->tagName\" id=\"treeitem_{$node->tagName}-{$id}\">";
					if ($node->tagName == 'webpagecategory') {
						$wc = new WebpageCategory();
						$wc->load($id);
						$output .= $wc->title . ' - ';
						$output .= '<a href="/?module=websites&controller=webpages&action=new&webpage_category_id='.$id.'&website_id='.$website->id.'">[New Webpage]</a> - ';
						$output .= '<a href="/?module=websites&controller=webpagecategorys&action=edit&id='.$id.'">[Edit]</a>';
					}
					else {
						$webpage = new Webpage();
						$webpage->load($id);
						$output .= $webpage->revision->title . ' - ';
						$output .= '<a href="/?module=websites&controller=webpages&action=new&parent_id='.$id.'&website_id='.$website->id.'">[New]</a>&nbsp;-&nbsp;';
						$output .= "<a href=\"/?module=websites&controller=webpages&action=view&id=$id\">[View]</a>&nbsp;-&nbsp;";
						$output .= "<a href=\"/?module=websites&controller=webpages&action=edit&id=$id\">[Edit]</a>&nbsp;-&nbsp;";
						$output .= "<a href=\"/?module=websites&controller=webpages&action=delete&id=$id\">[Delete]</a>";
						$output .= '<span class="webtree_info"> (Last Updated: '.substr($webpage->revision->created,0,16).')</span>';
					}
					$output .= getTree($node,$smarty);
					$output .= '</li>';
				}
			}
		}
		$output .= '</ul>';
		return $output;
	}
	
?>
