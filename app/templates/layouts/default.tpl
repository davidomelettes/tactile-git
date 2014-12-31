<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		{include_css}
		{if $current_user->getAccount()->getThemeCss() == 'themes/custom.css'}
		<style type="text/css">
			#header {literal}{{/literal}
				background-color: {$current_user->getAccount()->getCustomThemePrimary()};
				background-image: url(/gradient.php?s={$current_user->getAccount()->getCustomThemeSecondary()|urlencode}&e={$current_user->getAccount()->getCustomThemePrimary()|urlencode});
			{literal}}{/literal}
			ul#sub_nav li.level1,
			ul#main_nav li.level1 {literal}{{/literal} background-color: {$current_user->getAccount()->getCustomThemeSecondary()}; {literal}}{/literal}
		</style>
		{/if}
		<title>{if $head_title}{$head_title} | {/if}{if $_area eq 'campaignmonitor'}Campaign Monitor{else}{$_area|default:"My Dashboard"|capitalize}{/if} | Tactile CRM</title>
		
		<script type="text/javascript">
		/* <![CDATA[ */
		{assign var=account value=$current_user->getAccount()}
		var Om = {literal}{}{/literal};
		Om.Page = {literal}{{/literal}"controller":"{$_area}","action":"{$_action}"{literal}}{/literal};
		var Tactile = {literal}{}{/literal};
		Tactile.DATE_FORMAT = '{$DATE_FORMAT}';
		Tactile.DATE_TIME_FORMAT = '{$smarty.const.DATE_TIME_FORMAT}';
		Tactile.COUNTRY_CODE = '{$COUNTRY_CODE}';
		Tactile.CURRENCY_SYMBOL = '£';
		Tactile.DROPBOX_ADDRESS = '{$current_user->getDropboxAddress()}';
		Tactile.Account = {literal}{{/literal}
			'id': {$current_user->getAccount()->getId()},
			'plan': '{$current_user->getAccount()->getPlan()->getName()}',
			'plan_id': {$current_user->getAccount()->getPlan()->getId()},
			'is_in_trial': {if $current_user->getAccount()->in_trial()}true{else}false{/if},
			'is_free': {if $current_user->getAccount()->getPlan()->is_free()}true{else}false{/if},
			'is_per_user': {if $current_user->getAccount()->getPlan()->is_per_user()}true{else}false{/if},
			'tactilemail_enabled': {if $current_user->getAccount()->isTactileMailEnabled()}true{else}false{/if},
			'username': '{$current_user->username|escape}',
			'site_address': '{$current_user->getUserspace()}'
		{literal}}{/literal};
		/* ]]> */
		</script>
		<link rel="icon" href="/graphics/tactile/icons/favicon.ico" type="image/x-icon" />
		<link rel="shortcut icon" href="/graphics/tactile/icons/favicon.ico" type="image/x-icon" />
		{if $timeline_rss}<link rel="alternate" type="application/rss+xml" title="RSS" href="{$timeline_rss}" />{/if}
	</head>
	{if $current_user->getAccount()->is_free() && !$current_user->getAccount()->in_trial()}
		{assign var="upsell" value=true}
		{assign var="highlight" value='info'}
	{/if}
	{flash dismiss="Click to dismiss"}
	<body class="area_{$_area}">
		{if $_motd_important}
		<div id="motd_important">
			<div class="inner">
				<span id="motd_dismiss"><a title="Dismiss this message" class="sprite sprite-dismiss" href="/magic/dismiss_motd/?id={$_motd_id}"> </a></span>
				<p>{$_motd_important}</p>
			</div>
		</div>
		{/if}
		<div id="browser_warning" style="display: none;">
			You appear to be using <span class="warning">Internet Explorer 6</span>.
			Tactile CRM will look and behave much better if you <a href="http://www.microsoft.com/windows/downloads/ie/getitnow.mspx">upgrade to version 7</a>,
			or switch to another browser,
			such as <a href="http://www.mozilla.com/firefox/">FireFox</a>, <a href="http://www.apple.com/safari/">Safari</a>, or <a href="http://www.opera.com/">Opera</a>.
		</div>
		<div id="container">
			{include file="elements/navigation/navbar.tpl}
			<div id="header">
				<div id="topbit">
					<h1><a href="/organisations/view/{$account->organisation_id}">{$user_company_name|escape}</a></h1>
				</div>
				
				{include file="elements/navigation/mainnav.tpl"}
				{include file="elements/navigation/subnav.tpl"}
			</div>
			{include file="elements/upsell.tpl"}
			{$flash}
			<div id="main">
				{include file=$templateName}
			</div>
			<div class="clear">&nbsp;</div>
		</div>
		<div id="logo_holder">
			<img src="/graphics/tactile/tactile.png" alt="Tactile CRM" />
			<a href="http://tactilecrm.com">Tactile CRM</a> is an <a href="http://omelett.es">omelett.es ltd</a> product.<br />
			<a href="http://tactilecrm.com/privacy">Privacy Policy</a> | <a href="http://tactilecrm.com/terms">Terms &amp; Conditions</a>
		</div>
		<div id="modal_form" style="display: none;">
			<div class="modal-shadow">
				<div class="shadow-inner">
					<form method="post" class="saveform" action="/" enctype="multipart/form-data">
					</form>
				</div>
			</div>
		</div>
		<div id="javascript_holder">
			{include_javascript}
		</div>
	</body>
</html>
