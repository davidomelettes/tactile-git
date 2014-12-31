<div id="right_bar">
	{foldable}
		<p><a href="/account/">Back to Account Options</a></p>
	{/foldable}
</div>
<div id="the_page" class="account_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Cancelling Your Account</h2>
		</div>
		<form action="/account/process_cancel" method="post" class="saveform">
			<div class="content_holder">
				<fieldset>
					<div class="form_help">
						<p>Are you sure you want to cancel your account? <strong>This will be irreversible!</strong></p>
						<p>If you're not happy with your current plan you can always <a href="/account/change_plan/">change it</a>.</p>
						<p>The following will happen if you do decide to cancel:</p>
						<ul class="bullets">
							<li>All Users on your account will be immediately logged out</li>
							<li>No one will be able to log back in</li>
							<li><strong>It will become impossible to access any data left in Tactile</strong></li>
						</ul>
					</div>
					{if $google_apps_domain}
					<div class="content">
						<div class="row">
							<label for="password">Confirm your email address</label>
							<input type="text" name="password" id="password" value="{$google_apps_email|escape}" />
						</div>
					</div>
					{else}
					<div class="content">
						<div class="row">
							<label for="password">Confirm your password</label>
							<input type="password" name="password" id="password" />
						</div>
					</div>
					{/if}
				</fieldset>
			</div>
			<div class="content_holder">
				<label for="confirm" class="inline_checkbox">Tick the following box to confirm your cancellation.
				This action is not reversible; <strong>if you do this we will not be able to undo it</strong>.
				<em class="with_colour">Your site address will not become available to anybody else, nor will it be available later if you want to sign up with it again.</em>
				<input id="confirm" type="checkbox" class="checkbox" name="confirm" /></label>
			</div>
			<div class="content_holder">
				<fieldset id="save_container">
					<div class="content">
						<div class="row">
							<input type="submit" value="Cancel My Account" />
						</div>
					</div>
			    </fieldset>
			</div>
		</form>
	</div>
</div>
