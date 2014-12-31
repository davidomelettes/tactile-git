<div id="right_bar">
	{include file="elements/usage_warning.tpl"}
	{include file="elements/dash_dropbox.tpl"}
	{include file="elements/recently_viewed.tpl"}
</div>

<div id="the_page">
	{if $_motd}
	<div id="motd">
		<div class="inner">
			<span id="motd_dismiss"><a title="Dismiss this message" class="sprite sprite-dismiss" href="/magic/dismiss_motd/?id={$_motd_id}"> </a></span>
			<p>{$_motd}</p>
		</div>
	</div>
	{/if}
	<div id="dashboard_report">
		<div class="column wide">
			<div class="item" id="graph_stats">
				<h3>
					<a href="{$graph_url}" class="context">Graph</a>
					<div class="detail">
						<div class="bubble">
							<ul>
								<li><a class="record" href="/graphs/pipeline">My Pipeline</a></li>
								<li><a class="record" href="/graphs/sales_history">Sales History</a></li>
								<li><a class="record" href="/graphs/opps_by_source_qty">Opportunities by Source (Qty)</a></li>
								<li><a class="record" href="/graphs/opps_by_source_cost">Opportunities by Source (Cost)</a></li>
								<li><a class="record" href="/graphs/opps_by_type_qty">Opportunities by Type (Qty)</a></li>
								<li><a class="record" href="/graphs/opps_by_type_cost">Opportunities by Type (Cost)</a></li>
								<li class="last"><a class="record" href="/graphs/opps_by_status_qty">Opportunities by Status (Qty)</a></li>
							</ul>
						</div>
					</div>
				</h3>
				<div id="dashboard_graph">
				</div>
			</div>
		</div>
		<div class="column">
			<div class="item">
				<h3><a href="/activities/mine/">Your Activities</a></h3>
				<ul class="stats" id="activity_stats">
					<li class="overdue{if $overdue_activities < 1} none{/if}">
						<a href="/activities/my_overdue" class="context"><span class="num">{$overdue_activities}</span>
						Overdue</a>
					</li>
					<li class="today{if $todays_activities < 1} none{/if}">
						<a href="/activities/my_today" class="context"><span class="num">{$todays_activities}</span>
						Today</a>
					</li>
					<li class="later{if $later_activities < 1} none{/if}">
						<a href="/activities/my_later" class="context"><span class="num">{$later_activities}</span>
						Later</a>
					</li>
				</ul>
			</div>
			<div class="item">
				<h3><a href="/opportunities/mine">Your Opportunities</a></h3>
				<ul class="stats" id="opportunity_stats">
					<li class="open">
						<a href="/opportunities/mine_open" class="context"><span class="num">{$open_opportunities}</span>
						Open</a>
					</li>
					<li class="won">
						<a href="/opportunities/mine_recently_won" class="context"><span class="num">{$won_opportunities}</span>
						Won</a>
					</li>
					<li class="lost">
						<a href="/opportunities/mine_recently_lost" class="context"><span class="num">{$lost_opportunities}</span>
						Lost</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div id="page_title">
		<div id="timeline_helper">
			<div class="round-all">
				{if $restriction eq 'custom'}
				<a id="timeline_custom_edit" href="/preferences/dashboard/" class="round-all sprite sprite-edit">Customise</a>
				{/if}
				<a title="Switch to Block View" class="view block{if $timeline_view != 'list'} selected{/if} round-all sprite sprite-timeline">Block</a>
				<a title="Switch to List View" class="view list{if $timeline_view == 'list'} selected{/if} round-all sprite sprite-list">List</a>
				{if $timeline_rss}
				<a id="timeline_rss" title="Subscribe to this Activity feed" class="sprite sprite-rss" href="{$timeline_rss}">Subscribe</a>
				{/if}
			</div>
		</div>
		<h2>
			{if $restriction eq 'notes_emails'}
			Recent Notes &amp; Emails
			{elseif $restriction eq 'notes_emails_acts'}
			Recent Notes, Emails, &amp; Completed Activities
			{elseif $restriction eq 'custom'}
			Recent Activity (Custom)
			{/if}
		</h2>
	</div>
	<div id="page_main">
		<div class="content_holder">
			{if $activity_timeline|@count > 0}
			{include file="elements/activity_timeline.tpl"}
			{else}
			<div id="no_timeline" class="form_help">
				{if $restriction eq 'notes_emails'}
				<p>You haven't added any <strong>Notes</strong> or <strong>Emails</strong> yet, or there are none less than <strong>30</strong> days old.</p>
				{elseif $restriction eq 'notes_emails_acts'}
				<p>You haven't added any <strong>Notes</strong> or <strong>Emails</strong>, or completed any <strong>Activities</strong> yet, or there are none less than <strong>30</strong> days old.</p>
				{elseif $restriction eq 'custom'}
				<p>Nothing to display. You might want to try the '<a href="/?view=notes_emails_acts">Notes, Emails, &amp; Completed Activities</a>' view instead, or visit your <a href="/preferences/dashboard/">preferences page</a> to add items to your Dashboard's custom view.</p>
				{/if}
				<p>You can add notes to <a href="/organisations/">Organisations</a>, <a href="/people/">People</a>,
				<a href="/opportunities/">Opportunities</a> and <a href="/activities/">Activities</a> when you're looking at their record page.</p>
				{if $current_user->getDropboxAddress()}
				<p>You can bcc or forward emails to <a href="mailto:{$current_user->getDropboxAddress()}">{$current_user->getDropboxAddress()}</a>
				and Tactile will work out which of your existing contacts to attach them to.</p>
				{else}
				<p>Once you've set up a dropbox address in your user <a href="/preferences">preferences</a>, you can bcc or forward emails to Tactile,
				which will work out which of your existing contacts to attach them to.</p>
				{/if}
			</div>
			{/if}
		</div>
	</div>
</div>

