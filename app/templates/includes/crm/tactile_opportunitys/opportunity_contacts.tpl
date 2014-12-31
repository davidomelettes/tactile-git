<ul id="opportunity_contacts_list" class="related_list {$opportunity_related_contact_type}">
	{foreach item=contact from=$related_contacts}
	<li>
		<a class="action delete" href="/opportunities/unrelate_contact/{$Opportunity->id}?{$contact.type}_id={$contact.id}">Delete</a>
		<a class="view_link sprite sprite-{if $contact.type eq 'person'}person{else}organisation{/if}" href="/{if $contact.type eq 'person'}people{else}organisations{/if}/view/{$contact.id}">{$contact.name}</a>{if $contact.relationship neq ''} - {$contact.relationship}{/if}
	</li>
	{foreachelse}
	<li class="none_yet">Want to link more People/Organisations to this Opportunity? Add them now.</li>
	{/foreach}
</ul>