<th class="primary">Summary</th>
<th>Attached To</th>
{if $q && $qb_fields}
{include file='elements/indexes/custom_field_headers.tpl' model=$opp}
{else}
<th>Sales Stage</th>
<th>Expected Close</td>
<th class="numeric">Value</th>
{/if}