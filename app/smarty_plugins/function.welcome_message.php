<?php
function smarty_function_welcome_message($params, $smarty) {
	$hidden = Omelette_Magic::getAsBoolean(
		'hide_welcome_message',
		CurrentlyLoggedInUser::Instance()->getRawUsername()
	);
	if(!$hidden) {
		$smarty->display('elements/welcome_tab.tpl');
	}
}
?>