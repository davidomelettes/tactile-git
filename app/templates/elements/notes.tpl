<div class="split">
	<h3>Notes</h3>
	{foreach name=notes item=note from=$notes}
		{if $smarty.foreach.notes.first}
			<p class="note_instructions">
				{if $add_url}
				Click to <a class="highlight" href="{$add_url}" id="new_{$for}_note">add a new 
				note</a>.
				{/if}
				Notes you have added are highlighted in a brighter yellow, you can click these ones 
				to edit them.
			</p>
			<ul class="note_list {$for}_notes" id="{$for}_notes">
		{/if}
		<li {if $note->owner eq $current_user->getRawUsername()}title="Click to edit"{/if} id="note_{$note->id}" class="a_note{if $note->owner eq $current_user->getRawUsername()} editable{/if}">
			<h4 class="note_title">{$note->getFormatted('title')}</h4>
			<p class="attached_to">Attached To:</p>
			<ul class="attached_things">
				{if $note->organisation_id}<li class="client">{$note->organisation}</li>{/if}
				{if $note->person_id}<li class="person">{$note->person}</li>{/if}
				{if $note->opportunity_id}<li class="opportunity">{$note->opportunity}</li>{/if}
				{if $note->activity_id}<li class="activity">{$note->activity}</li>{/if}
			</ul>
			{assign var=note_body value=$note->getFormatted('note')}
			{if $note_body|strlen > 300}
				<p class="the_note">
					{$note_body|truncate:200:'...':false}
					<a class="note_toggle" href="#">[Show More]</a>
				</p>
				<p class="the_note the_whole_note" style="display:none;">
					{$note_body}
					<a class="note_toggle" href="#">[Show Less]</a>
				</p>
			{else}
				<p class="the_note  the_whole_note">{$note_body}</p>
			{/if}
			
			<p class="date owner">Posted by {$note->getFormatted('owner')} {$note->getFormatted('created')}</p>
				{if $note->created neq $note->lastupdated}<p class="date editor">Last edited by {$note->getFormatted('alteredby')} {$note->getFormatted('lastupdated')}</p>{/if}
			{if $note->private eq 't'}<p class="date private_note">This note is private</p>{/if}
			{if $note->owner eq $current_user->getRawUsername()}
			<p class="delete_note"><a href="/notes/delete/{$note->id}" class="highlight">Delete</a></p>
			{/if}
		</li>
	{foreachelse}
		<p class="note_instructions">No notes to display{if $add_url} - <a id="new_{$for}_note" href="{$add_url}" class="highlight">add one</a>?{else}.{/if}
		</p>
		<ul class="note_list {$for}_notes" id="{$for}_notes">
	{/foreach}
	</ul>
	<div style="clear:both;height:0;">&nbsp;</div>
</div>
