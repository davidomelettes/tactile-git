<?php
/**
 * Base class for calls to the SecPay XMLRPC API
 * 
 * @author gj
 * @package Payment
 */
abstract class SecPayRequest_Abstract {
	
	/**
	 * The URL the request should go to
	 * @access private
	 * @static String
	 */
	protected $url = 'https://www.secpay.com/secxmlrpc/make_call';
	
	/**
	 * An optional default instance of Zend_XmlRpc_Client to use for requests
	 *
	 * @var Zend_XmlRpc_Client
	 */
	protected static $default_client;
	
	/**
	 * An array of parameters
	 * @access private
	 * @var array
	 */
	protected $params = array();
	
	/**
	 * The XMLRPC client instance
	 *
	 * @var Zend_XmlRpc_Client
	 */
	protected $client;
	
	/**
	 * The secpay username (normally 6 letters + 2 numbers)
	 */
	protected $MID = 0;
	
	/**
	 * The VPN password (set in 'Change Remote Passwords')
	 */
	protected $VPN_PSWD = 1;
	
	/**
	 * A unique id created by us (client), can be used for refunds and repeats
	 */
	protected $TRANS_ID = 2;
	
//constants for the various 'repeat' options
	/**
	 * The Secpay term for 'monthly' repeats
	 */
	const REPEAT_MONTHLY = 'monthly';
	
	/**
	 * The Secpay term for 'forever'
	 */
	const REPEAT_FOREVER = -1;
	
//constants for the various 'deferred' options
	/**
	 * The Secpay term for 'single currency unit' deferred payment
	 * - using this option causes a new authorisation upon release
	 */
	const DEFERRED_SINGLE_UNIT = 'true';
	
	/**
	 * The Secpay term for 'full' deferred payment
	 * - using this option causes a new authorisation upon release
	 */
	const DEFERRED_FULL = 'full';
	
	/**
	 * The Secpay term for the 'reuse' deferred option
	 * - using this option uses the same auth-code as the initial request
	 */
	const DEFERRED_REUSE = 'reuse';
	
	
//adding constants here to avoid typos
	/**
	 * The 'key' for the deferred option (for values see DEFERRED_ consts above)
	 */
	const OPTION_DEFERRED = 'deferred';
	
	/**
	 * The 'key' for the cv2 option (a 3-digit number)
	 */
	const OPTION_CV2 = 'cv2';
	
	/**
	 * The 'key' for the card_type option (a string, see PaymentCard for values)
	 */
	const OPTION_CARD_TYPE = 'card_type';
	
	
	/**
	 * A Zend_Log instance, notified of things when a request is made
	 *
	 * @var Zend_Log
	 */
	protected $logger;
	
	/**
	 * Constructor
	 * Provide the instance with the MID and the VPN Password
	 * @param String $mid
	 * @param String $vpn_password
	 */
	public function __construct($mid=null, $vpn_password=null) {
		$this->params=array();
		for($i=0;$i<$this->NUM_PARAMS;$i++) {
			$this->params[$i] = '';
		}
		if($mid!==null) {
			$this->setMID($mid);
		}
		if($vpn_password!==null) {
			$this->setVPNPassword($vpn_password);
		}
	}
	
	public function getValue($key) {
		$key = strtoupper($key);
		if(!isset($this->$key)) {
			throw new Exception("Value for $key doesn't exist in this type of transaction");
		}
		return $this->params[$this->$key];
	}
	
	/**
	 * Pass in a Zend_Log instance that will be notified of things when requests are made
	 *
	 * @param Zend_Log $logger
	 */
	public function setLogger(Zend_Log $logger) {
		$this->logger = $logger;
	}
	
	/**
	 * Set the value of the MID (the secpay account name)
	 * 
	 * @param String $mid
	 * @return void
	 */
	public function setMID($mid) {
		$this->params[$this->MID] = $mid;
	}
	
	/**
	 * Set the value of the VPN password
	 * 
	 * @param String $password
	 * @return void
	 */
	public function setVPNPassword($password) {
		$this->params[$this->VPN_PSWD] = $password;
	}
	
	/**
	 * Set the value of the transaction id
	 * 
	 * @param String $trans_id
	 * @return void
	 */
	public function setTransId($trans_id) {
		$this->params[$this->TRANS_ID] = $trans_id;
	}
	
	
	/**
	 * Set the amount of the transaction (not accepted by all methods)
	 * This is cast to a string, as that's what the endpoint expects, and ints can slip in
	 * 
	 * @param String $amount
	 * @return void
	 * @throws SecPay_InvalidOptionException
	 */
	public function setAmount($amount) {
		if(!isset($this->AMOUNT)) {
			throw new SecPay_InvalidOptionException('This method doesn\'t have an amount field');
		}
		$this->params[$this->AMOUNT] = (string)$amount;
	}
	
	/**
	 * Set the remote password for the transaction.
	 * Only (seems to be) used by transactions that modify a previous transaction (release, refund)
	 *
	 * @param String $password
	 * @return void
	 * @throws SecPay_InvalidOptionException
	 */
	public function setRemotePswd($password) {
		if(!isset($this->REMOTE_PSWD)) {
			throw new SecPay_InvalidOptionException('This method doesn\'t have a remote_pswd field');
		}
		$this->params[$this->REMOTE_PSWD] = $password;
	}
	
	/**
	 * Set the id for the new transaction that is created by this one
	 * used by release and refund
	 *
	 * @param String $new_trans_id
	 * @return void
	 * @throws SecPay_InvalidOptionException
	 */
	public function setNewTransId($new_trans_id) {
		if(!isset($this->NEW_TRANS_ID)) {
			throw new SecPay_InvalidOptionException('This method doesn\'t have a new_trans_id field');
		}
		$this->params[$this->NEW_TRANS_ID] = $new_trans_id;
	}
	
	/**
	 * Set the expiry date- this is used by repeats as well as initial transactions
	 *
	 * @param String $expiry
	 * @return void
	 * @throws SecPay_InvalidOptionException
	 */
	public function setCardExpiry($expiry) {
		if(!isset($this->EXP_DATE)) {
			throw new SecPay_InvalidOptionException('This method doesn\'t have am expiry_date field');
		}
		$this->params[$this->EXP_DATE] = $expiry;
	}
	
	/**
	 * Set the customer's IP
	 * 
	 * @param String $ip_address
	 * @return void
	 */
	public function setCustomerIP($ip_address) {
		if(!isset($this->IP)) {
			throw new SecPay_InvalidOptionException('This method doesn\'t have am IP field');
		}
		$this->params[$this->IP] = $ip_address;
	}
	
	/**
	 * Take a PaymentCard object and grab the useful bits
	 * The PaymentCard should have been validated
	 * 
	 * @param PaymentCard $card
	 * @return void
	 */
	public function addPaymentCard(PaymentCard $card) {
		$this->setCardNumber($card->getCardNumber());
		$this->setCardExpiry($card->getExpiry());
		$this->setCardName($card->getCardholderName());
		$this->setCardType($card->getCardType());
		$this->setCV2($card->getCV2());
		$this->setBillingAddress($card->getAddress());
	}
	
	/**
	 * Set the customer's card number
	 * 
	 * @param String $card_number
	 * @return void
	 */
	public function setCardNumber($card_number) {
		if(!isset($this->CARD_NUMBER)) {
			throw new SecPay_InvalidOptionException('This method doesn\'t have a card_number field');
		}
		$number = preg_replace('#[^0-9]#','',$card_number);
		$this->params[$this->CARD_NUMBER] = $number;
	}
	
	/**
	 * Set the card's CV2 number
	 * Note: the req_cv2 option doesn't have any effect for 'API customers'
	 * 
	 * @param String $cv2
	 * @return void
	 */
	public function setCV2($cv2) {
		$this->addOption(self::OPTION_CV2,$cv2);
	}

	
	/**
	 * Set the card-holder's name
	 * 
	 * @param String $name
	 * @retur void
	 */
	public function setCardName($name) {
		if(!isset($this->NAME)) {
			throw new SecPay_InvalidOptionException('This method doesn\'t have a card-name field');
		}
		$this->params[$this->NAME] = $name;
	}
	
	/**
	 * Set the card type
	 * Card type is part of the 'options' parameter, should be one of 'Visa', 'Master Card' or 'Switch'
	 * 
	 * @param String $type
	 * @return void
	 */
	public function setCardType($type) {
		$this->addOption(self::OPTION_CARD_TYPE,$type);
	}
	
	/**
	 * Sets the digest part of the request
	 * Secpay will only test this if you ask them to (email: admin@secpay.com requesting req_digest=true)
	 * 
	 * @param String $remote_password
	 * @return void
	 */
	public function setDigest($remote_password) {
		$digest = md5($this->params[$this->TRANS_ID].$this->params[$this->AMOUNT].$remote_password);
		$this->addOption('digest',$digest);
	}
	
	/**
	 * Set the 'repeat' options for a transaction
	 * @param Date $start A Date in Ymd format (20070812)
	 * @param String $frequency How often the payment should be taken
	 * @param Int Occurences How many times to take payment
	 */
	public function setRepeat($start,$frequency,$occurences,$callback) {
		$this->addOption('repeat',$start.'/'.$frequency.'/'.$occurences);
		$this->addOption('repeat_callback',urlencode(SERVER_ROOT.$callback));
		$this->addOption('usage_type','R');
	}
	
	/**
	 * Set the transaction to be deferred
	 * - see https://www.secpay.com/xmlrpc/deferredTransaction.html
	 * 
	 * @param String $type
	 * @param Int $credit_card_valid
	 * @param Int $debit_card_valid
	 * @return void
	 */
	public function setDeferred($type,$credit_card_valid=7,$debit_card_valid=1) {
		switch($type) {
			case self::DEFERRED_SINGLE_UNIT: //fall through
			case self::DEFERRED_FULL :
				$this->addOption(self::OPTION_DEFERRED,$type);
				break;
			case self::DEFERRED_REUSE:
				$val = self::DEFERRED_REUSE . ':' . $credit_card_valid . ':' . $debit_card_valid;
				$this->addOption(self::OPTION_DEFERRED,$val);
				break;
			default:
				throw new Exception('Invalid option for "deferred"');
		}
	}
	
	/**
	 * Set the test-status of the transaction
	 * test_status is part of the 'options' parameter, is turned to a string during the concatenation there
	 *
	 * @param Boolean $test_status
	 * @return void
	 */
	public function setTest($test_status) {
		if($test_status === true) {
			$test_status = 'true';
		}
		if($test_status === false) {
			$test_status === 'false'; //this doesn't mean live!
		}
		$this->addOption('test_status',$test_status);
		if($test_status!=='live') {
			$this->addOption('dups','false');
			$this->addOption('mail_merchants',':');
		}
	}
	
	/**
	 * Sets the billing address part of the xml
	 * 
	 * @param Array $address_details
	 * @return void
	 */
	private function setBillingAddress($address_details) {
		$dom = new DOMDocument();
		//we do this so ->asXML() at the end doesn't include the prologue
		$address = $dom->createElement('address');
		$billing = $dom->createElement('billing');
		$billing->setAttribute('class','com.secpay.seccard.Address');
		$dom->appendChild($address);
		$address->appendChild($billing);
		foreach($address_details as $key=>$value) {
			$billing->appendChild($dom->createElement($key,urlencode($value)));
		}
		$xml = simplexml_import_dom($dom);
		$this->params[$this->BILLING] = $xml->billing->asXML();
	}
	
	/**
	 * Adds a key-value pair to the 'options' paramater, which is in a query-string format
	 * 
	 * @param String $key
	 * @param String $value
	 * @return void
	 */
	protected function addOption($key,$value) {
		if(!isset($this->OPTIONS)) {
			throw new SecPay_InvalidOptionException('This method doesn\'t have an "options" field');
		}
		if(!empty($this->params[$this->OPTIONS])) {
			$this->params[$this->OPTIONS].=',';
		}
		$this->params[$this->OPTIONS].=urlencode($key).'='.$value;
	}
	
	/**
	 * Allow over-riding of the default client, anything that meets the interface will do
	 *
	 * @param Zend_Http_Client $client
	 */
	public function setClient(Zend_XmlRpc_Client $client) {
		$this->client = $client;
	}
	
	/**
	 * Set a default instance of Zend_XmlRpc_Client to use
	 *
	 * @param Zend_XmlRpc_Client $client
	 */
	public static function setDefaultClient(Zend_XmlRpc_Client $client) {
		self::$default_client = $client;
	}
	
	/**
	 * Does the setup of the XMLRPC client
	 * @see self::setClient() if you want to over-ride
	 * @return void
	 */
	private function setupClient() {
		if(isset(self::$default_client)) {
			$this->client = self::$default_client;
		}
		elseif(!isset($this->client)) {
			require_once 'Zend/XmlRpc/Client.php';
			require_once 'Zend/Http/Client/Adapter/Socket.php';
			
			//tell the socket to use sslv3 (2 seems not to work)
			$socket = new Zend_Http_Client_Adapter_Socket();
			$socket->setConfig(array('ssltransport'=>'sslv3'));
	
			//then tell the Http_Client to use the socket...
			$http_client = new Zend_Http_Client();
			$http_client->setAdapter($socket);
		
			//then tell the XmlRpc_Client to use the Http_Client
			$this->client = new Zend_XmlRpc_Client($this->url);
			$this->client->setHttpClient($http_client);
		}
	}
	
	/**
	 * Perform's the function call
	 * 
	 * @return SecPaySyncResponse
	 */
	public function send() {
		$this->setupClient();
		try {
			$this->client->call($this->method,$this->params);
		}
		catch(Exception $e) {
			if(!is_null($this->logger)) {
				$this->logger->crit($e->getMessage());
				$this->logger->crit($e->getTraceAsString());
			}
			return false;
		}
		$response = $this->client->getLastResponse();
		$fp = fopen('/tmp/response'.time().'.xml','w+');
		fwrite($fp, $response->getReturnValue());
		fclose($fp);
		//turns the querystring into an assoc array- want to strip the ? from the front:
		parse_str(substr($response->getReturnValue(),1),$response_details);
		
		$secpay_response = new SecPaySyncResponse($response_details);
		return $secpay_response;
	}
	
	/**
	 * Returns the response from the Xml_Rpc_Client
	 *
	 * @return Zend_XmlRpc_Response
	 */
	function getRawResponse() {
		return $this->client->getLastResponse();
	}
	
	/**
	 * Returns the request object
	 *
	 * @return Zend_XmlRpc_Request
	 */
	function getRawRequest() {
		return $this->client->getLastRequest();
	}
}
?>