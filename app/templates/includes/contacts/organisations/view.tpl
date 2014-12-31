<script type="text/javascript">
	Tactile.organisation_id = {$Organisation->id};
	Tactile.organisation = {$Organisation->getFormatted('name')|json_encode};
	Tactile.id = {$Organisation->id};
	{if $contact_methods}
	Tactile.contact_methods = [{foreach from=$contact_methods item=cm name=cms}{$cm->asJson()}{if !$smarty.foreach.cms.last},{/if}{/foreach}]
	{/if}
	{if $addresses}
	Tactile.addresses = [{foreach from=$addresses item=address name=address}{$address->asJSON()}{if !$smarty.foreach.address.last},{/if}{/foreach}]
	{/if}
</script>
<div id="right_bar">
	{include file="elements/contact_methods.tpl" for="organisation"}
	{include file="elements/addresses.tpl" for="organisation"}
	{lazy_foldable key="related_people" view_url="/organisations/people/?id=`$Organisation->id`" add_url="/organisations/new_person/?id=`$Organisation->id`" title="People"}
	{lazy_foldable key="related_opportunities" view_url="/organisations/opportunities/?id=`$Organisation->id`" add_url="/organisations/new_opportunity/?id=`$Organisation->id`" title="Opportunities"}
	{if $activity_tracks}
	{lazy_foldable key="related_activities" view_url="/organisations/activities/?id=`$Organisation->id`" add_url="/organisations/new_activity/?id=`$Organisation->id`" title="Current Activities" add_html='<a id="showActivityTrackForm" style="display:none;">Add Track</a>'}
	{else}
	{lazy_foldable key="related_activities" view_url="/organisations/activities/?id=`$Organisation->id`" add_url="/organisations/new_activity/?id=`$Organisation->id`" title="Current Activities"}
	{/if}
	{lazy_foldable key="related_files" view_url="/organisations/files/?id=`$Organisation->id`" add_url="/organisations/new_file/?id=`$Organisation->id`" title="Files"}
	{if $show_invoices}
		{if $upsell}
			{foldable title="Invoices"}
				<p>Invoices are a 3rd Party Integration and only available on paid plans. <strong>You can <a href="/account/change_plan/">upgrade now</a> to re-enable them.</strong></p>
			{/foldable}
		{else}
			{lazy_foldable key="freshbooks_`$Organisation->id`" view_url="/organisations/freshbooks/?id=`$Organisation->id`" title="FreshBooks"}
		{/if}
	{/if}
	{if $show_resolve}
	{lazy_foldable key="organisation_resolve_tickets" view_url="/organisations/resolve_tickets/?id=`$Organisation->id`" title="Resolve Cases"}
	{/if}
	{if $show_zendesk}
		{if $upsell}
			{foldable title="Zendesk Tickets"}
				<p>Support Tickets are a 3rd Party Integration and only available on paid plans. <strong>You can <a href="/account/change_plan/">upgrade now</a> to re-enable them.</strong></p>
			{/foldable}
		{else}
			{lazy_foldable key="organisation_zendesk_tickets" view_url="/organisations/zendesk_tickets/?id=`$Organisation->id`" title="Zendesk Tickets"}
		{/if}
	{/if}
	{include file="elements/recently_viewed.tpl"}
</div>
<div id="the_page">
	<div id="organisation_view" class="view_holder inplace_container">
		<div id="page_title">
			<h2>
				{if $logo_url}
				<img id="heading_logo" class="custom{if $current_user->canEdit($Organisation)} can_edit{/if}" src="{$logo_url}" alt="" />
				{else}
				<img id="heading_logo" class="default{if $current_user->canEdit($Organisation)} can_edit{/if}" src="/graphics/tactile/items/organisations.png" alt="" />
				{/if}
				{$Organisation->name|escape}
			</h2>
			{include file="elements/edit_delete.tpl" url="organisations" for="organisation" model=$Organisation text="Organisation"}
		</div>
		{show_tags model=$Organisation type="organisations"}
		<div class="view_nav round-all">
			<h3 id="show_summary_stats"{if $view_summary_stats} class="selected"{/if}>Summary</h3>
			<h3 id="show_summary_info"{if $view_summary_info} class="selected"{/if}>Organisation Info</h3>
			<h3 id="show_recent_activity"{if $view_recent_activity} class="selected"{/if}>Recent Activity &amp; Notes</h3>
		</div>
		
		<div class="content_holder" id="summary_stats"{if !$view_summary_stats} style="display: none;"{/if}>
			<div class="stats">
				<div class="stat{if !$last_contact.by} alert{/if}" id="last_contacted">
					<h4>Last Contacted</h4>
					{if $Organisation->getFormatted('last_contacted')}
					<p class="value" title="{'Y-m-d H:i:s'|date_format:$Organisation->last_contacted}">{$Organisation->getFormatted('last_contacted')}</p>
					<p class="subtitle">by {$Organisation->getFormatted('last_contacted_by_user')}
					{else}
					<p class="value">Never</p>
					<p class="subtitle">Why not make a call?
					{/if}
					(<a href="/organisations/update_last_contacted/{$Organisation->id}/">Click to update</a>)</p>
				</div>
			</div>
			<div class="stats">
				<div class="stat" id="pipeline">
					<h4>Pipeline</h4>
					<p class="value">{$pipeline.weighted|number_format}</p>
					{if $pipeline.weighted eq 0 && $pipeline.total eq 0}
					<p class="subtitle"><a class="opportunity_adder">Add an Opportunity?</a></p>
					{else}
					<p class="subtitle">on {$pipeline.total|number_format} total</p>
					{/if}
				</div>
			</div>
			<div class="stats">
				<div class="stat" id="opportunities">
					<h4>Opportunity History</h4>
					{if $winrate eq 0}
					<p class="value">N/A</p>
					<p class="subtitle">none 'won' yet</p>
					{else}
					<p class="value">{$winrate}% Won</p>
					<p class="subtitle">avg. {$closetime} to win</p>
					{/if}
				</div>
			</div>
		</div>
		
		<div class="content_holder" id="summary_info"{if !$view_summary_info} style="display: none;"{/if}>
			<div class="content">
				{include file="elements/summary.tpl model=$Organisation}
			</div>
		</div>
		<div class="content_holder" id="recent_activity"{if !$view_recent_activity} style="display: none;"{/if}>
			{include file="elements/activity_timeline.tpl" for="organisation" add_url="/organisations/save_note/?organisation_id=`$Organisation->id`"}
		</div>
		<div class="content_holder" id="view_nothing_selected"{if $view_summary_stats || $view_summary_info || $view_recent_activity} style="display: none;"{/if}>
			<div class="form_help">
				<p>Expecting something? <span class="sprite sprite-upwards">Use the buttons above</span> to display more.</p>
			</div>
		</div>
	</div>
</div>
