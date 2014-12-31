{if $current_user->isBeta()}
{if $activity_tracks}
<form id="activityTrackForm" action="/{$_area}/add_activity_track/{$attached_id}" method="get" class="saveform">
	<div class="row">
		<label for="select_track_id">Activity Track</label>
		<select id="select_track_id" name="track_id">
			{foreach from=$activity_tracks item=track key=id}
			<option value="{$id}">{$track|escape}</option>
			{/foreach}
		</select>
	</div>
	<div class="row">
		<label class="inline_checkbox"><input type="checkbox" class="checkbox" name="auto" /> Use defaults?</label>
		<input type="submit" class="submit" value="Add Activity Track" />
	</div>
</form>
{elseif $current_user->isAdmin()}
<p id="noActivityTracks">Set up <a href="/tracks">Activity Tracks</a> to add many at once.</p>
{/if}
{/if}

<div id="activities">
{capture name=overdue}
{foreach name=overdue_activities item=activity from=$overdue_activities}
	<li>
		<input type="checkbox" class="checkbox" title="Check to mark as completed" />
		<div class="activity_summary">
			<a class="view_link completable round-all" href="/activities/view/{$activity->id}" title="{$activity->name}">{$activity->name|truncate:35:'...':false}</a>{if $activity->type neq ""} <span class="normal">({$activity->type})</span>{/if}
			<span class="edit"><a class="action" href="/activities/edit/?id={$activity->id}">Edit</a></span>
			<dl>
				{if $activity->isEvent()}
				<dt>Began</dt>
				<dd>{$activity->date_string()}</dd>
				<br />
				{if $activity->isHappeningNow()}
				<dt>Ends</dt>
				{else}
				<dt>Ended</dt>
				{/if}
				<dd>{$activity->end_date_string()}</dd>
				{else}
				<dt>Was Due</dt>
				<dd>{$activity->date_string()}</dd>
				{/if}
			</dl>
		</div>
		<div class="clear"></div>
	</li>
{/foreach}
{/capture}
{if $smarty.capture.overdue|trim neq ''}
<div class="overdue activities">
	<ul class="related_list overdue">
		{$smarty.capture.overdue}
	</ul>
</div>
{/if}


{capture name=today}
{foreach name=due_activities item=activity from=$due_activities}
	<li>
		<input type="checkbox" class="checkbox" title="Check to mark as completed" />
		<div class="activity_summary">
			<a class="view_link completable round-all" href="/activities/view/{$activity->id}" title="{$activity->name}">{$activity->name|truncate:35:'...':false}</a>{if $activity->type neq ""} <span class="normal">({$activity->type})</span>{/if}
			<span class="edit"><a class="action" href="/activities/edit/?id={$activity->id}">Edit</a></span>
			<dl>
				{if $activity->isEvent()}
				<dt>Begins</dt>
				{else}
				<dt>Due</dt>
				{/if}
				<dd>{$activity->date_string()}</dd>
				{if $activity->isEvent()}
				<br />
				{if $activity->isHappeningNow()}
				<dt>Ends</dt>
				{else}
				<dt>Ended</dt>
				{/if}
				<dd>{$activity->end_date_string()}</dd>
				{/if}
			</dl>
		</div>
		<div class="clear"></div>
	</li>		
{/foreach}
{/capture}
{if $smarty.capture.today|trim neq ''}
<div class="today activities">
	<ul class="related_list today">
		{$smarty.capture.today}
	</ul>
</div>
{/if}


{capture name=upcoming}
{foreach name=other_activities item=activity from=$upcoming_activities}
	<li>
		<input type="checkbox" class="checkbox" title="Check to mark as completed" />
		<div class="activity_summary">
			<a class="view_link completable round-all" href="/activities/view/{$activity->id}" title="{$activity->name}">{$activity->name|truncate:35:'...':false}</a>{if $activity->type neq ""} <span class="normal">({$activity->type})</span>{/if}
			<span class="edit"><a class="action" href="/activities/edit/?id={$activity->id}">Edit</a></span>
			<dl>
				{if $activity->isEvent()}
				<dt>Begins</dt>
				{else}
				<dt>Due</dt>
				{/if}
				<dd>{$activity->date_string()}</dd>
				{if $activity->isEvent()}
				<br />
				<dt>Ends</dt>
				<dd>{$activity->end_date_string()}</dd>
				{/if}
			</dl>
		</div>
		<div class="clear"></div>
	</li>
{/foreach}
{/capture}
{if $smarty.capture.upcoming|trim neq ''}
<div class="upcoming activities">
	<h4>What's Next?</h4>
	<ul class="related_list">
		{$smarty.capture.upcoming}
	</ul>
</div>
{/if}


{capture name=later}
{foreach name=later_activities item=activity from=$later_activities}
	<li>
		<input type="checkbox" class="checkbox" title="Check to mark as completed" />
		<div class="activity_summary">
			<a class="view_link completable round-all" href="/activities/view/{$activity->id}" title="{$activity->name}">{$activity->name|truncate:35:'...':false}</a>{if $activity->type neq ""} <span class="normal">({$activity->type})</span>{/if}
			<span class="edit"><a class="action" href="/activities/edit/?id={$activity->id}">Edit</a></span>
		</div>
		<div class="clear"></div>
	</li>
{/foreach}
{/capture}
{if $smarty.capture.later|trim neq ''}
<div class="later activities">
	<h4>Marked as Later</h4>
	<ul class="related_list">
		{$smarty.capture.later}
	</ul>
</div>
{/if}
{if $smarty.capture.overdue|trim eq '' && $smarty.capture.today|trim eq '' && $smarty.capture.upcoming|trim eq '' && $smarty.capture.later|trim eq ''}
	<p class="empty">None at present - check for <a href="/activities/by_{$attached_to|strtolower}/?id={$attached_id}">completed ones</a>.</p>
{else}
<div class="show_all"><a class="action" href="/activities/by_{$attached_to|strtolower}/?id={$attached_id}">Show All Activities</a></div>
{/if}
</div>
