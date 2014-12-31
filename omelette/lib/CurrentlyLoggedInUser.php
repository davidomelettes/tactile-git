<?php
/**
 * Singleton class representing the currently-loggedin user
 * 
 * @author gj
 * @package Omelette
 */
class CurrentlyLoggedInUser {

	protected static $_accountClassName = 'OmeletteAccount';
	
	/**
	 * The 'User' model that is the CLU
	 *
	 * @var Omelette_User
	 */
	private $user;
	
	/**
	 * The user's username, minus the 'userspace' part
	 *
	 * @var String
	 */
	private $username;
	
	/**
	 * The user's userspace- the bit after their username and //
	 *
	 * @var String
	 */
	private $userspace;
	
	/**
	 * The user's full username, including the userspace part
	 *
	 * @var String
	 */
	private $raw_username;
	
	private $is_owner;
	
	private static $static_user;
		
	private $organisation_id;	

	/**
	 * Whether the user logged in via HTTPS or not
	 *
	 * @var boolean
	 */
	private $_using_https = false;
	
	public function __construct($username) {
		$this->user = DataObject::Construct('User');
		$db = DB::Instance();
		$query = 'SELECT u.*, org.id AS organisation_id FROM users u JOIN user_company_access uca ON (u.username=uca.username) JOIN organisations org ON (org.id=uca.organisation_id) WHERE u.username='.$db->qstr($username);
		$row = $db->getRow($query);
		if (empty($row)) {
			throw new Exception('No user returned!');
		}
		
		// Are we using HTTPS?
		if (isset($_SERVER['HTTP_X_FARM'])) {
			$this->setUsingHttps(TRUE);
		}
		
		$this->organisation_id = $row['organisation_id'];
		unset($row['organisation_id']);
		$this->user->_data = $row;
		$this->user->load($username);
		list($this->username,$this->userspace) = explode('//',$username);
		$this->raw_username = $username;
		
		if(isset($_SESSION['is_owner']) && $_SESSION['is_owner']==true) {
			$this->is_owner = true;
		}
	}
	
	public function getUserCompanyID() {
		return $this->organisation_id;
	}
	
	/**
	 * Returns the Currently logged in User - there can be only one!
	 * @return CurrentlyLoggedInUser
	 */
	public static function Instance() {
		require_once 'Zend/Auth.php';
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity()) {
			self::$static_user=null;
			return false; 
		}		
		if(self::$static_user==null || self::$static_user->getRawUsername() !== $auth->getIdentity()) {
			self::$static_user = new CurrentlyLoggedInUser($auth->getIdentity());
		}
		return self::$static_user;
	}
	
	/**
	 * Creates a Currently Logged In User to satisfy access controls,
	 * but does not store the username in the session.
	 * This should allow access to a single page.
	 *
	 * @param string $key
	 * @return CurrentlyLoggedInUser
	 */
	public static function TemporaryInstanceByKey($key) {
		$db = DB::Instance();
		$query = "SELECT u.username FROM users u WHERE u.webkey = " . $db->qstr($key);
		if (FALSE !== ($username = $db->getOne($query))) {
			self::$static_user = new CurrentlyLoggedInUser($username);
			return self::$static_user;
		} else {
			return FALSE;
		}
	}
	
	public static function clear() {
		self::$static_user = null;
	}
	
	public function __get($var) {
		return $this->user->$var;
	}
	
	/**
	 * Return the user DO
	 * @return Omelette_User
	 */
	public function getModel() {
		return $this->user;
	}
	
	/**
	 * Add a record of a viewed-page to the user's list
	 * 
	 * @param ViewedPage $page
	 */
	public function addViewedPage(ViewedPage $page) {
		$pl = RecentlyViewedPageList::LoadForUser($this);
		$pl->addPage($page);
		$result = $pl->save();
		
	}
	
	public static function getUserRole() {
		$user = CurrentlyLoggedInUser::Instance();
		return Omelette_User::getUserRole($user->getModel());
	}
	
	public function getOrganisationName() {
		$db = DB::Instance();
		$query = 'SELECT name FROM organisations org JOIN user_company_access uca ON (org.id=uca.organisation_id)
					 WHERE uca.username='.$db->qstr($this->getRawUsername());
		$company = $db->GetOne($query);
		return $company;
	}
	
	public function getPersonFirstName() {
		$db = DB::Instance();
		$query = 'SELECT p.firstname FROM people p LEFT JOIN users u ON (u.person_id=p.id) WHERE u.username=' . $db->qstr($this->getRawUsername()) .' AND p.usercompanyid='.$db->qstr(EGS_COMPANY_ID);
		
		$first_name = $db->GetOne($query);
		return $first_name;
	}
	
	public function isAdmin() {
		return $this->user->is_admin == 't';
	}
	
	public function isBeta() {
		return $this->user->beta == 't';
	}
	
	/**
	 * Returns true iff the user is the person who owns the account
	 *
	 * @return Boolean
	 */
	public function isAccountOwner() {
		if(!isset($this->is_owner)) {
			$account = $this->getAccount();
			$this->is_owner = $this->getRawUsername() === $account->username.'//'.Omelette::getUserSpace();
			$_SESSION['is_owner'] = $this->is_owner;
		}
		return $this->is_owner;
	}
	
	/**
	 * Gets the account of the currently logged in user
	 *
	 * @return TactileAccount
	 */
	public function getAccount() {
		return Omelette::getAccount();
	}
	
	public function getAccountPlan() {
		return Omelette::getAccountPlan();
	}
	
	public function getAccountPlanName() {
		$plan = $this->getAccountPlan();
		return $plan->name;
	}
	
	public function getUserspace() {
		return Omelette::getUserspace();
	}
	
	public function isEnabled() {
		return $this->user->enabled == 't';
	}
	
	public function isResolveEnabled() {
		return $this->user->resolve_enabled == 't';
	}
	
	/**
	 * Returns the username, not including the user-space part
	 * @return String
	 */
	public function getUsername() {
		return $this->username;
	}
	
	/**
	 * Returns the username, including the user-space part
	 * 
	 * @return String
	 */
	public function getRawUsername() {
		return $this->raw_username;
	}
	
	/**
	 * Return's the user's timezone string (e.g. 'Europe/London')
	 * - currently hardcoded to return 'Europe/London'
	 * 
	 * @return String
	 */
	public function getTimezoneString() {
		//return 'Asia/Vladivostok';
		//return 'Australia/Darwin';
		//return 'Atlantic/Reykjavik';
		return $this->user->timezone;
	}
	
	/**
	 * Returns true iff the 'account' for the tenant is active
	 *
	 * @return Boolean
	 */
	public function hasValidAccount() {
		if(!isset($_SESSION['checked_active']) || $_SESSION['checked_active']!=date('YmdH')) {
			$account = $this->getAccount();
						
			if(!$account->is_enabled()) {
				return false;
			}
			$_SESSION['checked_active'] = date('YmdH');
		}
		return true;		
	}
	
	public function hasAgreedToTerms() {
		return !is_null($this->user->terms_agreed);
	}
	
	public function getDropboxAddress($action='dropbox') {
		$key = $this->user->dropboxkey;
		if (empty($key)) {
			return false;
		}
		$email = sprintf('%s@%s.%s.mail.tactilecrm.com', $action, $key, $this->userspace);
		return $email;
	}
	
	public function setUsingHttps($https) {
		if (FALSE === $https) {
			$this->_using_https = FALSE;
		} elseif (TRUE === $https) {
			 $this->_using_https = TRUE;
		} else {
			throw new Exception('Not a boolean value for _using_https!');
		}
	}
	
	public function usingHttps() {
		return $this->_using_https;
	}
	
	public function getCalendarAddress() {
		$key = $this->user->webkey;
		if (empty($key)) {
			return false;
		}
		$calendar = ($this->usingHttps() ? 'https' : 'http') .
			'://'.$_SERVER['HTTP_HOST'].'/public/icalendar/?key=' . $key;
		return $calendar;
	}
	
	public function getTimelineFeedAddress() {
		$key = $this->user->webkey;
		if (empty($key)) {
			return false;
		}
		$feed = ($this->usingHttps() ? 'https' : 'http') .
			'://'.$_SERVER['HTTP_HOST'].'/public/timeline/?key=' . $key;
		return $feed;
	}
	
	public function getApiToken() {
		$token = $this->user->api_token;
		if (empty($token)) {
			return false;
		}
		return $token;
	}
	
	public function canDelete($model) {
		if ($model instanceof Omelette_User) {
			return false;
		}
		return $this->isAdmin() || $model->canDelete();
	}
	
	public function canEdit($model) {
		return $this->isAdmin() || $model->canEdit();
	}
		
	
	public static function setAccountClassName($name) {
		self::$_accountClassName = $name;
	}
	
	public function can_click_to_dial() {
		$ext = $this->getModel()->getEntanetExtension();
		return !empty($ext);
	}
	
	public function isStaffUser() {
		return $this->user->hasRole(Omelette::getUserSpaceRole());
	}
	
	public function getResolveEmailAddress() {
		return 'support@' . $this->getAccount()->site_address . '.resolverm.com';
	}
	
}
