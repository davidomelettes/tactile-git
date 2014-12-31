<ul id="zendesk_tickets" class="related_list">
	{foreach key=link item=ticket from=$tickets}
	<li>
		<span><a href="{$ticket->link_for_site($zendesk_siteaddress)}">#{$ticket->id} {$ticket->subject}</a></span>
		<dl>
			<dt>Status:</dt>
			<dd>{$ticket->status}</dd>
			<dt>Priority:</dt>
			<dd>{$ticket->priority}</dd>
			<dt>Created:</dt>
			<dd>{$ticket->created}</dd>
		</dl>
	</li>
	{foreachelse}
	<li class="none_yet">No Tickets</li>
	{/foreach}
</ul>
<p>Last Updated: {$last_updated} <a href="{$refresh_link}">(update)</a></p>