<?php
class OmeletteLinkBuilder implements LinkBuilding {
	
	public function build($params,$data=false) {
		$string='';
		$attrs='';
		if(isset($params['value'])) {
			$value=$params['value'];
			unset($params['value']);
		}
		$url = Omelette::getUrl($params['module'],$params['controller'],$params['action']);

		unset($params['module']);
		unset($params['controller']);
		unset($params['action']);
		$extra='';
		foreach($params as $key=>$val) {
			if(substr($key,0,1)==='_') {
				$attrs.=str_replace('_','',$key).'="'.$val.'" ';
				continue;
			}
			$extra.=$key.'='.$val.'&';
		}
		if($extra!='') {
			$extra = '?'.rtrim($extra,'&');
		}
		return '<a '.$attrs.' href="/'.$url.$extra.'">'.$value.'</a>';
	}
	
}
?>