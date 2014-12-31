<div id="note_{$note->id}" class="note {if $note->owner eq $current_user->getRawUsername()}mine{if $_area neq ''}" title="Click to Edit{/if}{/if}">
	<div class="type round-left">Note</div>
	<div class="hbf">
		{if $note->organisation_id || $note->person_id || $note->opportunity_id || $note->activity_id}
		<div class="attached">
			{include file="elements/timeline/attached_things.tpl" parent=$note}
		</div>
		{/if}
		<div class="header">
			<h4 title="{$note->getTimelineSubject()|escape}">{$note->getTimelineSubject()|escape}</h4>
			{if $note->owner eq $current_user->getRawUsername()}
			<div class="actions">
				<ul>
					<li><a href="/notes/delete/{$note->id}" class="action delete">Delete</a></li>
				</ul>
			</div>
			{/if}
		</div>
		<div class="body">
			{assign var=note_body value=$note->getTimelineBody()}
			{if $note_body|strlen > 300}
				<p class="body_content">
					{$note_body|truncate:200:'...':false}<br />
					<a class="body_toggle action">[Show More]</a>
				</p>
				<p class="body_content full" style="display:none;">
					{$note_body}<br />
					<a class="body_toggle action">[Show Less]</a>
				</p>
			{else}
				<p class="body_content full">{$note_body}</p>
			{/if}
		</div>
		<div class="footer">
			<div class="owner_time">
				{$note->getTimelineWhenString()}
			</div>
			{if $note->private eq 't'}
			<div class="private">
				<span>This note is private</span>
			</div>
			{/if}
		</div>
	</div>
</div>