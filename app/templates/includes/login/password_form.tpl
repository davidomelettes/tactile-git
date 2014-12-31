{if $logo_url}
<img id="account_logo" src="{$logo_url}" alt="logo" />
{else}
<h1>Having trouble logging in?</h1>
{/if}
<ol>
	<li><strong>Check the URL</strong> in your browser's address bar. Is "{$site_address}" your account name?</li>
	<li>Don't forget: Usernames and passwords are <strong>case-sensitive</strong>.</li>
	<li>Forgotten your login details? Use the forms below to get help:</li> 
</ol>
{flash}
{$flash}
<form action="/password/resetbyusername" method="post" id="reset_password">
	<fieldset id="username_area">
		<h3>Forgotten your password?</h3>
		<input type="hidden" name="redirect" value="{$smarty.get.url|replace:"logout":""}" />
		<label for="username">Enter your username:</label>
		<input type="text" name="username" id="username" />
	</fieldset>
	<div><input class="submit" type="submit" value="Email me a new password" /></div>
</form>
<form action="/username/remindbyemail" method="post" id="remind_username">
	<fieldset id="email_area">
		<h3>Forgotten your username?</h3>
		<input type="hidden" name="redirect" value="{$smarty.get.url|replace:"logout":""}" />
		<label for="email">Enter your email address:</label>
		<input type="text" name="email" id="email" />
	</fieldset>
	<div><input class="submit" type="submit" value="Email me my username" /></div>
</form>
<p id="trouble_link"><a href="/">Back to login page</a></p>
