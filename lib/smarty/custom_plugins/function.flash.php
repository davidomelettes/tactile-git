<?php

function smarty_function_flash($params,&$smarty) {
		$flash=Flash::Instance();
		$messages='';
		$html='<div id="flash">';
		$dismiss = !empty($params['dismiss']) ? '<li class="dismiss"><a>'.$params['dismiss'].'</a></li>' : '';
		foreach($flash->messages as $message) {
			$messages.='<li>'.$message.'</li>';
		}
		if($messages!='') {
			$html.='<ul id="messages">'.$dismiss.$messages.'</ul>';
			$smarty->assign('highlight', 'info');
		}
		$errors='';
		foreach($flash->errors as $fieldname=>$error) {
			$errors.='<li id="error_'.$fieldname.'">'.$error.'</li>';
		}
		if($errors!='') {
			 
			$html.='<ul id="errors">'.$dismiss.$errors.'</ul>';
			$smarty->assign('highlight', 'errors');
		}
		$html.='</div>';
		//$type=($errors!='')?'error':'success';
		$smarty->assign('flash', $html);
		
}
