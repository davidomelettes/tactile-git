{assign var=person value=$model}
<td class="primary">
	<a href="/people/view/{$person->id}/" class="sprite-med sprite-person_med">{$person->fullname|escape}</a>
</td>
<td>
	{if $person->organisation}
	<a class="index_label" href="/organisations/view/{$person->organisation_id}/">{$person->organisation|escape}</a>
	{else}
	&nbsp;
	{/if}
</td>
{if $q && $qb_fields}
{include file='elements/indexes/custom_field_data.tpl' model=$person}
{else}
<td>
	{if $person->phone neq ""}<span class="single_line phone">{$person->phone|escape}</span>{else}&nbsp;{/if}<br />
	{if $person->mobile neq ""}<span class="single_line mobile">{$person->mobile|escape}</span>{else}&nbsp;{/if}
</td>
<td>
	{if $person->email neq ""}<a href="mailto:{$person->email|escape}{if $current_user->getDropboxAddress()}?bcc={$current_user->getDropboxAddress()}{/if}" class="index_label out email">{$person->email|escape}</a>{/if}<br/>&nbsp;
</td>
{/if}