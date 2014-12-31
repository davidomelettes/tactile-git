<div id="right_bar">
	{include file="elements/recently_viewed.tpl"}
	{include file="elements/import_export.tpl"}
	{foldable key="activity_overview_help" title="Activity Help" extra_class="help"}
		<p>In Tactile CRM, Activities are events or tasks that you and your colleagues need to do.</p>
		<p>Activities may be associated with an <a href="/organisations/">Organisation</a>, <a href="/people/">Person</a>, <a href="/opportunities/">Opportunity</a>, or any combination of the above.</p>
	{/foldable}
	{include file="elements/side_tag_list.tpl" key="activities_tag_list" type="activities"}
</div>
<div id="the_page">
	<div class="index_holder">
		<div id="page_title">
			{include file="elements/paging.tpl" for="activities" add="activity" add_text="Add New Activity"}
			<h2>
				{if $selected_tags}
				Tagged Activities
				{elseif $sub_title}
					{if $action eq 'to_user' OR $action eq 'by_user'}
						Current Activities {$sub_title|escape}
					{else}
						Activities {$sub_title|escape}
					{/if}
				{elseif $restriction eq 'all'}
				Activities (A-Z)
				{elseif $restriction eq 'mine'}
				Activities Assigned to Me
				{elseif $restriction eq 'my_overdue'}
				My Overdue Activities
				{elseif $restriction eq 'all_overdue'}
				All Overdue Activities
				{elseif $restriction eq 'all_current'}
				All Current Activities (by Date)
				{elseif $restriction eq 'recently_completed'}
				Recently Completed Activities
				{elseif $restriction eq 'recently_viewed'}
				Recently Viewed Activities
				{elseif $restriction eq 'my_today'}
				My Activities for Today
				{/if}
			</h2>
		</div>
		<div id="page_main">
			{if $selected_tags}
			<div class="restriction">
				{if $selected_tags}Tags: {foreach name=tags item=tag from=$selected_tags}"{$tag|escape}"{if !$smarty.foreach.tags.last} &amp; {/if}{/foreach}{/if}
			</div>
			{/if}
			
			{include file="elements/index_table.tpl" for="activities" index_collection=$activitys}
			
			{if $num_pages > 1}
			<div class="bottom_paging">
				{include file="elements/paging.tpl" for="activities" add="activity" add_text="Add New Activity"}
			</div>
			{/if}
		</div>
	</div>
</div>
