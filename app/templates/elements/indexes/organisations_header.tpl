<th class="primary">Name</th>
{if $q && $qb_fields}
{include file='elements/indexes/custom_field_headers.tpl' model=$org}
{else}
<th></th>
<th></th>
<th>Town</th>
{/if}