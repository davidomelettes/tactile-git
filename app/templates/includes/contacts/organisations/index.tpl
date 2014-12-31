<div id="right_bar">
	{include file="elements/usage_warning.tpl"}
	{include file="elements/recently_viewed.tpl"}
	{include file="elements/import_export.tpl"}
	{foldable key="organisation_overview_help" title="Organisation Help" extra_class="help"}
		<p>In Tactile CRM, Organisations represent the companies, businesses, and other groups with which you deal.</p>
		<p>An Organisation can contain many <a href="/people/">People</a>.</p>
	{/foldable}
	{include file="elements/side_tag_list.tpl" key="organisation_tag_list" type="organisations"}
</div>
<div id="the_page">
	<div class="index_holder">
		<div id="page_title">
			{include file="elements/paging.tpl" for="organisations" add="organisation" add_text="Add New Organisation"}
			<h2>
				{if $selected_tags}
				Tagged Organisations
				{elseif $sub_title}
				Organisations {$sub_title|escape}
				{elseif $restriction eq 'recently_viewed'}
				Recently Viewed Organisations
				{elseif $restriction eq 'alphabetical'}
				All Organisations (A-Z)
				{elseif $restriction eq 'recent'}
				Recently Added Organisations
				{elseif $restriction eq 'mine'}
				Organisations Assigned to Me
				{/if}
			</h2>
		</div>
		<div id="page_main">
			{if $selected_tags}
			<div class="restriction">
				{if $selected_tags}Tags: {foreach name=tags item=tag from=$selected_tags}"{$tag|escape}"{if !$smarty.foreach.tags.last} &amp; {/if}{/foreach}{/if}
			</div>
			{/if}
			
			{include file="elements/index_table.tpl" for="organisations" index_collection=$organisations}
			
			{if $num_pages > 1}
			<div class="bottom_paging">
				{include file="elements/paging.tpl" for="organisations" add="organisation" add_text="Add New Organisation"}
			</div>
			{/if}
		</div>
	</div>
</div>
