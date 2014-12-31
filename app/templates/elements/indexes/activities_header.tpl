<th class="primary">Summary</th>
<th>Attached To</th>
{if $q && $qb_fields}
{include file='elements/indexes/custom_field_headers.tpl' model=$act}
{else}
<th>When</th>
<th>Assigned To</th>
{/if}