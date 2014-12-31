{if $logo_url}
<img id="account_logo" src="{$logo_url}" alt="logo" />
{else}
<h1>Log in to Tactile CRM</h1>
{/if}
{flash}
{$flash}
<form action="/login/" method="post" id="normal_login_form">
	<fieldset id="username_area">
		<input type="hidden" name="redirect" value="{$smarty.server.REQUEST_URI|replace:"logout":""}" />
		<label for="username_field">Username</label>
		<input type="text" name="username" id="username_field"/>
		<script type="text/javascript">
			document.getElementById('username_field').focus();
		</script>
	</fieldset>
	<fieldset id="password_area">
		<label for="password">Password</label>
		<input type="password" name="password" id="password" />
	</fieldset>
	<fieldset id="remember_area">
		<input class="checkbox" type="checkbox" name="rememberUser" id="remember" />
		<label for="remember">Remember me on this computer</label>
	</fieldset>
	<div>
		<input class="submit" type="submit" value="Login" name="logmein" />
	</div>
</form>
{if $google_domain}
<form action="/" method="get" id="openid_login_form">
	<fieldset>
		<label for="openid_field">Google Apps Email Address</label>
		<input type="text" name="openid_login" id="openid_field" />
	</fieldset>
	<div>
		<input class="submit" type="submit" value="Login" name="logmein" />
	</div>
</form>
{/if}
<p id="trouble_link"><a href="/password/">Having trouble logging in?</a>{if !$smarty.server.HTTP_X_FARM} | <a href="https://{$smarty.server.SERVER_NAME}">Switch to SSL connection</a>{/if}</p>

