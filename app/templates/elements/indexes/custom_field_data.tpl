{foreach from=$q item=field key=field_name name=cfield}
	{if $qb_fields.$field_name.options}
	{if $qb_fields.$field_name.type eq 'custom_field'}
	{assign var='column' value='cfm'|cat:$qb_fields.$field_name.field_id}
	{else}
	{assign var='column' value=$qb_fields.$field_name.column}
	{/if}
	{assign var='option_id' value=$model->$column}
	<td>{$qb_fields.$field_name.options[$option_id]}</td>
	
	{elseif $qb_fields.$field_name.type eq 'custom_field'}
	{assign var='cfm' value='cfm'|cat:$qb_fields.$field_name.field_id}
	<td>{$model->$cfm|escape}</td>
	
	{elseif $field_name neq 'gen_name' && $field_name neq 'gen_description'}
	{assign var='column' value=$qb_fields.$field_name.column}
	<td>{$model->getFormatted($column)}</td>
	
	{/if}
{/foreach}
