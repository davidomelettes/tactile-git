<div id="right_bar">
	{foldable}
		<p><a href="/import/">Select a different Import type</a></p>
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
        <div id="page_title">
        	<h2>Import Highrise Data into Tactile</h2>
        </div>
        <form action="/import/highrise_users/" method="post" class="saveform" >
			<div class="content_holder">
				<fieldset>
					<h3>Login Information</h3>
					<div class="form_help">
						<p>Your <strong>Highrise API Token</strong> can be found on the "My Info" page on Highrise.</p>
					</div>
					<div class="content">
						<div id="gmail_username" style="position: relative;">
							<div class="row">
								<label for="username">Highrise API Token</label>
								<input type="text" name="username" id="username"/>
								<input type="hidden" name="password" id="password" value="x" />
							</div>
							<div class="row">
								<label for="password">Highrise Site Address</label>
								<input type="text" name="site" id="site"/>
							</div>
							<span id="accountname" class="fb_account_name false_input" style="position: absolute; left: 405px; top: 40px; font-size: 1.2em;">.highrisehq.com</span>
						</div>		
					</div>
				</fieldset>
			</div>
			
			<div class="content_holder">
				<fieldset id="save_container">
					<div class="content">
						<div class="row">
							<input type="submit" value="Import" />
						</div>
					</div>
				</fieldset>
			</div>
		</form>
	</div>
</div>
