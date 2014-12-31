{assign var=count value=0}
{assign var=total value=0}
{assign var=on_page value=$activity_timeline|count}
{assign var=segments value=$activity_timeline->countTimelineSegments()}
{assign var=current_segment value=0}

<div class="timeline {if $timeline_view == 'list'}list {/if}{$for}">
	{if $add_url}
	<div class="info">
		<div id="timeline_helper">
			<div class="round-all">
				{if $num_pages > 1}{include file="elements/timeline_paging.tpl"}{/if}
				{if $restriction eq 'custom'}
				<a id="timeline_custom_edit" href="/preferences/dashboard/" class="round-all sprite sprite-edit">Customise</a>
				{/if}
				<a title="Switch to Block View" class="view block{if $timeline_view != 'list'} selected{/if} round-all sprite sprite-timeline">Block</a>
				<a title="Switch to List View" class="view list{if $timeline_view == 'list'} selected{/if} round-all sprite sprite-list">List</a>
				{if $timeline_rss}
				<a title="Subscribe to this Activity feed" class="sprite sprite-rss" href="{$timeline_rss}">Subscribe</a>
				{/if}
			</div>
		</div>
		<p><a id="new_{$for}_note" class="action sprite sprite-add" href="{$add_url}">Add a new note</a>. Notes you have added are <span class="highlight">highlighted</span>, you can click these to edit them.</p>
	</div>
	{/if}
	<ul>
		{foreach name=activity_timeline from=$activity_timeline item=item}
		{assign var=count value=$count+1}
		{assign var=total value=$total+1}
		
		{if !$add_url && $prev_date neq $item->getTimelineDate()}
		{assign var=count value=0}
		{assign var=current_segment value=$current_segment+1}
		{if $on_page < $activity_timeline->per_page || $prev_date == 'Over a Week Ago' || $segments < 2 || $current_segment < $segments}
		<li class="when{if $total == 1} first{/if}"><h3>{$item->getTimelineDate()}</h3></li>
		{/if}
		{assign var=prev_date value=$item->getTimelineDate()}
 		{/if}
 		
 		{if $add_url || $on_page < $activity_timeline->per_page || $prev_date == 'Over a Week Ago' || $segments < 2 || $current_segment < $segments}
		<li class="item {if $count % 2 == 0}even{else}odd{/if}" id="tl_{$item->type}_{$item->id}">
			{if $item->getTimelineType() eq 'Email'}
			{include file="elements/timeline/email.tpl" email=$item}

			{elseif $item->getTimelineType() eq 'New Activity' || $item->getTimelineType() eq 'Completed Activity' || $item->getTimelineType() eq 'Overdue Activity'}
			{include file="elements/timeline/activity.tpl" activity=$item}

			{elseif $item->getTimelineType() eq 'Note'}
			{include file="elements/timeline/note.tpl" note=$item}
			
			{elseif $item->getTimelineType() eq 'File'}
			{include file="elements/timeline/s3_file.tpl" s3_file=$item}
			
			{elseif $item->getTimelineType() eq 'Opportunity'}
			{include file="elements/timeline/opportunity.tpl" opportunity=$item}
			
			{elseif $item->getTimelineType() eq 'Flag'}
			{include file="elements/timeline/flag.tpl" flag=$item}
			
			{else}
			{$item->getTimelineType()}
			
			{/if}
		</li>
		{/if}
		
		{foreachelse}
		{if $add_url}
		<li class="empty">
			<p>No activity to display. <a id="new_{$for}_note" class="action sprite sprite-add" href="{$add_url}">Add a note</a>?</p>
		</li>
		{/if}
		{/foreach}
	</ul>
</div>