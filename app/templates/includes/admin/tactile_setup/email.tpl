<div id="right_bar">
	{foldable}
		<p><a href="/admin/">Back to Admin</a></p>
	{/foldable}
	{foldable key="tactilemail_options" title="TactileMail Options" extra_class="unfoldable"}
		<form action="/setup/email_save/" method="post">
			<ul class="sidebar_options">
				{if !$account->is_free()}
				<li><label><input type="checkbox" class="checkbox" name="tactilemail_enabled"{if $tactilemail_enabled} checked="checked"{/if} />
				Enable TactileMail?</label></li>
				{/if}
				<li><label><input type="checkbox" class="checkbox" name="tactilemail_user_addresses"{if $tactilemail_user_addresses} checked="checked"{/if} />
				Allow User-specified email addresses?</label></li>
			</ul>
			<p><input type="submit" class="submit" value="Save" /></p>
		</form>
	{/foldable}
	{foldable title="TactileMail Help" key="tactilemail_help" extra_class="help"}
		<p>Before Users can send email via Tactile CRM, they will need to verify their Email Addresses.</p>
		<p>Using these controls, Admins can verify email addresses to be shared by all Users, and create message templates.</p>
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
        <div id="pref_nav">
			<ul>
				<li{if $pref_view eq 'shared_addresses'} class="on"{/if}><a href="/setup/shared_addresses/" id="pref_tab_shared_addresses">TactileMail Addresses</a></li>
				<li class="arrow {if $pref_view eq 'shared_addresses'} on_left{elseif $pref_view eq 'email_templates'} on_right{/if}">&nbsp;</li>
				<li{if $pref_view eq 'email_templates'} class="on"{/if}><a href="/setup/email_templates/" id="pref_tab_email_templates">TactileMail Templates</a></li>
				<li class="arrow {if $pref_view eq 'email_templates'} on_left{/if}">&nbsp;</li>
			</ul>
		</div>
		
		<div id="pref_content">
		
			<div class="content_holder">
				<div id="pref_shared_addresses" class="show"{if $pref_view neq 'shared_addresses'} style="display: none;"{/if}>
					<div class="form_help">
						<p>Adding an email address to this list will enable <strong>all Users</strong> on your Account to send from it.</p>
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
										<span class="unverified">Awaiting Verification</span> (<a href="/emailaddresses/send_validation_email/{$email->id}">Resend Email</a>) 
										{/if}
									</td>
									<td>
										<a class="action u_sure" href="/emailaddresses/delete/{$email->id}">Delete<a>
									</td>
								</tr>
								{foreachelse}
								<tr>
									<td colspan="3">No Shared Email Addresses</td>
								</tr>
								{/foreach}
							</tbody>
						</table>
						<p><a href="/emailaddresses/save/" id="add_shared_email_address" class="sprite sprite-add">Add Shared Email Address</a></p>
					</div>
				</div>
			</div>
			
			<div class="content_holder">
				<div id="pref_email_templates" class="show"{if $pref_view neq 'email_templates'} style="display: none;"{/if}>
					<div class="form_help">
						<p>Templates can be loaded when composing a new message using TactileMail.</p>
					</div>
					<div class="content">
						<ul id="activity_timeline">
							{foreach from=$templates item=template}
							<li class="when"><h3>{$template->name}</h3></li>
							<li>
								<div class="an_email timeline_item">
									<div class="header">
										<h4>{$template->subject}</h4>
										<div class="email_actions">
											<ul>
												<li><a class="action edit" href="/templates/edit/{$template->id}">Edit</a></li>
												<li><a class="action u_sure" href="/templates/delete/{$template->id}">Delete</a></li>
											</ul>
										</div>
									</div>
									<div class="body">
										<p>{$template->body|truncate:75:'...'}</p>
									</div>
								</div>
							</li>
							{/foreach}
						</ul>
						<p><a href="/templates/new/" id="new_email_template" class="sprite sprite-add">Create New Template</a></p>
					</div>
				</div>
			</div>
			
		</div>
	</div>
</div>
