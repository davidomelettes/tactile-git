{assign var=org value=$model}
<td class="primary">
	<a href="/organisations/view/{$org->id}/" class="sprite-med sprite-organisation_med">{$org->name|escape}</a>
</td>
{if $q && $qb_fields}
{include file='elements/indexes/custom_field_data.tpl' model=$org}
{else}
<td>
	{if $org->phone neq ""}<span class="single_line phone">{$org->phone|escape}</span>{else}&nbsp;{/if}<br/>
	{if $org->fax neq ""}<span class="single_line fax">{$org->fax|escape}</span>{else}&nbsp;{/if}
</td>
<td>
	{if $org->website neq ""}<span class="single_line">{$org->getFormatted('website', 'OmeletteURLFormatter')}</span>{else}&nbsp;{/if}<br />
	{if $org->email neq ""}<a href="mailto:{$org->email|escape}{if $current_user->getDropboxAddress()}?bcc={$current_user->getDropboxAddress()}{/if}" class="out email">{$org->email|escape}</a>{else}&nbsp;{/if}
</td>
<td>
	{if $org->town}<a href="/organisations/by_town/?q={$org->town|@urlencode}">{$org->town|escape}</a>{else}&nbsp;{/if}<br />
	{if $org->county}<a href="/organisations/by_county/?q={$org->county|@urlencode}">{$org->county|escape}</a>{else}&nbsp;{/if}
</td>
{/if}