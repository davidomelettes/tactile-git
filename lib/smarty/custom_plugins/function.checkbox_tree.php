<?php
	
	function smarty_function_checkbox_tree($params,&$smarty) {

		$html='';	
			
		# Set the variables we need 
		$checked = $params['checked'];
//		print_r($checked);
		$items = $params['items'];
		$admins = $params['admins'];
		$html.='<ul id="permission_tree" class="permissions">';
		if(!isset($items) || empty($items))
		{
			return false;
		}
		foreach($items as $item)
		{
			$html.=mktree($item, $checked, $admins);
		}
		$html.='</ul>';	
		return $html;
	}


	function mktree($items, $checked=array(), $admins, $setall = false, $adchild = false)
	{

		$html ='';
		$mod = '';
		if(isset($checked[$items['id']]) || $setall)
		{
			$mod=' CHECKED ';
			$setall=true;
			
		}
		/****
		 * If the item has children, we need to create the checkboxes for them by calling the mktree function on each of them
         * If not, just return a single checkbox.
		 */
		if(!empty($items['children']))
		{
			$html.='<li class="'.$items['type'].'">';
			if (trim($items['type']) == 'm' && !$adchild) {
					if (isset($admins[$items['name']]))
						$adcheck = 'checked';
					$html .= '<input class="checkbox" type=checkbox name="admin['.$items['name'].']" value="admin'.$items['id'].'" '.$adcheck.' /> ';
			}
			$html.='<input class="checkbox" type=checkbox name="permission['.$items['id'].']" value="'.$items['id'].'"'.$mod.' /> '.prettify($items['name']).": ".$items['description'].'<ul class="permission">';
			foreach($items['children'] as $child)
			{
				$html.= mktree($child, $checked, $admins, $setall, true);
			}	
			$html.='</ul></li>';
		}
		else
		{

			$html.='<li class="'.$items['type'].'">';
			if (trim($items['type']) == 'm' && !$adchild) {
					if (isset($admins[$items['name']]))
						$adcheck = 'checked';
					$html .= '<input class="checkbox" type=checkbox name="admin['.$items['name'].']" value="admin'.$items['id'].'" '.$adcheck.' /> ';
			}
			$html.='<input class="checkbox"  type=checkbox name="permission['.$items['id'].']" value="'.$items['id'].'"'.$mod.' /> '.prettify($items['name']).': '.$items['description'].'</li>';

		}
		return $html;
	}

?>
