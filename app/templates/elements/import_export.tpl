{if $current_user->isAdmin() || ($permission_import_enabled && $_area ne 'opportunities') || $permission_export_enabled || ($_area eq 'activities' && $current_user->GetCalendarAddress())}
{foldable key="import_export" title="Import / Export"}
	<ul class="sidebar_options">
		{if $current_user->isAdmin() || $permission_import_enabled}
			{if $_area eq 'people' || $_area eq 'organisations'}
			<li><a class="action sprite sprite-import" href="/import/">Import contacts into Tactile</a></li>
			{/if}
		{/if}
			
		{if $current_user->isAdmin() || $permission_export_enabled}
			{if $selected_tags}
			<li class="query"><a class="action sprite sprite-export" href="/{$_area}/export?{$current_query}">Export query results as CSV</a></li>
			{elseif $sub_title && (($action neq "by_status") && ($action neq "by_source") && ($action neq "by_classification") && ($action neq "by_rating") && ($action neq "by_industry") && ($action neq "by_type"))}
			<li class="query"><a class="action sprite sprite-export" href="/{$_area}/export?query={$action}&amp;{$current_query}">Export query results as CSV</a></li>
			{/if}
			
			{if $_area eq 'people' || $_area eq 'organisations'}
			<li><a class="action sprite sprite-export" href="/organisations/export">Export all Organisations as CSV</a></li>
			{if $restriction eq "mine"}
			{if $_area eq 'people'}
			<li class="query"><a class="action sprite sprite-export" href="/{$_area}/export?restriction=mine">Export my People as CSV</a></li>
			{elseif $_area eq 'organisations'}
			<li class="query"><a class="action sprite sprite-export" href="/{$_area}/export?restriction=mine">Export my Organisations as CSV</a></li>
			{/if}
			{/if}
			<li><a class="action sprite sprite-export" href="/people/export">Export all People as CSV</a></li>
			{if $_area eq 'people' && $current_user->getAccount()->isCampaignMonitorEnabled() && $current_user->isAdmin()}
				{if $upsell}
					<li class="query">Subscribing to Campaign Monitor is only available on paid plans. <a href="">Upgrade Now</a></li>
				{else}
					{if $selected_tags}
					<li class="query"><a id="cm_export_link" class="action sprite sprite-export" href="/{$_area}/export_to_campaignmonitor?{$current_query}">Subscribe results to Campaign Monitor</a></li>
					{elseif $sub_title}
					<li class="query"><a id="cm_export_link" class="action sprite sprite-export" href="/{$_area}/export_to_campaignmonitor?query={$action}&amp;{$current_query}">Subscribe results to Campaign Monitor</a></li>
					{else}
					<li><a id="cm_export_link" class="action sprite sprite-export" href="/{$_area}/export_to_campaignmonitor">Subscribe all People to Campaign Monitor</a></li>
					{/if}
				{/if}
			{/if}
			{/if}
			
			{if $_area eq 'opportunities'}
			<li><a class="action sprite sprite-export" href="/opportunities/export">Export all Opportunities as CSV</a></li>
			<li><a class="action sprite sprite-export" href="/opportunities/export?restriction=open">Export Open Opportunities as CSV</a></li>
				<li><a class="action sprite sprite-export" href="/opportunities/export?restriction=mine">Export My Opportunities as CSV</a></li>
			{/if}
		{/if}
		
		{if $_area eq 'activities'}
		{if $current_user->isAdmin() || $permission_export_enabled}
		<li><a class="action sprite sprite-export" href="/activities/export">Export all Activities as CSV</a></li>
		{/if}
		{if $current_user->GetCalendarAddress()}
		<li><a class="action sprite sprite-export" href="{$current_user->GetCalendarAddress()}">Subscribe to my iCalendar feed</a></li>
		{/if}
		{/if}
	</ul>
{/foldable}
{/if}
