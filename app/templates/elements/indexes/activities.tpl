{assign var=act value=$model}
<td class="primary">
	<a href="/activities/view/{$act->id}/" class="sprite-med sprite-activity_med">{$act->name|escape}</a>
</td>
<td>
	{if $act->organisation}
	<a href="/organisations/view/{$act->organisation_id}/">{$act->organisation|escape}</a>
		{if $act->person}
		<br /><a href="/people/view/{$act->person_id}/">{$act->person|escape}</a>
		{/if}
	{elseif $act->person}
	<a href="/people/view/{$act->person_id}/">{$act->person|escape}</a>
	{else}
	&nbsp;
	{/if}
</td>
{if $q && $qb_fields}
{include file='elements/indexes/custom_field_data.tpl' model=$act}
{else}
<td>
	{if $act->isEvent()}
	<span class="single_line">{$act->date_string()} <span class="until">until</span></span><br />
	<span class="single_line">{$act->end_date_string()}</span>
	{else}
	<span class="single_line">{$act->date_string()}</span>{/if}
</td>
<td>{$act->assigned_string(true)}</td>
{/if}