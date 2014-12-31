<?php

function smarty_function_include_javascript($params,&$smarty) {
	if(defined('PRODUCTION')&&PRODUCTION) {
		return '
		<script type="text/javascript" src="/js/lib/jquery-1.5.1.min.js"></script>
		<script type="text/javascript" src="/js/lib/jquery-ui-1.8.11.custom.min.js"></script>
		<script type="text/javascript" src="/js/combined-four.compressed.js' .
		(defined('TACTILE_SVN_REVISION') ? ('?' . TACTILE_SVN_REVISION) : '') .
		'"></script>';
	}
	return '
	<script type="text/javascript" src="/js/lib/jquery-1.5.1.min.js"></script>
	<script type="text/javascript" src="/js/lib/jquery-ui-1.8.11.custom.min.js"></script>
	<script type="text/javascript" src="/js/omelette.classes.js"></script>
	<script type="text/javascript" src="/js/lib/plugins/jquery.hotkeys.js"></script>
	<script type="text/javascript" src="/js/lib/plugins/jquery.activities.js"></script>
	<script type="text/javascript" src="/js/lib/plugins/jquery.expandingtextarea.js"></script>
	<script type="text/javascript" src="/js/lib/plugins/jquery.foldables.js"></script>
	<script type="text/javascript" src="/js/lib/plugins/jquery.quickadders.js"></script>
	<script type="text/javascript" src="/js/lib/plugins/jquery.timelines.js"></script>
	<script type="text/javascript" src="/js/lib/plugins/jquery.tags.js"></script>
	<script type="text/javascript" src="/js/lib/plugins/jquery.config.js"></script>
	<script type="text/javascript" src="/js/lib/plugins/jquery.freshbooks.js"></script>
	<script type="text/javascript" src="/js/lib/plugins/jquery.email.js"></script>
	<script type="text/javascript" src="/js/lib/plugins/jquery.customvalue.js"></script>
	<script type="text/javascript" src="/js/lib/plugins/jquery.indextables.js"></script>
	<script type="text/javascript" src="/js/lib/plugins/jquery.querybuilder.js"></script>
	<script type="text/javascript" src="/js/omelette.rules.js"></script>
	<script type="text/javascript" src="/js/tactile.rules.js"></script>
	<script type="text/javascript" src="/js/lib/farbtastic.js"></script>
	';
}
