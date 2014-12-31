{foldable key="activities" title="My Activities"}
	{capture name=overdue}		
	{foreach name=overdue_activities item=activity from=$overdue_activities}
		<li>
			<input type="checkbox" class="checkbox" title="Check to mark as completed" />
			<div class="activity_summary">
				<a class="view_link" href="/activities/view/{$activity->id}" title="{$activity->name}">{$activity->name|truncate:34:'...':false}</a>
				<dl class="slide" style="display: none;">
					{if $activity->isEvent()}
					<dt>Began</dt>
					<dd>{$activity->date_string()}</dd>
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
					{if $activity->type neq ""}
                        <dt>Type</dt>
                        <dd>{$activity->type}</dd>
                    {/if}
					{if $activity->person_id}
						<dt>{if $activity->isEvent()}With{else}For{/if}</dt>
						<dd><a href="/people/view/{$activity->person_id}">{$activity->person}</a></dd>
					{elseif $activity->organisation_id}
						<dt>{if $activity->isEvent()}With{else}For{/if}</dt>
						<dd><a href="/organisations/view/{$activity->organisation_id}">{$activity->organisation}</a></dd>
					{/if}
					{if $activity->description|trim neq ''}
					<dt>Description</dt>
					<dd>{$activity->getFormatted('description')}</dd>
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
				<a class="view_link" href="/activities/view/{$activity->id}" title="{$activity->name}">{$activity->name|truncate:34:'...':false}</a>
					{capture name=activity}
					{if $activity->isEvent() && ($activity->date_string()|trim neq 'Today')}
					<dt>Begins</dt>
					<dd>{$activity->date_string()}</dd>
					{if $activity->isHappeningNow()}
					<dt>Ends</dt>
					{else}
					<dt>Ended</dt>
					{/if}
					<dd>{$activity->end_date_string()}</dd>
					{/if}
					{if $activity->type neq ""}
                        <dt>Type</dt>
                        <dd>{$activity->type}</dd>
                    {/if}
					{if $activity->person_id}
						<dt>{if $activity->isEvent()}With{else}For{/if}</dt>
						<dd><a href="/people/view/{$activity->person_id}">{$activity->person}</a></dd>
					{elseif $activity->organisation_id}
						<dt>{if $activity->isEvent()}With{else}For{/if}</dt>
						<dd><a href="/organisations/view/{$activity->organisation_id}">{$activity->organisation}</a></dd>
					{/if}
					{if $activity->description|trim neq ''}
					<dt>Description</dt>
					<dd>{$activity->getFormatted('description')}</dd>
					{/if}
					{/capture}
				{if $smarty.capture.activity|trim neq ''}
				<dl{if ($activity->date_string()|trim eq 'Today') && (!$activity->organisation_id && !$activity->person_id) && ($activity->type eq "")} class="slide" style="display: none;"{/if}>
					{$smarty.capture.activity}
				</dl>
				{/if}
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
				<a class="view_link" href="/activities/view/{$activity->id}" title="{$activity->name}">{$activity->name|truncate:34:'...':false}</a>
				<dl{if !$activity->isEvent()} class="slide" style="display: none;"{/if}>
					{if $activity->isEvent()}
					<dt>Begins</dt>
					{else}
					<dt>Due</dt>
					{/if}
					<dd>{$activity->date_string()}</dd>
					{if $activity->isEvent()}
					<dt>Ends</dt>
					<dd>{$activity->end_date_string()}</dd>
					{/if}
					{if $activity->type neq ""}
                        <dt>Type</dt>
                        <dd>{$activity->type}</dd>
                    {/if}
					{if $activity->person_id}
						<dt>{if $activity->isEvent()}With{else}For{/if}</dt>
						<dd><a href="/people/view/{$activity->person_id}">{$activity->person}</a></dd>
					{elseif $activity->organisation_id}
						<dt>{if $activity->isEvent()}With{else}For{/if}</dt>
						<dd><a href="/organisations/view/{$activity->organisation_id}">{$activity->organisation}</a></dd>
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
				<a class="view_link" href="/activities/view/{$activity->id}" title="{$activity->name}">{$activity->name|truncate:34:'...':false}</a>
				{if !$activity->type || !$activity->person_id || !$activity->organisation_id || !$activity->type}
				<dl class="slide" style="display: none;">
					{if $activity->type neq ""}
	                    <dt>Type</dt>
	                    <dd>{$activity->type}</dd>
	                {/if}
					{if $activity->person_id}
						<dt>For</dt>
						<dd><a href="/people/view/{$activity->person_id}">{$activity->person}</a></dd>
					{elseif $activity->organisation_id}
						<dt>For</dt>
						<dd><a href="/organisations/view/{$activity->organisation_id}">{$activity->organisation}</a></dd>
					{/if}
				</dl>
				{/if}
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
		<p class="empty">No Activities. <a href="/activities/new">Add One?</a></p>
	{/if}
	
	{if $activities->num_pages > 1}
	<p>(Displaying {$activities->per_page}/{$activities->num_records})</p>
	{/if}
{/foldable}
