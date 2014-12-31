<div id="right_bar">
	{include file="elements/usage_warning.tpl"}
	{include file="elements/reports/dashboard_graph.tpl"}
	{include file="elements/recently_viewed.tpl"}
	{include file="elements/import_export.tpl"}
	{foldable key="opportunity_overview_help" title="Opportunity Help" extra_class="help"}
		<p>In Tactile CRM, Opportunities are sources of income, which could be potential, won, or lost.</p>
		<p>An Opportunity can be attached to an <a href="/organisations/">Organisation</a> and/or a <a href="/people/">Person</a>.</p>
		<p>An Opportunity may also have many <a href="/activities/">Activities</a> associated with it.</p>
	{/foldable}
	{include file="elements/side_tag_list.tpl" key="opportunities_tag_list" type="opportunities"}
</div>
<div id="the_page">
	<div class="index_holder">
		<div id="page_title">
			{include file="elements/paging.tpl" for="opportunities" add="opportunity" add_text="Add New Opportunity"}
			<h2>
				{if $selected_tags}
				Tagged Opportunities
				{elseif $sub_title}
				Opportunities {$sub_title|escape}
				{elseif $restriction eq 'open'}
				All Open Opportunities (A-Z)
				{elseif $restriction eq 'open_date'}
				All Open Opportunities (Date)
				{elseif $restriction eq 'mine_open'}
				My Open Opportunities (A-Z)
				{elseif $restriction eq 'mine_open_date'}
				My Open Opportunities (Date)
				{elseif $restriction eq 'mine'}
				Opportunities Assigned to Me
				{elseif $restriction eq 'recently_won'}
				Recently Won Opportunities
				{elseif $restriction eq 'recently_lost'}
				Recently Lost Opportunities
				{elseif $restriction eq 'most_recent'}
				Recently Added Opportunities
				{elseif $restriction eq 'archived'}
				Archived Opportunities
				{elseif $restriction eq 'recently_viewed'}
				Recently Viewed Opportunities
				{/if}
			</h2>
		</div>
		<div id="page_main">
			{if $selected_tags}
			<div class="restriction">
				{if $selected_tags}Tags: {foreach name=tags item=tag from=$selected_tags}"{$tag|escape}"{if !$smarty.foreach.tags.last} &amp; {/if}{/foreach}{/if}
			</div>
			{/if}
			
			{include file="elements/index_table.tpl" for="opportunities" index_collection=$opportunitys}
			
			{if $num_pages > 1}
			<div class="bottom_paging">
				{include file="elements/paging.tpl" for="opportunities" add="opportunity" add_text="Add New Opportunity"}
			</div>
			{/if}
		</div>
	</div>
</div>
