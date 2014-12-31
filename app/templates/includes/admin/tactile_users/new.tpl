<div id="right_bar">
	{foldable}
		<p><a href="/users/">Back to User List</a></p>
	{/foldable}
	{foldable key="user_usage" title="User Usage"}
		<p>You are currently using <strong>{$users_used}</strong> of your <strong>{$users_limit}</strong> User limit</p>
		{if $plan->is_per_user()}
		<p><a href="/users/purchase/">Increase Limit</a></p>
		{else}
		<p><a href="/account/change_plan/">Increase Limit</a></p>
		{/if}
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			{if $Person->id}
			<h2>Editing {$User->getFormatted('username')}</h2>
			{else}
			<h2>New User</h2>
			{/if}
		</div>
		<form action="/users/save/" method="post" class="saveform">
			<div class="content_holder">
				<fieldset>
					<h3>User Details</h3>
					<div class="form_help">
						<p>Once this User has been saved they will receive an email with their username and password - <strong>please choose the username carefully as it cannot be changed or deleted</strong>.</p> 
					</div>
					<div class="content">
						{with model=$Person}
						{input type="hidden" attribute="id"}
						{input type="text" attribute="firstname"}
						{input type="text" attribute="surname"}
						{with alias='email'}
							{input type="text" attribute="contact" label="Email Address *"}
							{input type="hidden" attribute="id"}
						{/with}
						{/with}
						{with model=$User}
						{if $google_domain}
						{input type="hidden" attribute="username"}
						<div class="row">
							<span class="false_label">Google Apps Domain</span>
							<span class="false_input">{$google_domain}</span>
						</div>
						{else}
						{input type="text" attribute="username"}
						{/if}
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<fieldset>
					<div class="form_help">
						<p>Admin Users have unrestricted access to Tactile and can add/remove other Users.</p>
					</div>
					<div class="content">
						{input type="checkbox" attribute="is_admin" label="Make Admin"}
						{input type="hidden" attribute="person_id"}
						{/with}
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<fieldset>
					<div class="form_help">
						<p>If you have any <a href="/groups/">Groups</a> setup you can add the new user to them.</p>
					</div>
					<div class="content">
						<div class="row">
							<label for="role_ids">Groups</label>
							<select multiple="multiple" id="role_ids" name="role_ids[]">{html_options options=$roles selected=$selected_roles}</select>
						</div>
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<fieldset id="save_container">
					<div class="content">
						<div class="row">
							<input type="submit" value="Save" />
						</div>
					</div>
				</fieldset>
			</div>
		</form>
	</div>
</div>
