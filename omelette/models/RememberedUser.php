<?php
class RememberedUser extends DataObject{
	
	
	public function __construct(){
		parent::__construct('remembered_users');
		$this->belongsTo('User');
		$this->getField('expires')->setDefault(date('Y-m-d H:i:s', strtotime('+14 days')));
	}
	
	/**
	 * Sets a cookie and adds a remembered_users entry for the given username (optionally re-using an ID)
	 * 
	 * @param String $username
	 * @param Int optional $id
	 * 
	 * @return Boolean
	 */
	public function rememberMe($username, $id=null) {
		$remembered_data = array('username' => $username, 'hash' => md5(mt_rand()));
		if($id !== null){
			$remembered_data['id'] = $id;
		}
		$errors = array();
		$remembereduser = DataObject::Factory($remembered_data, $errors, 'RememberedUser');
		if($remembereduser === false || $remembereduser->save() === false){
			return false;
		}
		$cookie_data[0] = $remembereduser->username;
		$cookie_data[1] = $remembereduser->hash;
		$cookie_data[2] = $remembereduser->id;
		$cookie_data = implode(':', $cookie_data);
		
		setcookie('TactileCookie', $cookie_data, strtotime('+14 days'), '/'); 
		return true;
	}
	
	/**
	 * Returns true iff there is a cookie set
	 * 
	 * @return Boolean
	 */
	public static function is_remembered(){
		return isset($_COOKIE['TactileCookie']);
	}
	
	/**
	 * Deletes the corresponding remembered_users entry for the current cookie
	 * - assumes is_remembered() has already been called
	 * 
	 * @return void
	 */
	public static function destroyMemory(){
		$cookie_data = explode(':', $_COOKIE['TactileCookie']);
		$memory = new Remembereduser();
		$memory->delete($cookie_data[2]);
		setcookie('TactileCookie', '', time() - 3600,'/');
	}
	
	/**
	 * Deletes all remembered_users entries for the given username
	 * 
	 * @param String $username
	 * @return Boolean
	 */
	public static function destroyAllMemories($username) {
		$db = DB::Instance();
		$query = 'DELETE FROM remembered_users WHERE username='.$db->qstr($username);
		return $db->Execute($query) !== false;
	}
}
?>