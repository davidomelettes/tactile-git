{if $custom_fields|count > 0}
	<li class="odd single" id="{$for}_custom_fields">
		<script type="text/javascript">
		Tactile.custom_fields={$custom_fields_json};

		Tactile.existing_custom_fields={$existing_custom_fields_json};
		</script>
		<span class="view_label"><span><strong>Custom Fields</strong></span></span><span class="view_data"><span><a id="custom_fields_update" class="action">Update</a></span></span>
	</li>
	{foreach item=field from=$custom_fields_map name=custom_fields}
	<li class="custom{if $smarty.foreach.custom_fields.index % 2 == 0} odd{else} even{/if}" id="{$for}_custom_field_row_{$field->id}">
		<span class="view_label"><span>{$field->name}</span></span><span class="view_data"><span>
			{if $field->type eq 'c'}
				{if $field->enabled == 't'}
					<img src="/graphics/tactile/icons/tick.png"/>
				{else}
					<img src="/graphics/tactile/icons/cross.png"/>
				{/if}
			{elseif $field->type eq 's'}
				{$field->option_name}
			{elseif $field->type eq 'n'}
				{$field->value_numeric}
			{else}
				{$field->value}
			{/if}
		</span></span>
	</li>
	{/foreach}
	<li class="spacer"> </li>
{/if}
