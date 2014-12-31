<div id="right_bar">
	{foldable}
		<p><a href="/account/">Back to Account Options</a></p>
	{/foldable}
</div>
<div id="the_page" class="account_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Change Account Owner</h2>
		</div>
		<form action="{$smarty.const.SERVER_ROOT|replace:'http://':'https://'}/account/process_change_owner" method="post" class="saveform">
			<div class="content_holder">
				<fieldset>
					<div class="form_help">
						<p>Are you sure you want to change the account owner? <strong>This will be irreversible by you!</strong></p>
						<p>The account owner has access to change the account and payment details, change the plan or even cancel the account.</p>
						<p>You cannot change yourself back to being the owner once you have selected another user, the nominated user must login themselves and change the account owner back to you.</p>
						<p>Before a user can become the new account owner, they must be an admin.</p>
					</div>
					<div class="content">
						<div class="row">
							<label for="user">New owner</label>
							<select class="required" id="user" name="user">
								{html_options options=$users selected=$users|default:"GB"}
							</select>
						</div>
					</div>
				</fieldset>
			</div>
			<div class="content_holder">
				<label for="confirm" class="inline_checkbox">Tick the following box to confirm the change of owner.
				This action is not reversible; <strong>if you do this you must ask the new owner to make you owner again</strong>.
				<input id="confirm" type="checkbox" class="checkbox" name="confirm" /></label>
			</div>
			<div class="content_holder">
				<fieldset id="save_container">
					<div class="content">
						<div class="row">
							<input type="submit" value="Change Account Owner" />
						</div>
					</div>
			    </fieldset>
			</div>
		</form>
	</div>
</div>