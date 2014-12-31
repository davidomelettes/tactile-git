{foreach from=$q item=field key=field_name name=cfield}
{if $qb_fields.$field_name.type eq 'custom_field'}
<th>{$qb_fields.$field_name.label|escape}</th>
{elseif $field_name neq 'gen_name' && $field_name neq 'gen_description'}
<th>{$qb_fields.$field_name.label|escape}</th>
{/if}
{/foreach}
