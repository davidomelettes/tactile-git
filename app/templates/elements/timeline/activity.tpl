<div class="{if $activity->completed}completed_{elseif $activity->getTimelineType() eq 'Overdue Activity'}overdue_{/if}activity{if $activity->owner eq $current_user->getRawUsername()} mine{/if}{if !$activity->organisation_id && !$activity->person_id && !$activity->opportunity_id} nothing_attached{/if}">
	<div class="type round-left">{if $activity->type eq 'Event'}Event{else}Todo{/if}</div>
	<div class="hbf">
		{if $activity->organisation_id || $activity->person_id || $activity->opportunity_id}
		<div class="attached">
			{include file="elements/timeline/attached_things.tpl" parent=$activity}
		</div>
		{/if}
		<div class="header">
			<h4 title="{$activity->getTimelineSubject()}"><a href="{$activity->getTimelineURL()}">{$activity->getTimelineSubject()|truncate:60}</a></h4>
		</div>
		<div class="body">
			{if $activity->getTimelineBody()}
			<p>{$activity->getTimelineBody()}</p>
			{/if}
		</div>
		<div class="footer">
			<div class="assigned_time">
				{$activity->getTimelineWhenString()}
			</div>
		</div>
	</div>
</div>