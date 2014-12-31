{foldable key="my_dropbox" title="My Dropbox"}
	<ul class="sidebar_options unassigned_emails">
		{if $unassigned_emails > 0}
		<li><strong><a href="/emails/unassigned/" class="sprite sprite-alert">You have {$unassigned_emails} unassigned email{if $unassigned_emails > 1}s{/if}</a></strong></li>
		{/if}
		<li><a href="/emails/all" class="sprite sprite-email">View all of my emails</a></li>
	</ul>
{/foldable}
