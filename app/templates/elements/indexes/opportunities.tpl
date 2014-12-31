{assign var=opp value=$model}
<td class="primary">
	<a href="/opportunities/view/{$opp->id}/" class="sprite-med sprite-opportunity{if $opp->is_archived()}_archived{/if}_med">{$opp->name|escape}</a>
</td>
<td>
	{if $opp->organisation_id neq ""}
	<a class="index_label" href="/organisations/view/{$opp->organisation_id}/">{$opp->organisation|escape}</a>
	{else}
	&nbsp;
	{/if}
	{if $opp->person_id neq ""}
	<br /><a class="index_label" href="/people/view/{$opp->person_id}">{$opp->person}</a>
	{/if}
</td>
{if $q && $qb_fields}
{include file='elements/indexes/custom_field_data.tpl' model=$opp}
{else}
<td><a href="/opportunities/by_status/?q={$opp->status|@urlencode}">{$opp->status|escape}</a></td>
<td>{$opp->getFormatted('enddate')}</td>
<td class="numeric">{$opp->cost|escape}</td>
{/if}