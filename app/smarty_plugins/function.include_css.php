<?php
	function smarty_function_include_css($params,&$smarty) {
		$theme_css = CurrentlyLoggedInUser::Instance()->getAccount()->getThemeCss();
		if(defined('PRODUCTION')&&PRODUCTION) {
			return '<link rel="stylesheet" type="text/css" media="all" href="/new_css/combined-four.compressed.css' .
			(defined('TACTILE_SVN_REVISION') ? ('?' . TACTILE_SVN_REVISION) : '') .
			'" />
			<link rel="stylesheet" type="text/css" media="all" href="/new_css/smoothness/jquery-ui-1.8.9.custom.css" />
			<link rel="stylesheet" type="text/css" media="all" href="/new_css/'.$theme_css.'" />
			<!--[if IE 6]>		
				<link rel="stylesheet" type="text/css" media="all" href="/new_css/ie/ie6.css" />
			<![endif]-->
			<!--[if IE]>
				<link rel="stylesheet" type="text/css" media="all" href="/new_css/ie/ie.css" />
			<![endif]-->
			<link rel="stylesheet" type="text/css" media="all" href="/new_css/tactile_webform.css" />
			';
		}
		
		return '
		<link rel="stylesheet" type="text/css" media="all" href="/new_css/smoothness/jquery-ui-1.8.9.custom.css" />
		<link rel="stylesheet" type="text/css" media="all" href="/new_css/reset.css" />
		<link rel="stylesheet" type="text/css" media="all" href="/new_css/screen.css" />
		<link rel="stylesheet" type="text/css" media="all" href="/new_css/nonstandard.css" />
		<link rel="stylesheet" type="text/css" media="all" href="/new_css/index_page.css" />
		<link rel="stylesheet" type="text/css" media="all" href="/new_css/view_page.css" />
		<link rel="stylesheet" type="text/css" media="all" href="/new_css/edit_page.css" />
		<link rel="stylesheet" type="text/css" media="all" href="/new_css/images.css" />
		<link rel="stylesheet" type="text/css" media="all" href="/new_css/colours.css" />
		<link rel="stylesheet" type="text/css" media="all" href="/new_css/'.$theme_css.'" />
		<!--[if IE 6]>		
			<link rel="stylesheet" type="text/css" media="all" href="/new_css/ie/ie6.css" />
		<![endif]-->
		<!--[if IE]>
		<link rel="stylesheet" type="text/css" media="all" href="/new_css/ie/ie.css" />
		<![endif]-->
		<link rel="stylesheet" type="text/css" media="all" href="/new_css/tactile_webform.css" />
		<link rel="stylesheet" type="text/css" media="all" href="/new_css/farbtastic.css" />
		';
	}
