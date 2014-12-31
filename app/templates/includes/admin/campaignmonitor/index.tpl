<div id="right_bar">
	{foldable}
		<p><a href="/admin/">Back to Admin</a></p>
	{/foldable}
	{foldable key="campaignmonitor_help" title="What is Campaign Monitor?" extra_class="help"}
	<p>
		Campaign Monitor is an email marketing service that allows you to send beautiful email campaigns, track the results and manage your subscribers.
	</p>
	<p class="t-center"><a href="https://www.campaignmonitor.com/" target="_blank"><img src="/graphics/3rd_party/campaignmonitor.png" alt="Campaign Monitor Logo" /></a></p>
	<p>The video below takes you through how to set up and use Tactile CRM and Campaign Monitor together.</p>
	<p class="video">
		<img src="/graphics/3rd_party/campaignmonitorvideo.png" alt="Campaign Monitor Video" />
	</p>
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Setup Campaign Monitor Integration</h2>
		</div>
		{if !$cm_key}
		<form action="/campaignmonitor/setup" method="post" class="saveform" id="campaignmonitor_setup_form">
		{else}
		<form action="/campaignmonitor/unlink" method="post" class="saveform">
		{/if}
			<div class="content_holder">
				<fieldset>
					<h3>Campaign Monitor Account Details</h3>
					{if !$cm_key}
					<div class="form_help">
						<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
						<p>To integrate with Campaign Monitor we'll need your <strong>Account API Key</strong>, from your Account Settings page.</p>
					</div>
					<div class="content">
						<div class="row">
							<label for="cm_key">API Key</label>
							<input class="required" type="text" id="cm_key" name="cm_key" value="{$cm_key}"/>
						</div>
					</div>
					{else}
					<div class="form_help">
						<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
						<p>To unlink Campaign Monitor from Tactile, press Unlink below.</p>
					</div>
					<div class="content">
						<div class="row">
							<label>API Key</label>
							<span class="false_input">{$cm_key}</span>
						</div>
						<div class="row">
							<label>Client</label>
							<span class="false_input">{$cm_client}</span>
						</div>
					</div>
					{/if}
				</fieldset>
				<fieldset class="prefs_save">
					<div class="content">
						<div class="row">
							<input type="submit" class="submit" value="{if !$cm_key}Connect{else}Unlink{/if}" />
						</div>
					</div>
				</fieldset>
			</div>
		</form>
		
	</div>
</div>
