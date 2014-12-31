<?php
require_once 'Zend/Rest/Client.php';

require_once 'Service/Zendesk/Entity/User.php';
require_once 'Service/Zendesk/Entity/Ticket.php';

require_once 'Service/Zendesk/Exception.php';

class Service_Zendesk extends Zend_Service_Abstract {
	const ROLE_END_USER = 0;
	const ROLE_ADMINISTRATOR = 2;
	const ROLE_AGENT = 4;
	
	const ENDPOINT = 'http://%s.zendesk.com';
	
	/** Details for connecting to the Zendesk account. */
	protected $_siteaddress, $_email, $_password;
	
	/**
	 * Constructs new Zendesk service object with account parameters.
	 *
	 * @param String $siteaddress The zendesk site-address (x.zendesk.com)
	 * @param string $email The email address for the user
	 * @param string $password The password for the user
	 */
	public function __construct($siteaddress, $email, $password) {
		$this->_siteaddress = $siteaddress;
		$this->_email = $email;
		$this->_password = $password;
	}
	
	private function _getUri($path = null) {
		$uri = sprintf(self::ENDPOINT, $this->_siteaddress);
		
		if (!is_null($uri)) $uri .= $path;
		
		return $uri;
	}
	
	private function _get($path, $query = null, $headers = null) {
		$hc = self::getHttpClient();
		
		if (!is_null($query))   $hc->setParameterGet($query);
		if (!is_null($headers)) $hc->setHeaders($headers);
		
		$hc->setAuth($this->_email, $this->_password);
		$hc->setUri($this->_getUri($path));
		
		return simplexml_load_string($hc->request()->getBody());
	}
	
	private function _buildQuery($params) {
		return array_filter(
			$params,
			create_function('$x', 'return !is_null($x);')
		);
	}
	
	public function users($query = null, $role = null) {
		return $this->_get(
			'/users.xml',
			$this->_buildQuery(
				array(
					'query' => $query,
					'role' => $role
				)
			)
		);
	}
	
	public function requests($for = null) {
		if (!is_null($for)) {
			$headers = array('X-On-Behalf-Of' => $for);
		} else {
			$headers = null;
		}
		
		return $this->_get(
			'/requests.xml',
			null,
			$headers
		);
	}
	
	public function search($query) {
		return $this->_get(
			'/search.xml',
			array(
				'query' => $query
			)
		);
	}
	
	public function credentials_valid() {
		$users = $this->users();
		
		if ($users->getName() == 'users') {
			return true;
		}
		
		return false;
	}
}
