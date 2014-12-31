<script type="text/javascript">
	Tactile.person_id = {$Person->id};
	Tactile.person = {$Person->fullname|json_encode};
	Tactile.organisation_id = {$Person->organisation_id|default:'null'};
	Tactile.organisation = {$Person->getFormatted('organisation')|json_encode};
	Tactile.id = {$Person->id};
	{if $contact_methods}
	Tactile.contact_methods = [{foreach from=$contact_methods item=cm name=cms}{$cm->asJson()}{if !$smarty.foreach.cms.last},{/if}{/foreach}]
	{/if}
	{if $organisation_contact_methods}
	Tactile.organisation_contact_methods = [{foreach from=$organisation_contact_methods item=cm name=cms}{$cm->asJson()}{if !$smarty.foreach.cms.last},{/if}{/foreach}]
	{/if}
	{if $addresses}
	Tactile.addresses = [{foreach from=$addresses item=address name=address}{$address->asJSON()}{if !$smarty.foreach.address.last},{/if}{/foreach}]
	{/if}
	{if $organisation_addresses}
	Tactile.organisation_addresses = [{foreach from=$addresses item=address name=address}{$address->asJSON()}{if !$smarty.foreach.address.last},{/if}{/foreach}]
	{/if}
</script>
<div id="right_bar">
	{include file="elements/contact_methods.tpl" for="person"}
	{include file="elements/addresses.tpl" for="person"}
	{lazy_foldable key="related_opportunities" view_url="/people/opportunities/?id=`$Person->id`" add_url="/people/new_opportunity/?id=`$Person->id`" title="Opportunities"}
	{if $activity_tracks}
	{lazy_foldable key="related_activities" view_url="/people/activities/?id=`$Person->id`" add_url="/people/new_activity/?id=`$Person->id`" title="Current Activities" add_html='<a id="showActivityTrackForm" style="display:none;">Add Track</a>'}
	{else}
	{lazy_foldable key="related_activities" view_url="/people/activities/?id=`$Person->id`" add_url="/people/new_activity/?id=`$Person->id`" title="Current Activities"}
	{/if}
	{lazy_foldable key="related_files" view_url="/people/files/?id=`$Person->id`" add_url="/people/new_file/?id=`$Person->id`" title="Files"}
	{if $show_resolve}
	{lazy_foldable key="person_resolve_tickets" view_url="/people/resolve_tickets/?id=`$Person->id`" title="Resolve Cases"}
	{/if}
	{if $show_zendesk}
	{if $upsell}
			{foldable title="Support Tickets"}
				<p>Support Tickets are a 3rd Party Integration and only available on paid plans. <strong>You can <a href="/account/change_plan/">upgrade now</a> to re-enable them.</strong></p>
			{/foldable}
		{else}
			{lazy_foldable key="person_zendesk_tickets" view_url="/people/zendesk_tickets/?id=`$Person->id`" title="Zendesk Tickets"}
		{/if}
	{/if}
	{include file="elements/recently_viewed.tpl"}
</div>
<div id="the_page">
	<div id="person_view" class="view_holder inplace_container">
		<div id="page_title">
			<h2>
				{if $logo_url}
				<img id="heading_logo" class="custom{if $current_user->canEdit($Person)} can_edit{/if}" src="{$logo_url}" alt="" />
				{else}
				<img id="heading_logo" class="default{if $current_user->canEdit($Person)} can_edit{/if}" src="/graphics/tactile/items/people.png" alt="" />
				{/if}
				{$Person->fullname|escape}
			</h2>
			{include file="elements/edit_delete.tpl" url="people" for="person" model=$Person text="Person"}
		</div>
		{show_tags model=$Person type="people"}
		<div class="view_nav round-all">
			<h3 id="show_summary_stats"{if $view_summary_stats} class="selected"{/if}>Summary</h3>
			<h3 id="show_summary_info"{if $view_summary_info} class="selected"{/if}>Person Info</h3>
			<h3 id="show_recent_activity"{if $view_recent_activity} class="selected"{/if}>Recent Activity &amp; Notes</h3>
		</div>
		
		{if !$Person->isUser()}
		<div class="content_holder" id="summary_stats"{if !$view_summary_stats} style="display: none;"{/if}>
			<div class="stats">
				<div class="stat{if !$last_contact.by} alert{/if}" id="last_contacted">
					<h4>Last Contacted</h4>
					{if $Person->getFormatted('last_contacted')}
					<p class="value" title="{'Y-m-d H:i:s'|date_format:$Person->last_contacted}">{$Person->getFormatted('last_contacted')}</p>
					<p class="subtitle">by {$Person->getFormatted('last_contacted_by_user')}
					{else}
					<p class="value">Never</p>
					<p class="subtitle">Why not make a call?
					{/if}
					(<a href="/people/update_last_contacted/{$Person->id}/">Click to update</a>)</p>
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
		{/if}
		
		<div class="content_holder" id="summary_info"{if !$view_summary_info} style="display: none;"{/if}>
			{if $Person->isUser()}
			<div class="form_help">
				<p><strong>This Person is a User.</strong> You can edit but not delete them.
				{if $Person->getUser()->is_enabled()}
				{if $current_user->isAdmin()}
				Disable <strong><a href="/users/view/{$Person->getUser()->getFormatted('username')}">{$Person->getUser()->getFormatted('username')}</a></strong> to remove this Person.
				{else}
				They can only be removed by an admin.
				{/if}
				{/if}
				</p>
			</div>
			{/if}
			<div class="content">
				{include file="elements/summary.tpl model=$Person}
			</div>
		</div>
		<div class="content_holder" id="recent_activity"{if !$view_recent_activity} style="display: none;"{/if}>
			{include file="elements/activity_timeline.tpl" for="person" add_url="/people/save_note/?person_id=`$Person->id`"}
		</div>
		<div class="content_holder" id="view_nothing_selected"{if $view_summary_stats || $view_summary_info || $view_recent_activity} style="display: none;"{/if}>
			<div class="form_help">
				<p>Expecting something? <span class="sprite sprite-upwards">Use the buttons above</span> to display more.</p>
			</div>
		</div>
	</div>
</div>
