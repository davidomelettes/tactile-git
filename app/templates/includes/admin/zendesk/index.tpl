<div id="right_bar">
	{foldable}
		<p><a href="/admin/">Back to Admin</a></p>
	{/foldable}
	{foldable key="zendesk_help" title="What is Zendesk?" extra_class="help"}
	<p>
		Zendesk is a Customer support / Help desk product. Its primary features include email ticket tracking, providing a customer self service portal, and general help desk reporting and tracking features.
	</p>
	<p class="t-center"><a href="https://www.zendesk.com/" target="_blank"><img src="/graphics/3rd_party/zendesk.png" alt="Zendesk Logo" /></a></p>
	<p>The video below takes you through how to set up and use Tactile CRM and Zendesk together.</p>
	<p class="video">
		<img src="/graphics/3rd_party/zendeskvideo.png" alt="ZenDesk Video" />
	</p>
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Setup Zendesk Integration</h2>
		</div>
		<form action="/zendesk/setup" method="post" class="saveform" id="zendesk_setup_form">
			<div class="content_holder">
				<fieldset>
					<h3>Zendesk Account Details</h3>
					<div class="form_help">
						<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
						<p>To integrate with Zendesk we'll need your <strong>Site Address</strong>, <strong>Email Address</strong> and <strong>Password</strong>.</p>
					</div>
					<div class="content">
						<div style="position: relative;">
							<div class="row">
								<label for="accountname">Site Address</label>
								{if $siteaddress}
								<span class="fb_account_name false_input" id="siteaddress">{$siteaddress|escape}</span>
								{else}
								<input class="required" type="text" id="siteaddress" name="siteaddress" /><span style="position: absolute; left: 405px; top: 5px; font-size: 1.2em;">.zendesk.com</span>
								{/if}
							</div>
						</div>
						<div class="row">
							<label for="email">Email Address</label>
							<input class="required" type="text" id="email" name="email" value="{$email}"/>
						</div>
						<div class="row">
							<label for="password">Password</label>
							<input class="required" type="password" id="password" name="password" value="{$password}"/>
						</div>
					</div>
				</fieldset>
				<fieldset class="prefs_save">
					<div class="content">
						<div class="row">
							<input class="submit" type="submit" value="{if $siteaddress}Update Details{else}Connect{/if}" />
						</div>
					</div>
				</fieldset>
			</div>
		</form>
		{if $siteaddress}
		<form action="/zendesk/reset" method="post" class="saveform delete_form" id="zendesk_reset_form">
			<div class="content_holder">
				<fieldset class="prefs_save">
					<h3>Unlink Zendesk Account</h3>
					<div class="content">
						<p>If you want to unlink Tactile from Zendesk, click this button.</p>
						<div class="row">
							<input class="submit" type="submit" value="Reset" />
						</div>
					</div>
				</fieldset>
			</div>
		</form>
		{/if}
	</div>
</div>
