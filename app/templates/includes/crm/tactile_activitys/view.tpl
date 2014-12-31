<script type="text/javascript">
	Tactile.person_id = {$Activity->person_id|default:'null'};
	Tactile.person = {$Activity->person|json_encode};
	Tactile.organisation_id = {$Activity->organisation_id|default:'null'};
	Tactile.organisation = {$Activity->organisation|json_encode};
	Tactile.opportunity_id = {$Activity->opportunity_id|default:'null'};
	Tactile.opportunity = {$Activity->opportunity|json_encode};
	Tactile.activity_id = {$Activity->id};
	Tactile.activity = {$Activity->getFormatted('name')|json_encode};
	Tactile.id = {$Activity->id};
</script>
<div id="right_bar">
	{if $current_user->canEdit($Activity)}
	{foldable key="actions" title="Actions"}
	<ul class="sidebar_options">
		{if $Activity->completed eq ''}
		<li><a href="/activities/complete/{$Activity->id}" class="sprite sprite-complete">Mark as Completed</a></li>
		{else}
		<li><a href="/activities/uncomplete/{$Activity->id}" class="sprite sprite-uncomplete">Mark as Uncompleted</a></li>
		{/if}
		<li><a href="/activities/icalendar/{$Activity->id}" class="sprite sprite-download">Download activity as an iCal File</a></li>
	</ul>
	{/foldable}
	{/if}
	{include file="elements/contact_methods.tpl" for="activity"}
	{lazy_foldable key="related_files" view_url="/activities/files/?id=`$Activity->id`" add_url="/activities/new_file/?id=`$Activity->id`" title="Files"}
	{include file="elements/recently_viewed.tpl"}
</div>
<div id="the_page">
	<div id="activity_view" class="view_holder inplace_container">
		<div id="page_title">
			<h2>
				<img id="heading_logo" class="default" src="/graphics/tactile/items/activities.png" alt="" />
				{$Activity->getFormatted('name')}
			</h2>
			{include file="elements/edit_delete.tpl" url="activities" for="activity" model=$Activity text="Activity"}
		</div>
		{show_tags model=$Activity type="activities"}
		<div class="view_nav round-all">
			<h3 id="show_summary_info"{if $view_summary_info} class="selected"{/if}>Activity Info</h3>
			<h3 id="show_recent_activity"{if $view_recent_activity} class="selected"{/if}>Recent Activity &amp; Notes</h3>
		</div>
		<div class="content_holder" id="summary_info"{if !$view_summary_info} style="display: none;"{/if}>
			<div class="content">
				{include file="elements/summary.tpl model=$Activity}
			</div>
		</div>
		<div class="content_holder" id="recent_activity"{if !$view_recent_activity} style="display: none;"{/if}>
			{include file="elements/activity_timeline.tpl" for="activity" add_url="/activities/save_note/?activity_id=`$Activity->id`"}
		</div>
		<div class="content_holder" id="view_nothing_selected"{if $view_summary_info || $view_recent_activity} style="display: none;"{/if}>
			<div class="form_help">
				<p>Expecting something? <span class="sprite sprite-upwards">Use the buttons above</span> to display more.</p>
			</div>
		</div>
	</div>
</div>
