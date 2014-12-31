<div id="right_bar">
	{foldable}
		<p><a href="/admin/">Back to Admin</a></p>
	{/foldable}
	{foldable extra_class="help" title="Custom Fields Help" key="custom_fields_help"}
		<p>Custom Fields allow you to store information specific to your business
		against <span class="sprite sprite-organisation">Organisations,</span>
		<span class="sprite sprite-person">People,</span>
		<span class="sprite sprite-opportunity">Opportunities,</span>
		and <span class="sprite sprite-activity">Activities.</span></p>
		<p>Use the table on the left to create/edit Fields,
		define what type of information they will store, and which object types they apply to.</p>
	{/foldable}
</div>
<div id="the_page">
	<div class="admin_holder">
		<div id="page_title">
			<h2>Custom Fields</h2>
		</div>
		<form action="/customfields/save/" method="post">
			<div class="content_holder">
				<table class="custom_values index_table" id="custom_fields_table">
					<thead>
					<tr>
						<th id="field_name">Field Name</th>
						<th>Type</th>
						<th id="enabled_for">Enabled For</th>
						<th class="delete" id="delete"></th>
					</tr>
					</thead>
					<tfoot class="form_controls">
						<tr>
							<td colspan="4"><a class="action">Add New Value</a><span class="or"> or </span><input type="submit" value="Save" /></td>
						</tr>
					</tfoot>
					<tbody>				
					{foreach name=values item=field key=fieldid from=$values}
						<tr id="custom_field_{$field->id}">
							<td><input name="custom[{$field->id}][name]" class="name" type="text" value="{$field->name}"/></td>
							<td>
								{if $field->type eq 's'}
								Option List
								<input type="hidden" name="custom[{$field->id}][type]" value="s" />
								{assign var=farray value=$field->toArray()}
								{assign var=options value=$farray.options}
								{foreach from=$options item=option name=options}
								<span class="addRow">
									<input class="name" type="text" name="custom[{$field->id}][type][option][{$option.id}]" value="{$option.value}" id="custom_option_{$option.id}"/>
									{if $options|count > 1}
									<img class="delete_option" src="/graphics/tactile/icons/cross.png"/>
									{/if}
									{if $smarty.foreach.options.last}
									<img class="plus" src="/graphics/tactile/icons/add.png"/>
									{/if}
								</span>
								{/foreach}
								{elseif $field->type eq 't'}
								Text
								{elseif $field->type eq 'n'}
								Numeric
								{elseif $field->type eq 'c'}
								Yes/No
								{/if}
							</td>
							<td>
								<span class="sprite sprite-organisation" title="Organisations"><input name="custom[{$field->id}][organisations]" value="1" type="checkbox" {if $field->organisations   eq 't'}checked="checked"{/if}></span>
								<span class="sprite sprite-person" title="People"><input name="custom[{$field->id}][people]" value="1" type="checkbox" {if $field->people   eq 't'}checked="checked"{/if}></span>
								<span class="sprite sprite-opportunity" title="Opportunities"><input name="custom[{$field->id}][opportunities]" value="1" type="checkbox" {if $field->opportunities  eq 't'}checked="checked"{/if}></span>
								<span class="sprite sprite-activity" title="Activities"><input name="custom[{$field->id}][activities]" value="1" type="checkbox" {if $field->activities  eq 't'}checked="checked"{/if}></span></td>
							<td class="delete">
								<a class="action" href="/customfields/delete/?id={$field->id}">Delete</a>
							</td>
						</tr>
					{foreachelse}
						<tr class="none_yet">
							<td colspan="4">You haven't added any Custom Fields yet</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
		</form>
	</div>
</div>
