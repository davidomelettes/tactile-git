<?php
	
	function smarty_function_shoptree($params,&$smarty) {
		$tree = $params['tree'];
		return getTree($tree,$smarty);
	}
	
	function getTree($tree,$smarty) {
		$output = '<ul id="shoptree_top">';		
		if ($tree instanceof DOMDocument) {
			$output .= '<li class="new">';
			$output	.= '<a href="/?module=ecommerce&controller=sections&action=new">[New Section]</a>';
			$output .= '</li>';
		}
		if ($tree->hasChildNodes()) {
			$nodes = $tree->childNodes;
			foreach ($nodes as $node) {
				if (!($node instanceof DOMText)) {
					$id = $node->getAttribute('id');
					$output .= "<li class=\"drag $node->tagName\" id=\"treeitem_{$node->tagName}-{$id}\">";
					$output .= $node->getAttribute('display_title').' - ';
					$output .= '<a href="/?module=ecommerce&controller=sections&action=new&parent_id='.$id.'">[New]</a>&nbsp;-&nbsp;';
					$output .= "<a href=\"/?module=ecommerce&controller=sections&action=view&id=$id\">[View]</a>&nbsp;-&nbsp;";
					$output .= "<a href=\"/?module=ecommerce&controller=sections&action=edit&id=$id\">[Edit]</a>";
					//$output .= "<a href=\"/?module=websites&controller=webpages&action=delete&id=$id\">[Delete]</a>";
					//$output .= '<span class="webtree_info"> (Last Updated: '.substr($webpage->revision->created,0,16).')</span>';
					$output .= getTree($node,$smarty);
					$output .= '</li>';
				}
			}
		}
		$output .= '</ul>';
		return $output;
	}
	
?>
