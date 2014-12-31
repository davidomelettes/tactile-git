<?php

class RedirectHandler implements Redirection{

	public function Redirect() {
		$args=func_get_args();
		$arg_array=array('controller','action','module','other');
		if(is_array($args[0]))
			$args=$args[0];
		foreach($args as $i=>$arg) {
			${$arg_array[$i]}=$arg;
		}
		Flash::Instance()->save();
		$othercat = '';
		if (!(empty($other))) {
			foreach($other as $key=>$value) {
				$othercat .= '&'.$key.'='.$value;
			}
		}
		if(isset($module)&&is_array($module))
		{
			$mod='';
			$prefix='module=';
			foreach($module as $m)
			{
				$mod.=$prefix.$m."&";
				$prefix='sub'.$prefix;
			}
			$module = substr($mod,7,-1);
		}
		$location = ((!empty($module))?'module='.$module:'')."&".((!empty($controller))?'controller='.$controller:'').((!empty($controller)&&!empty($action))?'&':'').((!empty($action))?'action='.$action:'').$othercat;
		if ($location[0] == '&')
			$location = substr($location,1);
		header('Location: '.SERVER_ROOT.((!empty($location))?'/?'.$location:''));
		exit;

	}

}

?>
