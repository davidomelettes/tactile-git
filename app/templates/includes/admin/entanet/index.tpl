<div id="right_bar">
	{foldable}
		<p><a href="/admin/">Back to Admin</a></p>
	{/foldable}
	{foldable key="entanet_help" title="What is Entanet/VOIP Enrich?" extra_class="help"}
	<p>
		VOIP Enrich is a hosted VOIP platform designed specifically with small businesses in mind.
	</p>
	<p>
		It’s easy to setup and install and starts at as little as £7/month. If you are interested in signing up for a VOIP enrich account why not talk to <a href="http://senokianinternet.com">Senokian Internet</a> who will help you get setup and using the service (they host Tactile CRM and are a really helpful company).
	</p>
	{/foldable}
</div>
<div id="the_page">
	<div class="edit_holder">
		<div id="page_title">
			<h2>Setup Entanet Integration</h2>
		</div>
		<form action="/entanet/setup" method="post" class="saveform" id="entanet_setup_form">
			<div class="content_holder">
				<fieldset>
					<h3>Entanet Account Details</h3>
					<div class="form_help">
						<span class="more">(<a href="http://www.tactilecrm.com/help">More Help</a>)</span>
						<p>To integrate with Entanet we'll need your <strong>Domain</strong> (e.g. "acme.managedvoip.net") and <strong>Security Code</strong>.</p>
					</div>
					<div class="content">
						<div class="right">
							<img src="/graphics/3rd_party/entanet_logo2.jpg" alt="Entanet Logo" />
						</div>
						<label for="entanet_domain">Domain</label>
						{if $entanet_domain}
						<span class="entanet_domain false_input" style="float:left;">{$entanet_domain|h}</span>
						{else}
						<input class="required" type="text" id="entanet_domain" name="entanet_domain" />
						{/if}
						<label for="entanet_code">Security Code</label>
						<input class="required" type="text" id="entanet_code" name="entanet_code" value="{$entanet_code}"/>
					</div>
				</fieldset>
				<fieldset class="prefs_save">
					<div class="content">
						<input type="submit" value="{if $entanet_domain}Update Details{else}Connect{/if}" />
					</div>
				</fieldset>
			</div>
		</form>
		{if $entanet_domain}
		<form action="/entanet/assign_extensions" method="post" class="saveform" id="entanet_assign_form">
			<div class="content_holder">
				<fieldset>
					<h3>Assign Extensions</h3>
					<div class="form_help">
						<p>
						You can assign your Entanet extensions to your users - you need to do this for the &ldquo;Click-to-Dial&rdquo; and
						&ldquo;Who's Calling?&rdquo; functionality to work.
						</p> 
					</div>
					<div class="content">
						<table id="entanet-user-table">
							<thead>
								<tr>
									<th>Tactile User</th><th>VOIP Enrich Extension</th>
								</tr>
							</thead>
							<tbody>
								{foreach from=$users item=username key=raw_username}
									<tr>
										<td>{$username|h}</td>
										<td><input type="text" name="extensions[{$username}]" value="{$extensions.$raw_username|h}" /></td>
									</tr>
								{/foreach}
							</tbody>
						</table>
					</div>
				</fieldset>
				<fieldset class="prefs_save">
					<div class="content">
						<input type="submit" value="Assign Extensions" />
					</div>
				</fieldset>
			</div>
		</form>
		<form action="/entanet/reset" method="post" class="saveform delete_form" id="entanet_reset_form">
			<div class="content_holder">
				<fieldset class="prefs_save">
					<h3>Unlink Entanet Account</h3>
					<div class="content">
						<p>If you want to unlink Tactile from Entanet, click this button.</p>
						<input type="submit" value="Reset" />
					</div>
				</fieldset>
			</div>
		</form>
		{/if}
	</div>
</div>