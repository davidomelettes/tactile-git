<script type="text/javascript">
	Tactile.qb = {$qb_fields|@json_encode};
	{if $q}
	Tactile.q = {$q|@json_encode};
	{/if}
</script>
<div id="right_bar">
	{foldable title="Advanced Search Help" key="advanced_search_help" extra_class="help"}
	<p>Search for records based on a series of filters. To add a filter to your search, select a field from the Add Filter select box.</p>
	<p>Clicking on "Save this Search" will allow you to store the current set of filters for later use.
	These searches can then be accessed from the relevant tab at the top of the screen.</p>
	{/foldable}
	{foldable title="Saved Searches" key="saved_searches"}
	<ul class="related_list">
	{foreach from=$advanced_searches item=search}
		<li><a class="sprite sprite-search" href="/search/recall/{$search->id}">{$search->name|escape}</a><a href="/search/delete/{$search->id}" class="action delete">Delete</a></li>
		{foreachelse}
		<li class="empty">You don't have any saved searches yet.</li>
	{/foreach}
	</ul>
	{/foldable}
</div>
<div id="the_page">
	<div id="page_title">
		<h2>Advanced Search</h2>
	</div>
	<div id="page_main">
		<div class="content_holder">
			<form method="get" action="/search/advanced" class="saveform">
				<div id="queryBuilder" class="round-all">
					<ul>
						<li class="row record_type">
							<label for="qb_record_type">Record Type</label>
							<select id="qb_record_type" type="text" name="r">
								<option value="org"{if $r eq 'org'} selected="selected"{/if}>Organisations</option>
								<option value="per"{if $r eq 'per'} selected="selected"{/if}>People</option>
								<option value="opp"{if $r eq 'opp'} selected="selected"{/if}>Opportunities</option>
								<option value="act"{if $r eq 'act'} selected="selected"{/if}>Activities</option>
							</select>
						</li>
					
					{if $q}
					{foreach from=$q item=filter key=filter_name}
						<li class="row" id="{$filter_name}">
							<a class="delete sprite sprite-remove"></a>
							<label for="qb_{$filter_name}">{$qb_fields.$filter_name.label}</label>
							{if $qb_fields.$filter_name.operators}
							<select name="q[{$filter_name}][op]" class="ops">
							{foreach from=$qb_fields.$filter_name.operators item=op}
								<option value="{$op}"{if $filter.op eq $op} selected="selected"{/if}>{$op}</option>
							{/foreach}
							{/if}
							</select>
							{if $qb_fields.$filter_name.accept eq 'select'}
							<select id="qb_{$filter_name}" type="checkbox" name="q[{$filter_name}][value]" class="value">
							{foreach from=$qb_fields.$filter_name.options item=option_value key=option_key}
								<option value="{$option_key}"{if $option_key == $filter.value} selected="selected"{/if}>{$option_value|escape}</option>
							{/foreach}
							</select>
							{elseif $qb_fields.$filter_name.accept eq 'boolean'}
							<select id="qb_{$filter_name}" type="checkbox" name="q[{$filter_name}][value]" class="value">
								<option value="TRUE"{if $filter.value == 'TRUE'} selected="selected"{/if}>TRUE</option>
								<option value="FALSE"{if $filter.value == 'FALSE'} selected="selected"{/if}>FALSE</option>
							</select>
							{else}
							<input id="qb_{$filter_name}" type="text" name="q[{$filter_name}][value]" class="value{if $qb_fields.$filter_name.accept eq 'date'} datefield{/if}" value="{$filter.value}" />
							{/if}
						</li>
					{/foreach}
					{/if}
					
					</ul>
					
					<div class="row">
						<select id="qb_add" class="add">
							<option value="" selected="selected">-- Add Filter --</option>
							{foreach from=$qb_fields item=field key=field_name}
							<option value="{$field_name}">{$field.label|escape}</option>
							{/foreach}
						</select>
					</div>
					<div class="row">
						<a class="action cancel">Save this Search</a></a><input type="submit" class="submit" value="Search" />
					</div>
				</div>
			</form>
		</div>
		
		{if $collection}
			{include file="elements/paging.tpl" for="search" action="advanced"}
			
			{if $r eq 'act'}
			{include file="elements/index_table.tpl" for="activities" index_collection=$collection}
			{elseif $r eq 'opp'}
			{include file="elements/index_table.tpl" for="opportunities" index_collection=$collection}
			{elseif $r eq 'per'}
			{include file="elements/index_table.tpl" for="people" index_collection=$collection}
			{else}
			{include file="elements/index_table.tpl" for="organisations" index_collection=$collection}
			{/if}
			
			{if $num_pages > 1}
			<div class="bottom_paging">
			{include file="elements/paging.tpl" for="people"}
			</div>
			{/if}
		{/if}
		
	</div>
</div>