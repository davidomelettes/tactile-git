<div id="right_bar">
	{foldable}
		<p><a href="/admin/">Back to Admin</a></p>
	{/foldable}
	{foldable key="setup_overview_help" title="Setup Help" extra_class="help"}
		<p>There are a number of places in Tactile CRM where you can choose the values you select from in order to customise the application to your needs.</p>
		<p>Use the list on the left to see which values you can change, and set them up to reflect the way your business works.</p>
	{/foldable}
</div>
<div id="the_page">
	<div class="admin_holder">
		<div id="page_title">
			<h2>Configurable Values</h2>
		</div>
		<form action="/setup/save/" method="post">
			{if $selected_group && $selected_option}
			<div>
				<input type="hidden" value="{$selected_group}" name="group" />
				<input type="hidden" value="{$selected_option}" name="option" />
			</div>
			{/if}
			{foreach name=groups item=options key=group from=$groups}
			<div class="content_holder">
				<h3>{if $group eq 'companies'}Organisations{else}{$group|prettify}{/if}</h3>
				<ul class="admin_list">
					{foreach name=options item=classname key=option from=$options}
					<li>
						<h4><a class="group" href="/setup/?group={$group}&amp;option={$option}">
						{if $group eq 'opportunities' && $option eq 'status'}
						Sales Stage &raquo;
						{else}
						{$option|prettify} &raquo;
						{/if}
						</a></h4>
						
						{if $group eq 'companies' && $option eq 'status'}
						<p>Is this Organisation new to you? Do they need contacting?</p>
						{elseif $group eq 'companies' && $option eq 'source'}
						<p>Where did you find out about this Organisation?</p>
						{elseif $group eq 'companies' && $option eq 'classification'}
						<p>Is this Organisation large or small? Public sector or private?</p>
						{elseif $group eq 'companies' && $option eq 'rating'}
						<p>How valuable is this Organisation to you?</p>
						{elseif $group eq 'companies' && $option eq 'industry'}
						<p>What type of business does this Organisation conduct?</p>
						{elseif $group eq 'companies' && $option eq 'type'}
						<p>Is this Organisation a business, a charity, etc?</p>
						{elseif $group eq 'opportunities' && $option eq 'status'}
						<p>At what stage of your sales pipeline is this Opportunity? Initial discussions, negotiations, etc.</p>
						{elseif $group eq 'opportunities' && $option eq 'source'}
						<p>Where did this Opportunity come from?</p>
						{elseif $group eq 'opportunities' && $option eq 'type'}
						<p>What kind of Opportunity is this? New business, or repeated custom?</p>
						{elseif $group eq 'activities' && $option eq 'type'}
						<p>Schedule a call back? Meeting? What are you doing?</p>
						{/if}
						
						{if $group eq $selected_group && $option eq $selected_option}
						<div class="content">
						<table class="group_values index_table" id="{$group}_{$option}">
							<thead>
								<tr>
									<th>Name</th>
									{if $current_model && $current_model->isField('position')}
									<th>Position</th>
									{/if}
									{if $group eq 'opportunities' && $option eq 'status'}
									<th>Position</th>
									<th class="toggle">Open?</th>
									<th class="toggle">Won?</th>
									{/if}
									<th></th>
								</tr>
							</thead>
							<tfoot class="form_controls">
								<tr>
									<td colspan="{if $group eq 'opportunities' && $option eq 'status'}5{else}2{/if}"><a class="action">Add New Value</a><span class="or"> or </span><input type="submit" value="Save" /></td>
								</tr>
							</tfoot>
							<tbody>
								{foreach name=values item=value from=$values}
								<tr id="{$group}_{$option}_{$value->id}">
									<td>
										<input class="id" type="hidden" name="{$group}[{$option}][{$value->id}][id]" value="{$value->id}" />
										<input class="name" type="text" value="{$value->name|h:$smarty.const.ENT_QUOTES}" name="{$group}[{$option}][{$value->id}][name]" />
									</td>
									{if $group eq 'opportunities' && $option eq 'status'}
									<td>
										<input type="text" class="position short" name="{$group}[{$option}][{$value->id}][position]" value="{$value->position}" />
									</td>
									<td class="toggle">
										<input type="checkbox" class="open checkbox" name="{$group}[{$option}][{$value->id}][open]" {if $value->open eq 't'}checked="checked"{/if} />
									</td>
									<td class="toggle">
										<input type="checkbox" class="won checkbox" name="{$group}[{$option}][{$value->id}][won]" {if $value->won eq 't'}checked="checked"{/if} />
									</td>
									{/if}
									<td class="t-right" style="width: 100%;"><a class="action" href="/setup/delete/?group={$group}&amp;option={$option}&amp;id={$value->id}">Delete</a></td>
								</tr>
								{/foreach}
							</tbody>
						</table>
						</div>
						{/if}
					</li>
					{/foreach}
				</ul>
			</div>
			{/foreach}
		</form>
		<!-- Adding hidden form for each status that can be deleted -->
		{foreach name=groups item=options key=group from=$groups}
			{foreach name=options item=classname key=option from=$options}
				{if $group eq $selected_group && $option eq $selected_option}
				{foreach name=values item=value key=id from=$values}
					<form action="/setup/delete/" method="post" class="delete_form" id="status_delete_{$id}_form">
						<p><input type="hidden" value="{$selected_group}" name="group" />
						<input type="hidden" value="{$selected_option}" name="option" />
						<input type="hidden" value="{$id}" name="id" /></p>
					</form>
				{/foreach}		
				{/if}
			{/foreach}
		{/foreach}
		<!-- End hidden delete forms -->
	</div>
</div>
