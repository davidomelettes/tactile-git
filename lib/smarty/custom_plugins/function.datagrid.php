<?php
// should be deprecated.
function smarty_function_datagrid($params,&$smarty) {

		# Set the variables we need 
		if(isset($params['funcs']))$functions = $params['funcs'];
		$grid = $params['grid'];
		if(isset($params['widest_column']))$widest_column = $params['widest_column'];
		$limits = array(10,20,50);
		$orderby = $grid->orderby;
		$records = $grid->records;
		$page=$grid->page;
		$search = false;
		$fields = $grid->getFields();
		$searchString;

	#Set the target
		$location = $smarty->get_template_vars('module').'&amp;controller='.$smarty->get_template_vars('controller').'&amp;action='.$smarty->get_template_vars('action');
		$short_location = 'module='.$smarty->get_template_vars('module').'&amp;controller='.$smarty->get_template_vars('controller');

		if(count($grid->search)!==0)
		{
			$search = true;
			$html='<b>Search Results for:</b> <i>';
			foreach($grid->search as $search)
				$html.=  $search.', ';
				$searchString.='&search[]='.$search;
				
			$html.='</i>(<a href="/?module='.$location.'">clear search</a>)<br />';
		}
		if(count($grid->searchField) !==0)
		{
		foreach($grid->searchField as $search)
				$searchString.='&amp;field[]='.$search;
		}
			
		if($search)
		{
			$location .= $searchString;
			$short_location .= $searchString; 
		}
		
		#Set the direction
		if($grid->direction==='ASC')$direction='DESC';
		else $direction = 'ASC';

		#If there are fields to display
		if(!empty($fields)){
				
	
			#Check if the delete function is specified and create a form
			if(isset($functions['delete']))
			{
				$delete=true;
				$html.="\n".'<form action="/?'.$short_location.'&amp;action=delete_'.$smarty->get_template_vars('action').'" method="post">'."\n \t".'<input type="hidden" name="action" value="delete" />';
			}
		
		
			$html .= '<table>
						<thead>
							<tr>';	
			# Print out the field names
			foreach($fields as $field)
			{ 	
				$value = split("_",$field->name);
				$name = $field->name;

				if($name == $widest_column)
					$width = ' style="width: 100%"';
				else
					$width = '';
				
				if($orderby === $name){
					$name = $name.'&amp;direction='.$direction;
				}
				$name = '&amp;orderby='.$name;
				$html.='<td'.$width.'><a href="/?module='.$location.$name.'&limit='.$grid->limit.'">'.$module.ucfirst($value[0])."</a></td>";
			}
			# Print out any functions
			if(isset($functions))
			{		
				foreach($functions as $key=>$function)
				{
					$html.="<td>".ucfirst($key)."</td>";
				}
			}
			$html.="</tr></thead>\n";
			$html.='<tfoot><tr><td colspan="0">&nbsp;</td></tr></tfoot>';
			$html.='<tbody>';
			#print out values
			foreach($grid as $row) {
				$id = $row->getId();
				$html.="<tr>\n";
				$num=0;
				foreach($row as $column) {
					if($num === 0)
					{
						$html.="<td>".'<a href="/?module='.$smarty->get_template_vars('module').'&amp;controller='.$smarty->get_template_vars('controller').'&amp;action='.$smarty->get_template_vars('clickaction').'&amp;id='.$id.'">'.$column."</a></td>\n";
						$num++;
					}
					else
					{
						$html.="<td>".$column."</td>\n";
					}


					
				}
				if(isset($functions))
				{
					foreach($functions as $key=>$function)
					{
						if(strtolower($key) === 'delete')
							$html.='<td><input type=checkbox name="delete['.$id.']" /></td>';
						else
							$html.="<td>".'<a href=/?module='.$smarty->get_template_vars('module').'&amp;controller='.$smarty->get_template_vars('controller').'&amp;action='.strtolower($function)."&amp;id=".$id.">".$key."</a></td>\n";
					}
				}
				$html.="</tr> \n";
		
			}
			if($grid->isEmpty()) { 
				$html.='<tr><td colspan="0">No entries</td></tr>';

			}
		$html .= '</tbody></table>';
		

		#If delete is true, we have a form so close it!
		if($delete)
		{
				$html.="\n".'<input type="submit" value="Delete" onclick="return confirmSubmit()" /> <input type="reset" />'."\n </form>";
		}	

		# Print out the navigation.
		$html.="\n".' <br /><div class="tablenav"><form action="#"><div id="subtablenav" class="pagingcontainer">';
		if($grid->pages > 1)
		{

			if($page > 1)
				$html.=' <a href="/?module='.$location.'&amp;page=1&amp;orderby='.$grid->orderby.'&amp;limit='.$grid->limit.'" >&lt;&lt; First</a>  ';
			if($page > 1)
				$html.=' <a href="/?module='.$location.'&amp;page='.($page - 1).'&amp;orderby='.$grid->orderby.'&amp;limit='.$grid->limit.'" >&lt; Prev</a>  ';
			if(($page + 1)<= $grid->pages)
				$html.=' <a href="/?module='.$location.'&amp;page='.($page + 1).'&amp;orderby='.$grid->orderby.'&amp;limit='.$grid->limit.'" >Next &gt;</a> ';
			if($grid->pages > $page)
				$html.=' <a href="/?module='.$location.'&amp;page='.$grid->pages.'&amp;orderby='.$grid->orderby.'&amp;limit='.$grid->limit.'" >Last &gt;&gt;</a> ';
		}
		$html.="</div>\n <fieldset><label for='setlimit'>Display:</label><select".' name="setlimit" onchange="limit(this)">';
		foreach($limits as $limit){
				$html.='<option value="/?module='.$location.'&amp;limit='.$limit.'"';
				if($limit == $grid->limit) $html.=' selected="selected" ';
				$html.='>'.$limit.'</option>';
		}


					$html.="  </select></fieldset> \n </form> \n </div>";
	}

	return $html;
}
?>
