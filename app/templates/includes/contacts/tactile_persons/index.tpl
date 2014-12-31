<div id="right_bar">
	{include file="elements/usage_warning.tpl"}
	{include file="elements/recently_viewed.tpl"}
	{include file="elements/import_export.tpl"}
	{foldable key="person_overview_help" title="People Help" extra_class="help"}
		<p>In Tactile CRM, a Person is an individual with which you have contact.</p>
		<p>A Person can belong to an <a href="/organisations/">Organisation</a>.</p>
	{/foldable}
	{include file="elements/side_tag_list.tpl" key="people_tag_list" type="people"}
</div>
<div id="the_page">
	<div class="index_holder">
		<div id="page_title">
			{include file="elements/paging.tpl" for="people" add="person" add_text="Add New Person"}
			<h2>
				{if $selected_tags}
				Tagged People
				{elseif $sub_title}
				People {$sub_title|escape}
				{elseif $restriction eq 'recently_viewed'}
				Recently Viewed People
				{elseif $restriction eq 'alphabetical'}
				All People (by Surname A-Z)
				{elseif $restriction eq 'firstname'}
				All People (by Firstname A-Z)
				{elseif $restriction eq 'mine'}
				People Assigned to Me
				{elseif $restriction eq 'recent'}
				Recently Added People
				{elseif $restriction eq 'individuals'}
				Individuals
				{/if}
			</h2>
		</div>
		<div id="page_main">
			{if $selected_tags}
			<div class="restriction">
				{if $selected_tags}Tags: {foreach name=tags item=tag from=$selected_tags}"{$tag|escape}"{if !$smarty.foreach.tags.last} &amp; {/if}{/foreach}{/if}
			</div>
			{/if}
			
			{include file="elements/index_table.tpl" for="people" index_collection=$persons}
			
			{if $num_pages > 1}
			<div class="bottom_paging">
				{include file="elements/paging.tpl" for="people" add="person" add_text="Add New Person"}
			</div>
			{/if}
		</div>
	</div>
</div>
