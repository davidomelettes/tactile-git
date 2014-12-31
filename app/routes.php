<?php
$rp = RouteParser::Instance();
$rp->addRoute(
	new RegexRoute(
		'^$',
		array(
			'module'=>'tactile',
			'controller'=>'dashboard',
			'action'=>'index'
		)
	)
);
$rp->addRoute(
	new RegexRoute(
		'^(?P<action>log(?:in|out))/?$',
		array(
			'module'=>'login',
			'controller'=>'index'
		)
	)
);
$rp->addRoute(
	new RegexRoute(
		'^password(?:/(?P<action>[a-z_]+))?/?',
		array(
			'module'=>'login',
			'controller'=>'index',
			'action'=>'password_form'
		)
	)
);
$rp->addRoute(
	new RegexRoute(
		'^username(?:/(?P<action>[a-z_]+))?/?',
		array(
			'module'=>'login',
			'controller'=>'index',
			'action'=>'password_form'
		)
	)
);
$rp->addRoute(
	new RegexRoute(
		'^openid(?:/(?P<openid_domain>[a-z_.-]+))?/?',
		array(
			'module'=>'login',
			'controller'=>'index',
			'action'=>'openid'
		)
	)
);
$rp->addRoute(
	new RegexRoute(
		'^magic/(?P<action>[a-z_]+)?/?$',
		array(
			'module'=>'tactile',
			'controller'=>'magic'
		)
	)
);
$rp->addRoute(
	new RegexRoute('^(?P<area>admin)/(?P<action>[a-z_]+)?/?$',
		array(
			'module'=>'admin',
			'controller'=>'admin',
			'action'=>'index'
		)
	)
);
$rp->addRoute(
	new RegexRoute('^(?P<area>users)/(?P<action>[^/]+)/(?P<username>[^/]+)/?$',
		array(
			'module'=>'admin',
			'controller'=>'users',
			'action'=>'view'
		)
	)
);
$rp->addRoute(
	new RegexRoute(
		'^(?P<area>help)(?:/(?P<action>[a-z_]+))?/?$',
		array(
			'module'=>'tactile',
			'controller'=>'help'
		)
	)
);
$rp->addRoute(
	new RegexRoute(
		'^(?P<area>welcome)/?$',
		array(
			'module'=>'tactile',
			'controller'=>'dashboard',
			'action'=>'welcome'
		)
	)
);
$rp->addRoute(
	new RegexRoute(
		'^(?P<area>form)/?$',
		array(
			'module'=>'public',
			'controller'=>'public',
			'action'=>'form'
		)
	)
);
$rp->addRoute(
	new RegexRoute(
		'^(?P<area>form_html)/?$',
		array(
			'module'=>'public',
			'controller'=>'public',
			'action'=>'form_html'
		)
	)
);
$areas=array(
	'files'=>array(
		'module'=>'tactile',
		'controller'=>'files'
	),	
	'import'=>array(
		'module'=>'tactile',
		'controller'=>'import'
	),
	'notes'=>array(
		'module'=>'tactile',
		'controller'=>'notes'
	),
	'tags'=>array(
		'module'=>'tactile',
		'controller'=>'tags'
	),
	'organisations'=>array(
			'module'=>'contacts',
			'controller'=>'organisations'
	),
	'people'=>array(
			'module'=>'contacts',
			'controller'=>'persons'
		),
	'opportunities'=>array(
			'module'=>'crm',
			'controller'=>'opportunitys'
		),
	'activities'=>array(
			'module'=>'crm',
			'controller'=>'activitys'
		),
	'users'=>array(
		'module'=>'admin',
		'controller'=>'users'
	),
	'groups'=>array(
		'module'=>'admin',
		'controller'=>'roles'
	),
	'setup'=>array(
		'module'=>'admin',
		'controller'=>'setup'
	),
	'customfields'=>array(
		'module'=>'admin',
		'controller'=>'customfields'
	),
	'account'=>array(
		'module'=>'admin',
		'controller'=>'account'
	),
	'suspension'=>array(
		'module'=>'suspension',
		'controller'=>'suspension'
	),
	'terms'=>array(
		'module'=>'tactile',
		'controller'=>'terms'
	),
	'public' => array(
		'module' => 'public',
		'controller' => 'public'
	),
	'graphs' => array(
		'module' => 'reports',
		'controller' => 'graphs'
	),
	'emails' => array(
		'module' => 'tactile',
		'controller' => 'emails'
	),
	'freshbooks' => array(
		'module' => 'admin',
		'controller' => 'freshbooks'
	),
	'xero' => array(
		'module' => 'admin',
		'controller' => 'xero'
	),
	'entanet' => array(
		'module' => 'admin',
		'controller' => 'entanet'
	),
	'voip' => array(
		'module' => 'tactile',
		'controller' => 'voip'
	),
	'api' => array(
		'module' => 'admin',
		'controller' => 'api'
	),
	'zendesk' => array(
		'module' => 'admin',
		'controller' => 'zendesk'
	),
	'appearance' => array(
		'module' => 'admin',
		'controller' => 'appearance'
	),
	'campaignmonitor' => array(
		'module' => 'admin',
		'controller' => 'campaignmonitor'
	),
	'templates' => array(
		'module' => 'admin',
		'controller' => 'templates'
	),
	'emailaddresses' => array(
		'module' => 'admin',
		'controller' => 'emailaddresses'
	),
	'webform' => array(
		'module' => 'admin',
		'controller' => 'webform'
	),
	'preferences' => array(
		'module' => 'tactile',
		'controller' => 'preferences'
	),
	'googleapps' => array(
		'module' => 'admin',
		'controller' => 'googleapps'
	),
	'permissions' => array(
		'module' => 'admin',
		'controller' => 'permissions'
	),
	'search' => array(
		'module' => 'tactile',
		'controller' => 'search'
	),
	'tracks' => array(
		'module' => 'admin',
		'controller' => 'tracks'
	),
);
foreach($areas as $area=>$defaults) {
	$rp->addRoute(new CRUDRoute($area,$defaults));
}

$webkey_whitelist = array(
	'public'	=> array(
		'public'		=> array('icalendar', 'timeline', 'fourohfour')
	)
);
$rp->setWebkeyRouteWhitelist($webkey_whitelist);

$api_whitelist = array(
	'login'		=> array(
		'index'			=> array('index')
	),
	'tactile'	=> array(
		'preferences'	=> array('index', 'options'),
		'notes'			=> array('save')
	),
	'contacts'	=> array(
		'organisations'	=> array(
			'index',
			'view',
			'contact_methods',
			'timeline',
			'mine',
			'options',
			'save',
			'save_contact',
			'save_note',
			'save_custom_multi',
			'add_tag',
			'list_all'
		),
		'persons'		=> array(
			'index',
			'view',
			'contact_methods',
			'timeline',
			'mine',
			'options',
			'save',
			'save_contact',
			'save_note',
			'save_custom_multi',
			'add_tag',
			'list_all'
		)
	),
	'crm' => array(
		'activitys' => array(
			'index',
			'view',
			'mine',
			'timeline',
			'options',
			'save',
			'save_note',
			'add_tag',
			'list_all'
		),
		'opportunitys'	=> array(
			'index',
			'view',
			'timeline',
			'mine',
			'recently_won',
			'recently_lost',
			'options',
			'save',
			'save_note',
			'add_tag',
			'list_all'
		)
	)
);
$rp->setApiRouteWhitelist($api_whitelist);
$rp->setPublicRouteWhitelist(array('public'=>array('public'=>array('form', 'form_html'))));

