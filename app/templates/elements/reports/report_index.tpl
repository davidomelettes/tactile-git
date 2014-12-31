		<h2>
			{if !$show_user_box}
				{assign var="prefix" value="My "}
			{else}
				{assign var="prefix" value=""}
			{/if}
			{if $action eq 'sales_history'}
			{$prefix}Sales History
			{elseif $action eq 'pipeline'}
			{$prefix}Pipeline Graph
			{elseif $action eq 'pipeline_report'}
			{$prefix}Pipeline Report{if $selected_user} (for 
				{if $selected_user eq "!"}All Active Users)
				{elseif $selected_user eq "*"}All Users - inc. disabled)
				{elseif '/^@/'|preg_match:$selected_user}the {'/^@/'|preg_replace:'':$selected_user} group)
				{else}{$selected_user})
				{/if}{/if}
			{elseif $action eq 'sales_report'}
			{$prefix}Sales Report{if $selected_user} (for 
				{if $selected_user eq "!"}All Active Users)
				{elseif $selected_user eq "*"}All Users - inc. disabled)
				{elseif '/^@/'|preg_match:$selected_user}the {'/^@/'|preg_replace:'':$selected_user} group)
				{else}{$selected_user}){/if}
				{/if}
			{elseif $action eq 'opps_by_source_qty'}
			Opportunities by Source (Quantity)
			{elseif $action eq 'opps_by_source_cost'}
			Opportunities by Source (Cost)
			{elseif $action eq 'opps_by_type_qty'}
			Opportunities by Type (Quantity)
			{elseif $action eq 'opps_by_type_cost'}
			Opportunities by Type (Cost)
			{elseif $action eq 'opps_by_status_qty'}
			Opportunities by Status (Quantity)
			{/if}
		</h2>
