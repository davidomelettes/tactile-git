{if $is_attached}
{if $invoices|@count eq 0 && $estimates|@count eq 0}
<p>No Invoices or Estimates to show.</p>
{/if}
{include file="elements/invoice_list.tpl" invoices=$invoices}
{include file="elements/estimate_list.tpl" estimates=$estimates}
<p>Want to <a class="action freshbooks_reset_link" id="freshbooks_reset_{$Organisation->id}">remove this link with FreshBooks</a>?</p>
<p>This will only unlink this organisation and will NOT delete/change any data in FreshBooks.</p>
<p><em>(Last updated {$response_date})</em></p>
{else}
<p>Do you want to <a class="action freshbooks_add_link" id="freshbooks_add_{$Organisation->id}">link this Organisation</a> with a client in FreshBooks?</p>
{/if}