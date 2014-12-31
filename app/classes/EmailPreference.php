<?php

/**
 *
 */
class EmailPreference {

	protected static $defaults = array(
		'activity_reminder'=>true,
		'activity_notification'=>true,
		'missing_contact_email'=>true
	);
	
	public static function getAll($username) {
		$db = DB::Instance();
		$query = 'SELECT mail_name, send FROM email_preferences WHERE owner='.$db->qstr($username);
		$list = $db->GetAssoc($query);
		$list = array_map(create_function('$a', 'return $a=="t"?true:false;'), $list);
		return array_merge(self::$defaults, $list);		
	}
	
	public static function setAll($prefs, $username) {
		$db = DB::Instance();
		$db->StartTrans();
		$base = array('owner'=>$username, 'lastupdated'=>'now()');
		foreach($prefs as $name=>$value) {
			$data = array_merge($base, array('mail_name'=>$name, 'send'=>($value=='yes')?'t':'f'));
			$success = $db->Replace('email_preferences', $data, array('owner','mail_name'), true);
			if($success===false) {
				$db->FailTrans();
				return false;
			}
		}
		return $db->CompleteTrans();
	}
	
	public static function getSendStatus($mail_name, $username) {
		$db = DB::Instance();
		$query = 'SELECT send FROM email_preferences 
			WHERE owner = '.$db->qstr($username).' AND mail_name='.$db->qstr($mail_name);
		$result = $db->GetOne($query);
		if($result === false) {
			return self::$defaults[$mail_name];
		}
		return $result=='t';
	}
	
}

?>
