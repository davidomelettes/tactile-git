<ul id="resolve_tickets" class="related_list">
	{foreach item=ticket from=$tickets}
	<li>
		<span><a href="http://{$resolve_address}.resolverm.com/tickets/view/{$ticket.id}">#{$ticket.id}: {$ticket.summary}</a></span>
		<dl>
			<dt>Status:</dt>
			<dd>{$ticket.status}</dd>
			{if $ticket.person}
				<dt>Person:</dt>
				<dd><a href="/people/view/{$ticket.person_id}">{$ticket.person}</a></dd>
			{/if}
			<dt>Updated:</dt>
			<dd>{$ticket.lastupdated}</dd>
			<dt>Created:</dt>
			<dd>{$ticket.created}</dd>
		</dl>
	</li>
	{foreachelse}
	<li class="none_yet">No Tickets</li>
	{/foreach}
</ul>