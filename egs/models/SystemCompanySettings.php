<?php
class SystemCompanySettings {
	const DAY_START_HOURS = '9';
	const DAY_START_MINUTES = '0';
	const DAY_LENGTH = '8';
	const _THEME = 'default';
	public static function get($var) {
		$key = EGS_COMPANY_ID.DB_NAME.$var;
		if(!HAS_APC||false===($return=apc_fetch($key))) {
			//if it's a constant, use that
			$c_var = 'self::'.$var;
			if(defined($c_var)) {
				$return= constant($c_var);
			}
			else {
				//check for a db-field corresponding to the value. and use that
				$sc = new Systemcompany();
				if(EGS_COMPANY_ID!=='null'&&$sc->isField($var)) {
					
					$res=$sc->loadBy('organisation_id',EGS_COMPANY_ID);
					
					$return = $sc->$var;
				}
				else {
					//_ indicates a default for a DB-value
					$c_var='self::_'.$var;
					if(defined($c_var)) {
						$return = constant($c_var);
					}
				}
			}
			if(HAS_APC) {
				apc_store($key,$return);
			}
		}
		return $return;
	}
	
}
?>
