<div id="right_bar">
	{foldable key="preferences_overview_help" title="Preferences Help" extra_class="help"}
		<p>Use these preferences to control how Tactile looks and behaves for you.</p>
	{/foldable}
	{foldable key="toggle_welcome_message" title="Welcome Tab"}
		<form action="/preferences/toggle_welcome_message" method="post">
			<p style="overflow: hidden;">
			{if $display_welcome_message_box}		
				<span class="mini_preference_label">Tab is not currently displayed.</span>
				<input type="submit" class="submit" value="Display It" class="savebutton mini_preference_save" />
			{else}
				<span class="mini_preference_label">Tab is currently displayed.</span>
				<input type="submit" class="submit" value="Hide It" class="savebutton mini_preference_save" />
			{/if}
			</p>
			<div class="clear"></div>
		</form>
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
		<div id="pref_nav">
			<ul>
				<li{if $pref_view eq 'password'} class="on"{/if}><a href="/preferences/password/" id="pref_tab_password">Password</a></li>
				<li class="arrow {if $pref_view eq 'password'} on_left{elseif $pref_view eq 'dashboard'} on_right{/if}">&nbsp;</li>
				<li{if $pref_view eq 'dashboard'} class="on"{/if}><a href="/preferences/dashboard/" id="pref_tab_dashboard">Dashboard</a></li>
				<li class="arrow {if $pref_view eq 'dashboard'} on_left{elseif $pref_view eq 'email'} on_right{/if}">&nbsp;</li>
				<li{if $pref_view eq 'email'} class="on"{/if}><a href="/preferences/email/" id="pref_tab_email">Email</a></li>
				<li class="arrow {if $pref_view eq 'email'} on_left{elseif $pref_view eq 'date_time'} on_right{/if}">&nbsp;</li>
				<li{if $pref_view eq 'date_time'} class="on"{/if}><a href="/preferences/date_time/" id="pref_tab_date_time">Date &amp; Time</a></li>
				<li class="arrow {if $pref_view eq 'date_time'} on_left{elseif $pref_view eq 'keys'} on_right{/if}">&nbsp;</li>
				<li{if $pref_view eq 'keys'} class="on"{/if}><a href="/preferences/keys/" id="pref_tab_keys">External Access</a></li>
				<li class="arrow {if $pref_view eq 'keys'} on_left{/if}">&nbsp;</li>
			</ul>
		</div>
		
		<div id="pref_content">
			<div class="content_holder">
				<div id="pref_password" class="show"{if $pref_view neq 'password'} style="display: none;"{/if}>
					<form action="/preferences/change_password/" method="post" class="saveform">
						<fieldset>
							<div class="content">
								<div class="row">
									<label for="current_password">Current Password</label>
									<input type="password" name="current_password" id="current_password" class="required"/>
								</div>
								<div class="row">
									<label for="new_password">New Password</label>
									<input type="password" name="new_password" id="new_password" class="required" />
								</div>
								<div class="row">
									<label for="new_password_again">New Password Again</label>
									<input type="password" name="new_password_again" id="new_password_again" class="required" />
								</div>
							</div>
						</fieldset>
						<fieldset class="prefs_save">
							<div class="content">
								<div class="row">
									<input type="submit" value="Change Password" class="submit"/>
								</div>
							</div>
						</fieldset>
					</form>
					{if $google_domain}
					{if $google_email}
					<form action="/preferences/unlink_google_login/" method="post" class="saveform">
						<fieldset>
							<div class="content">
								<div class="form_help">
									<p>When logging in to Tactile CRM, you may enter your email address as your username, without a password.</p>
								</div>
								<div class="row">
									<span class="false_label">Google Apps Domain</span>
									<span class="false_input">{$google_domain}</span>
								</div>
								<div class="row">
									<span class="false_label">Email Address</span>
									<span class="false_input" title="{$openid}">{$google_email}</span>
								</div>
							</div>
						</fieldset>
						<fieldset class="prefs_save">
							<div class="content">
								<div class="row">
									<input type="submit" value="Unlink Accounts" class="submit"/>
								</div>
							</div>
						</fieldset>
					</form>
					{else}
					<form action="/preferences/save_google_login/" method="post" class="saveform">
						<fieldset>
							<div class="content">
								<div class="form_help">
									<p>Google Apps users can enter their Email Address below to link it to your Tactile CRM account.</p>
								</div>
								<div class="row">
									<span class="false_label">Google Apps Domain</span>
									<span class="false_input">{$google_domain}</span>
								</div>
								<div class="row">
									<label for="google_login">Email Address</label>
									<input type="text" name="google_login" id="google_login" class="required"/>
								</div>
							</div>
						</fieldset>
						<fieldset class="prefs_save">
							<div class="content">
								<div class="row">
									<input type="submit" value="Link Accounts" class="submit"/>
								</div>
							</div>
						</fieldset>
					</form>
					{/if}
					{/if}
				</div>
			</div>
			
			<div class="content_holder">
				<div id="pref_dashboard" class="show"{if $pref_view neq 'dashboard'} style="display: none;"{/if}>
					<form action="/preferences/filter_dashboard/" method="post" class="saveform">
						<fieldset>
							<div class="content">
								<table id="timeline_options" class="index_table">
									<colgroup />
									<colgroup class="radios" span="3" />
									<thead>
										<tr>
											<th>Show on Dashboard</th>
											<th class="toggle">All</th>
											<th class="toggle">Just Mine</th>
											<th class="toggle">None</th>
										</tr>
									</thead>
									<tbody id="activity_timeline">
										<tr>
											<th scope="row" class="primary an_activity"><span class="sprite sprite-activity">New, Uncompleted Activities</span></th>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[activities][new]" value="all" {if $dashboard_prefs.activities.new eq 'all'} checked="checked"{/if}/></td>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[activities][new]" value="mine" {if $dashboard_prefs.activities.new eq 'mine'} checked="checked"{/if}/></td>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[activities][new]" value="none" {if $dashboard_prefs.activities.new eq 'none'} checked="checked"{/if}/></td>
										</tr>
										<tr>
											<th scope="row" class="primary an_overdue_activity"><span class="sprite sprite-activity">Recently Overdue Activities</span></th>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[activities][overdue]" value="all" {if $dashboard_prefs.activities.overdue eq 'all'} checked="checked"{/if}/></td>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[activities][overdue]" value="mine" {if $dashboard_prefs.activities.overdue eq 'mine'} checked="checked"{/if}/></td>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[activities][overdue]" value="none" {if $dashboard_prefs.activities.overdue eq 'none'} checked="checked"{/if}/></td>
										</tr>
										<tr>
											<th scope="row" class="primary a_completed_activity"><span class="sprite sprite-activity">Recently Completed Activities</span></th>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[activities][completed]" value="all" {if $dashboard_prefs.activities.completed eq 'all'} checked="checked"{/if}/></td>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[activities][completed]" value="mine" {if $dashboard_prefs.activities.completed eq 'mine'} checked="checked"{/if}/></td>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[activities][completed]" value="none" {if $dashboard_prefs.activities.completed eq 'none'} checked="checked"{/if}/></td>
										</tr>
										<tr>
											<th scope="row" class="primary an_opportunity"><span class="sprite sprite-opportunity">New Opportunities</span></th>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[opportunities][new]" value="all" {if $dashboard_prefs.opportunities.new eq 'all'} checked="checked"{/if}/></td>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[opportunities][new]" value="mine" {if $dashboard_prefs.opportunities.new eq 'mine'} checked="checked"{/if}/></td>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[opportunities][new]" value="none" {if $dashboard_prefs.opportunities.new eq 'none'} checked="checked"{/if}/></td>
										</tr>
										<tr>
											<th scope="row" class="primary a_note"><span class="sprite sprite-note">Notes</span></th>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[notes][new]" value="all" {if $dashboard_prefs.notes.new eq 'all'} checked="checked"{/if}/></td>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[notes][new]" value="mine" {if $dashboard_prefs.notes.new eq 'mine'} checked="checked"{/if}/></td>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[notes][new]" value="none" {if $dashboard_prefs.notes.new eq 'none'} checked="checked"{/if}/></td>
										</tr>
										<tr>
											<th scope="row" class="primary an_email"><span class="sprite sprite-email">Emails</span></th>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[emails][new]" value="all" {if $dashboard_prefs.emails.new eq 'all'} checked="checked"{/if}/></td>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[emails][new]" value="mine" {if $dashboard_prefs.emails.new eq 'mine'} checked="checked"{/if}/></td>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[emails][new]" value="none" {if $dashboard_prefs.emails.new eq 'none'} checked="checked"{/if}/></td>
										</tr>
										<tr>
											<th scope="row" class="primary a_file"><span class="sprite sprite-file">Files</span></th>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[files][new]" value="all" {if $dashboard_prefs.files.new eq 'all'} checked="checked"{/if}/></td>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[files][new]" value="mine" {if $dashboard_prefs.files.new eq 'mine'} checked="checked"{/if}/></td>
											<td class="toggle"><input class="checkbox" type="radio" name="dashboard[files][new]" value="none" {if $dashboard_prefs.files.new eq 'none'} checked="checked"{/if}/></td>
										</tr>
									</tbody>
								</table>
							</div>
						</fieldset>
						<fieldset class="prefs_save">
							<div class="content">
								<div class="row">
									<input type="submit" value="Save Preferences" class="submit"/>
								</div>
							</div>
						</fieldset>
					</form>
				</div>
			</div>
			
			<div class="content_holder">
				<div id="pref_email" class="show"{if $pref_view neq 'email'} style="display: none;"{/if}>
					<form action="/preferences/email_preferences/" method="post" class="saveform">
						<fieldset>
							<div class="form_help">
								<p>Choose whether Tactile CRM emails you about overdue or recently assigned Activities, or Dropbox problems.</p>
							</div>
							<div class="content">
								<div class="row">
									<label for="activity_reminders">Activity Reminders</label>
									<select id="activity_reminders" name="email_prefs[activity_reminder]">
										<option value="yes" {if $email_prefs.activity_reminder}selected="selected"{/if}>Send</option>
										<option value="no" {if !$email_prefs.activity_reminder}selected="selected"{/if}>Don't Send</option>
									</select>
								</div>
								<div class="row">
									<label for="activity_notifications">Activity Notifications</label>
									<select id="activity_notifications" name="email_prefs[activity_notification]">
										<option value="yes" {if $email_prefs.activity_notification}selected="selected"{/if}>Send</option>
										<option value="no" {if !$email_prefs.activity_notification}selected="selected"{/if}>Don't Send</option>
									</select>
								</div>
								<div class="row">		
									<label for="missing_contact_email">Missing Contact Alerts</label>
									<select id="missing_contact_email" name="email_prefs[missing_contact_email]">
										<option value="yes" {if $email_prefs.missing_contact_email}selected="selected"{/if}>Send</option>
										<option value="no" {if !$email_prefs.missing_contact_email}selected="selected"{/if}>Don't Send</option>
									</select>
								</div>
							</div>
						</fieldset>
						<fieldset class="prefs_save">
							<div class="content">
								<div class="row">
									<input type="submit" value="Change Email Preferences" class="submit" />
								</div>
							</div>
						</fieldset>
						{if $tactilemail_user_addresses}
						<fieldset>
							<div class="form_help">
								<p>Tactile CRM requires you to verify any Email Addresses you wish to send from.</p>
							</div>
							<div class="content">
								<table id="tactilemail_shared_addresses" class="index_table">
									<thead>
										<tr>
											<th>Email Address</th>
											<th>Verification Status</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										{foreach from=$emails item=email}
										<tr>
											<td class="email_address">{if $email->display_name}{$email->display_name} &lt;{/if}<span>{$email->email_address}</span>{if $email->display_name}&gt;{/if}</td>
											<td>
												{if $email->is_verified()}
												<span class="verified">Verified</span>
												{else}
												<span class="unverified">Awaiting Verification</span> (<a href="/preferences/send_validation_email/{$email->id}">Resend Email</a>) 
												{/if}
											</td>
											<td>
												<a class="action u_sure" href="/preferences/delete_email_address/?id={$email->id}">Delete<a>
											</td>
										</tr>
										{foreachelse}
										<tr>
											<td colspan="3">No Email Addresses</td>
										</tr>
										{/foreach}
									</tbody>
								</table>
								<p><a id="add_user_email_address" href="/preferences/save_email_address/" class="sprite sprite-add">Add An Email Address</a></p>
							</div>
						</fieldset>
						{/if}
					</form>
				</div>
			</div>
			
			<div class="content_holder">
				<div id="pref_date_time" class="show"{if $pref_view neq 'date_time'} style="display: none;"{/if}>
					<form action="/preferences/change_datetime" method="post" class="saveform">
						<fieldset>
							<div class="content">
								<div class="row">
									<label for="date_format">Date Format</label>
									<select id="date_format" name="date_format">
										<option value="dmy"{if $date_format eq 'dmy'} selected="selected"{/if}>Day / Month / Year</option>
										<option value="mdy"{if $date_format eq 'mdy'} selected="selected"{/if}>Month / Day / Year</option>
									</select>
								</div>
								<div class="row">
									<label for="time_format">Time Format</label>
									<select id="dtime_format" name="time_format">
										<option value="24h"{if $time_format eq '24h'} selected="selected"{/if}>13:00 (24-hour)</option>
										<option value="12h"{if $time_format eq '12h'} selected="selected"{/if}>1:00PM (12-hour)</option>
									</select>
								</div>
								<div class="row">
									<label for="timezone">Timezone</label>
									<select id="timezone" name="timezone">
										{html_options options=$europe_timezones selected=$europe_selected}
										<option value="">-------------</option>
										{html_options options=$america_timezones selected=$america_selected}
										<option value="">-------------</option>
										{html_options options=$all_timezones selected=$all_selected}
									</select>
								</div>
							</div>
						</fieldset>
						<fieldset class="prefs_save">
							<div class="content">
								<div class="row">
									<input type="submit" value="Change Date/Time Settings" class="submit" />
								</div>
							</div>
						</fieldset>
					</form>
				</div>
			</div>
			
			<div class="content_holder">
				<div id="pref_keys" class="show"{if $pref_view neq 'keys'} style="display: none;"{/if}>
					<form action="/preferences/gen_key/" method="post" class="saveform" id="key_prefs">
						<fieldset>
							<div class="form_help">
								<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
								<p>Your Dropbox Address is a unique email address which you can use to attach mail to your Tactile contacts.</p>
							</div>
							<div class="content">
								{if $dropboxkey neq ''}
								<p>Your Dropbox Address is: <span class="dropboxkey mini_preference_label"><a href="mailto:{$current_user->getDropboxAddress()}">{$current_user->getDropboxAddress()}</a></span></p>
								{else}
								<p>You have not yet created a Dropbox Address.</p>
								{/if}
							</div>
						</fieldset>
						<fieldset>
							<div class="content">
								<div class="row">
									<input type="submit" value="New Dropbox Address" class="submit mini_preference_save" name="submit_key" />
								</div>
							</div>
						</fieldset>
						<fieldset>
							<div class="form_help">
								<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
								<p>Your Subscription Key can be used to access your Tactile activities from any iCalendar-supporting application, and your dashboard feed from any RSS-supporting application.</p>
							</div>
							<div class="content">
								{if $webkey neq ''}
								<p>Your iCal Calendar URL is: <span class="webkey mini_preference_label"><a href="{$current_user->getCalendarAddress()}">{$current_user->getCalendarAddress()}</a></span></p>
								<p>Your Outlook Calendar URL is: <span class="webkey mini_preference_label"><a href="{$current_user->getCalendarAddress()|replace:"http":"webcal"}">{$current_user->getCalendarAddress()|replace:"http":"webcal"}</a></span></p>
								<p>Your Dashboard Feed URL is: <span class="webkey mini_preference_label"><a href="{$current_user->getTimelineFeedAddress()}">{$current_user->getTimelineFeedAddress()}</a></span></p>
								{else}
								<p>You have not yet created a Subscription Key.</p>
								{/if}
							</div>
						</fieldset>
						<fieldset>
							<div class="content">
								<div class="row">
									<input type="submit" value="New Subscription Key" class="submit mini_preference_save" name="submit_key" />
								</div>
							</div>
						</fieldset>
						{if $api_enabled}
						<fieldset>
							<div class="form_help">
								<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
								<p>Your API Token can be used to access your Tactile data without using the web interface.</p>
							</div>
							<div class="content">
								{if $api_token neq ''}
								<p>Your API Token is: <span class="api_token mini_preference_label">{$current_user->getApiToken()}</span></p>
								{else}
								<p>You have not yet generated an API Token.</p>
								{/if}
							</div>
						</fieldset>
						<fieldset>
							<div class="content">
								<div class="row">
									<input type="submit" value="New API Token" class="submit mini_preference_save" name="submit_key" />
								</div>
							</div>
						</fieldset>
						{/if}
					</form>
				</div>
			</div>
		
		</div>
	</div>
</div>
