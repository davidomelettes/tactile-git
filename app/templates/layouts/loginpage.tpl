<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" media="all" href="/new_css/login/login_page.css" />
		<title>Tactile CRM Log In</title>
		<link rel="icon" href="/graphics/tactile/icons/favicon.ico" type="image/x-icon" />
		<link rel="shortcut icon" href="/graphics/tactile/icons/favicon.ico" type="image/x-icon" />
	</head>
	<body>
		<div id="horizon">
			<div id="wrapper" class="{$wrapper_class}">
				{include file=$templateName}
			</div>
            <p id="ownership">&copy; <a href="http://omelett.es">omelett.es ltd</a>. All Rights Reserved.<br />
            <a href="http://www.tactilecrm.com">Tactile</a> &amp; <a href="http://www.tactilecrm.com">Tactile CRM</a> are trademarks of <a href="http://omelett.es">omelett.es ltd</a>.</p>
		</div>
		<div>
	        <script type="text/javascript">
	        var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	        document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	        </script>
	        <script type="text/javascript">
	        {literal}
	        try {
	        var pageTracker = _gat._getTracker("UA-2898885-6");
	        pageTracker._setDomainName(".tactilecrm.com");
	        pageTracker._trackPageview();
	        } catch(err) {}</script>
	        {/literal}
       	</div>
	</body>
</html>
