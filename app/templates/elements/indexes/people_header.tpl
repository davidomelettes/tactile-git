<th class="primary">Name</th>
<th>Organisation</th>
{if $q && $qb_fields}
{include file='elements/indexes/custom_field_headers.tpl' model=$person}
{else}
<th></th>
<th></th>
{/if}