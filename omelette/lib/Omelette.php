<?php
class Omelette {
	private static $userspace;
	private static $account;
	private static $account_plan;
	
	private static $translations = array(
		'contacts'=>array(
			'companys'=>'organisations',
			'persons'=>'people'
		),
		'crm'=>array(
			'opportunitys'=>'opportunities',
			'activitys'=>'activities'
		),
		'admin'=>array(
			'roles'=>'groups',
			'users'=>'users',
			'templates'=>'templates'
		),
		'tactile'=>array(
			'preferences'=>'preferences'
		)
	);
	
	private static $is_api = false;
	
	static public $selfUrl = null;
	
	public static function setIsApi($boolean) {
		self::$is_api = $boolean;
	}
	
	public static function isApi() {
		return self::$is_api;
	}
	
	/**
	 * Returns a pretty url given an egs-style module/controller/action
	 * 
	 * @param String $module
	 * @param String $controller
	 * @param String $action
	 * @return String
	 */
	public static function getUrl($module,$controller,$action) {
		$func = array(APP_NAME,'getURL');
		if(is_callable($func)) {
			$override = call_user_func($func,$module,$controller,$action);
			if($override!==false) {
				return $override;
			}
		}
		if(isset(self::$translations[$module][$controller])) {
			$url = self::$translations[$module][$controller].'/'.$action.'/';
			return $url;
		}
		return (!empty($module)?$module.'/':'').
			(!empty($controller)?$controller.'/':'').
			(!empty($action)?$action.'/':'');
	}
	
	/**
	 * Returns, and if necessary creates, the 'everyone' role for the current User-space
	 * 
	 * @return Role
	 */
	public static function getUserSpaceRole() {
		static $cache_role;
		static $cache_userspace;
		if ($cache_role == null || $cache_userspace !== Omelette::getUserSpace()) {
			$role = DataObject::Construct('Role');
			$exists=$role->loadBy('name','//'.Omelette::getUserSpace());
			if($exists===false) {
				$role_data=array('name'=>'//'.Omelette::getUserSpace());
				$errors=array();
				$role = DataObject::Factory($role_data,$errors,'Role');
				if($role===false) {
					print_r($errors);echo "boo";exit;
				}
				$role->save();
			}
			$cache_role = $role;
			$cache_userspace = Omelette::getUserSpace();
		}
		return $cache_role;
	}
	
	/**
	 * Set the current user-space
	 * @param String $space
	 */
	public static function setUserSpace($space) {
		self::$userspace = $space;
	}
	
	/**
	 * Returns the currently set user-space
	 * @throws Exception
	 * @return String
	 */
	public static function getUserSpace() {
		if(!isset(self::$userspace)) {
			throw new Exception('Userspace not set');
		}
		return self::$userspace;
	}

	public static function setAccount($account) {
		if ($account instanceof OmeletteAccount) {
			self::$account = $account;
		} else {
			$aaccount = new TactileAccount();
			if (FALSE === $aaccount->load($account)) {
				throw new Exception('Failed to load TactileAccount!');
			} else {
				self::$account = $aaccount;
			}
		}
	}
	
	public static function getAccount() {
		if (!isset(self::$account)) {
			$user = CurrentlyLoggedInUser::Instance();
			$account = new TactileAccount();
			$cc = new ConstraintChain();
			$cc->add(new Constraint('organisation_id','=',EGS::getCompanyId()));
			$account = $account->loadBy($cc);
			self::setAccount($account);
		}
		return self::$account;
	}
	
	public static function setAccountPlan($plan) {
		if ($plan instanceof AccountPlan) {
			self::$account_plan = $plan;
		} else {
			$aplan = new AccountPlan();
			if (FALSE === $aplan->load($plan)) {
				throw new Exception('Failed to load AccountPlan!');
			} else {
				self::$account_plan = $aplan;
			}
		}
	}
	
	public static function getAccountPlan() {
		if (!isset(self::$account_plan)) {
			self::setAccountPlan(self::getAccount()->current_plan_id);
		}
		return self::$account_plan;
	}
	
	public static function getPublicIdentity() {
		$db = DB::Instance();
		$query = "SELECT username FROM tactile_accounts WHERE site_address = " . $db->qstr(self::getUserSpace());
		$username = $db->getOne($query); 
		return ($username === FALSE ? FALSE : ($username . '//' . self::getUserSpace()));
	}
	
	static public function isHttps() {
		$port = 80;
		if (isset($_SERVER['HTTP_HOST'])) {
            if (($pos = strpos($_SERVER['HTTP_HOST'], ':')) === false) {
                if (isset($_SERVER['SERVER_PORT'])) {
                    $port = ':' . $_SERVER['SERVER_PORT'];
                }
            } else {
                $port = substr($_SERVER['HTTP_HOST'], $pos);
            }
        } else if (isset($_SERVER['SERVER_NAME'])) {
            $url = $_SERVER['SERVER_NAME'];
            if (isset($_SERVER['SERVER_PORT'])) {
                $port = ':' . $_SERVER['SERVER_PORT'];
            }
        }
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            if ($port == ':443') {
                $port = '';
            }
        } else {
            if ($port == ':80') {
                $port = '';
            }
        }
        return $port == '443';
	}
	
	static public function selfUrl()
    {
        if (self::$selfUrl !== null) {
            return self::$selfUrl;
        } if (isset($_SERVER['SCRIPT_URI'])) {
            return $_SERVER['SCRIPT_URI'];
        }
        $url = '';
        $port = '';
        if (isset($_SERVER['HTTP_HOST'])) {
            if (($pos = strpos($_SERVER['HTTP_HOST'], ':')) === false) {
                if (isset($_SERVER['SERVER_PORT'])) {
                    $port = ':' . $_SERVER['SERVER_PORT'];
                }
                $url = $_SERVER['HTTP_HOST'];
            } else {
                $url = substr($_SERVER['HTTP_HOST'], 0, $pos);
                $port = substr($_SERVER['HTTP_HOST'], $pos);
            }
        } else if (isset($_SERVER['SERVER_NAME'])) {
            $url = $_SERVER['SERVER_NAME'];
            if (isset($_SERVER['SERVER_PORT'])) {
                $port = ':' . $_SERVER['SERVER_PORT'];
            }
        }
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $url = 'https://' . $url;
            if ($port == ':443') {
                $port = '';
            }
        } else {
            $url = 'http://' . $url;
            if ($port == ':80') {
                $port = '';
            }
        }

        $url .= $port;
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $url .= $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $query = strpos($_SERVER['REQUEST_URI'], '?');
            if ($query === false) {
                $url .= $_SERVER['REQUEST_URI'];
            } else {
                $url .= substr($_SERVER['REQUEST_URI'], 0, $query);
            }
        } else if (isset($_SERVER['SCRIPT_URL'])) {
            $url .= $_SERVER['SCRIPT_URL'];
        } else if (isset($_SERVER['REDIRECT_URL'])) {
            $url .= $_SERVER['REDIRECT_URL'];
        } else if (isset($_SERVER['PHP_SELF'])) {
            $url .= $_SERVER['PHP_SELF'];
        } else if (isset($_SERVER['SCRIPT_NAME'])) {
            $url .= $_SERVER['SCRIPT_NAME'];
            if (isset($_SERVER['PATH_INFO'])) {
                $url .= $_SERVER['PATH_INFO'];
            }
        }
        return $url;
    }
}
