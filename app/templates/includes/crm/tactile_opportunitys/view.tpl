<script type="text/javascript">
	Tactile.person_id = {$Opportunity->person_id|default:'null'};
	Tactile.person = {$Opportunity->person|json_encode};
	Tactile.organisation_id = {$Opportunity->organisation_id|default:'null'};
	Tactile.organisation = {$Organisation->getFormatted('name')|json_encode};
	Tactile.opportunity_id = {$Opportunity->id};
	Tactile.opportunity = {$Opportunity->getFormatted('name')|json_encode};
	Tactile.id = {$Opportunity->id};
</script>
<div id="right_bar">
	{foldable key="dropbox_info" title="Opportunity Dropbox"}
		{assign var='dropbox' value='opp+'|cat:$Opportunity->id}
        <p>This Opportunity has its <strong><a class="sprite sprite-dropbox" href="mailto:{$current_user->getDropboxAddress($dropbox)|urlencode}">own dropbox</a></strong>.</p>
		<p><a href="mailto:{$current_user->getDropboxAddress($dropbox)|urlencode}">Email sent to it</a> will automatically appear in the Recent Activity section of this page.</p>
        {/foldable}
	{if $Opportunity->is_archived()}
		{lazy_foldable key="opportunity_contacts" view_url="/opportunities/opportunity_contacts/?id=`$Opportunity->id`" title="Additional Contacts"}
		{include file="elements/contact_methods.tpl" for="opportunity"}
		{lazy_foldable key="related_activities" view_url="/opportunities/activities/?id=`$Opportunity->id`" title="Current Activities"}
		{lazy_foldable key="related_files" view_url="/opportunities/files/?id=`$Opportunity->id`" title="Files"}
	{else}
		{lazy_foldable key="opportunity_contacts" view_url="/opportunities/opportunity_contacts/?id=`$Opportunity->id`" add_url="/opportunities/new_opportunity_contact/?id=`$Opportunity->id`" title="Additional Contacts"}
		{include file="elements/contact_methods.tpl" for="opportunity"}
		{if $activity_tracks}
		{lazy_foldable key="related_activities" view_url="/opportunities/activities/?id=`$Opportunity->id`" add_url="/opportunities/new_activity/?id=`$Opportunity->id`" title="Current Activities" add_html='<a id="showActivityTrackForm" style="display:none;">Add Track</a>'}
		{else}
		{lazy_foldable key="related_activities" view_url="/opportunities/activities/?id=`$Opportunity->id`" add_url="/opportunities/new_activity/?id=`$Opportunity->id`" title="Current Activities"}
		{/if}
		{lazy_foldable key="related_files" view_url="/opportunities/files/?id=`$Opportunity->id`" add_url="/opportunities/new_file/?id=`$Opportunity->id`" title="Files"}
	{/if}
	{include file="elements/recently_viewed.tpl"}
</div>
<div id="the_page">
	<div id="opportunity_view" class="view_holder">
		<div id="page_title">
			<h2>
				<img id="heading_logo" class="default" src="/graphics/tactile/items/opportunities.png" alt="" />
				{$Opportunity->getFormatted('name')}
			</h2>
			{include file="elements/edit_delete.tpl" url="opportunities" for="opportunity" model=$Opportunity text="Opportunity" edit_locked=$Opportunity->is_archived()}
		</div>
		{show_tags model=$Opportunity type="opportunities" edit_locked=$Opportunity->is_archived()}
		<div class="view_nav round-all">
			<h3 id="show_summary_stats"{if $view_summary_stats} class="selected"{/if}>Summary</h3>
			<h3 id="show_summary_info"{if $view_summary_info} class="selected"{/if}>Opportunity Info</h3>
			<h3 id="show_recent_activity"{if $view_recent_activity} class="selected"{/if}>Recent Activity &amp; Notes</h3>
		</div>
		
		{if $Opportunity->is_archived()}
		<div class="content_holder">
			<div class="form_help">
				<form action="/opportunities/save" method="post">
					<p class="inline_input">
						<input type="hidden" name="Opportunity[id]" value="{$Opportunity->id}" />
						<strong>This Opportunity is archived</strong>.
						
						{if $within_opp_limit}
						<input type="hidden" name="Opportunity[_checkbox_exists_archived]" value="true" />
						You can <input type="submit" class="submit" value="Unarchive" /> it to edit it again.
						{else}
						</p><p>You have reached the limit of Opportunities allowed by your current plan.
						{if $current_user->isAdmin()}
						To unarchive this Opportunity you can <a class="action" href="/account/change_plan/">Upgrade your account</a> to increase your Opportunity Limit
						{else}
						Your Account Owner can upgrade your account to allow more Opportunities
						{/if}
						or you can Archive some others.
						{/if}
					</p>
					<p>Whilst archived it does not count towards your Opportunity Limit, but it cannot be edited, and the summary is not displayed.</p>
				</form>
			</div>
		</div>
		{/if}
		
		{if !$Opportunity->is_archived()}
		<div class="content_holder" id="summary_stats"{if !$view_summary_stats} style="display: none;"{/if}>
			<div class="stats">
				<div class="stat{if $current_user->canEdit($Opportunity)} can_edit{/if}{if $won eq 'f'} warning{else} won{/if}" id="pipeline">
					<h4>Pipeline Stage</h4>
					<p class="value" id="pipeline_{$Opportunity->status_id}">{$Opportunity->status}</p>
					<p class="subtitle"><a>Click to change</a></p>
				</div>
			</div>
			<div class="stats">
				<div class="stat" id="value">
					<h4>Value</h4>
					<p class="value">{$Opportunity->cost|number_format}</p>
					<p class="subtitle">{$Opportunity->probability}% Probability</p>
				</div>
			</div>
			<div class="stats">
				<div class="stat" id="close">
					{if $won eq 'f'}
					<h4>Expected Close</h4>
					<p class="value">{$Opportunity->getFormatted('enddate')}</p>
					<p class="subtitle">Age: {$age}</p>
					{else}
					<h4>Won Date</h4>
					<p class="value">{$Opportunity->getFormatted('enddate')}</p>
					<p class="subtitle">Time to Win: {$age}</p>
					{/if}
				</div>
			</div>
		</div>
		{/if}
		
		<div class="content_holder" id="summary_info"{if !$view_summary_info} style="display: none;"{/if}>
			<div class="content">
				{include file="elements/summary.tpl model=$Opportunity}
			</div>
		</div>
		<div class="content_holder" id="recent_activity"{if !$view_recent_activity} style="display: none;"{/if}>
			{if $Opportunity->is_archived()}
			{include file="elements/activity_timeline.tpl" for="opportunity"}
			{else}
			{include file="elements/activity_timeline.tpl" for="opportunity" add_url="/opportunities/save_note/?opportunity_id=`$Opportunity->id`"}
			{/if}
		</div>
		<div class="content_holder" id="view_nothing_selected"{if $view_summary_stats || $view_summary_info || $view_recent_activity} style="display: none;"{/if}>
			<div class="form_help">
				<p>Expecting something? <span class="sprite sprite-upwards">Use the buttons above</span> to display more.</p>
			</div>
		</div>
	</div>
</div>
