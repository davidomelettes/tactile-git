<?php
/**
 * Responsible for carrying out a request to SecPay for a transaction
 * @author gj
 * @package Payment
 */
class SecPayRequest {
	
	/**
	 * The URL the request should go to
	 * @access private
	 * @static String
	 */
	private static $url = 'https://www.secpay.com/secxmlrpc/make_call';
	
	/**
	 * The method-name used for the request
	 * @access private
	 * @static String
	 */
	private static $method_name = 'SECVPN.validateCardFull';
	
	/**
	 * The secpay username (normally 6 letters + 2 numbers)
	 */
	const MID = 0;
	
	/**
	 * The VPN password (set in 'Change Remote Passwords')
	 */
	const VPN_PSWD = 1;
	
	/**
	 * A unique id created by us (client), can be used for refunds and repeats
	 */
	const TRANS_ID = 2;
	
	/**
	 * The IP address that the cardholders machine is presenting to the internet.
	 */
	const IP = 3;
	
	/**
	 * The cardholders name as it is on their card.
	 */
	const NAME = 4;
	
	/**
	 * Credit card number
	 */
	const CARD_NUMBER = 5;
	
	/**
	 * The amount for the transaction, no currency or formatting
	 */
	const AMOUNT = 6;
	
	/**
	 * Card expiry date, either mm/yy or mmyy
	 */
	const EXPIRY_DATE = 7;
	
	/**
	 * Switch/solo have an issue number, empty string for other card types
	 */
	const ISSUE_NUMBER = 8;
	
	/**
	 * Card start date, empty string otherwise
	 */
	const START_DATE = 9;
	
	/**
	 * Order details relevant to transaction: http://www.secpay.com/sc_api.html#order
	 */
	const ORDER = 10;
	
	/**
	 * Shipping details relevant to transaction: http://www.secpay.com/sc_api.html#shipping
	 */
	const SHIPPING = 11;
	
	/**
	 * Billing details relevant to transaction: http://www.secpay.com/sc_api.html#billing
	 */
	const BILLING = 12;
	
	/**
	 * Various other options, including deferred and repeat
	 */
	const OPTIONS = 13;
	
//adding constants here to avoid typos
	/**
	 * The 'key' for the deferred option (for values see DEFERRED_ consts below)
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
	 * The number of parameters to the XMLRPC call- this is used to fill empty ones
	 */
	const NUM_PARAMS = 14;
	
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
	
	/**
	 * An array of parameters
	 * @access private
	 * @var array
	 */
	private $params = array();
	
	/**
	 * The XMLRPC client instance
	 *
	 * @var Zend_XmlRpc_Client
	 */
	private $client;
	
	/**
	 * Constructor
	 */
	public function __construct($mid=null, $vpn_password=null) {
		$this->params=array();
		for($i=0;$i<self::NUM_PARAMS;$i++) {
			$this->params[$i] = '';
		}
		if($mid!==null) {
			$this->setMID($mid);
		}
		if($vpn_password!==null) {
			$this->setVPNPassword($vpn_password);
		}
	}
	
	/**
	 * Set the value of the MID (the secpay account name)
	 * @param String $mid
	 * @return void
	 */
	public function setMID($mid) {
		$this->params[self::MID] = $mid;
	}
	
	/**
	 * Set the value of the VPN password
	 * @param String $password
	 * @return void
	 */
	public function setVPNPassword($password) {
		$this->params[self::VPN_PSWD] = $password;
	}
	
	/**
	 * Set the value of the transaction id
	 * @param String $trans_id
	 * @return void
	 */
	public function setTransId($trans_id) {
		$this->params[self::TRANS_ID] = $trans_id;
	}
	
	/**
	 * Set the customer's IP
	 * @param String $ip_address
	 * @return void
	 */
	public function setCustomerIP($ip_address) {
		$this->params[self::IP] = $ip_address;
	}
	
	/**
	 * Take a PaymentCard object and grab the useful bits
	 * The PaymentCard should have been validated
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
	 * @param String $card_number
	 * @return void
	 */
	public function setCardNumber($card_number) {
		$number = preg_replace('#[^0-9]#','',$card_number);
		$this->params[self::CARD_NUMBER] = $number;
	}
	
	/**
	 * Set the card's CV2 number
	 * Note: the req_cv2 option doesn't have any effect for 'API customers'
	 */
	public function setCV2($cv2) {
		$this->addOption(self::OPTION_CV2,$cv2);
	}
	
	/**
	 * Set the customer's card's expiry date
	 * @param String $expiry
	 * @return void
	 */
	public function setCardExpiry($expiry) {
		$this->params[self::EXPIRY_DATE] = $expiry;
	}
	
	/**
	 * Set the card-holder's name
	 * @param String $name
	 * @retur void
	 */
	public function setCardName($name) {
		$this->params[self::NAME] = $name;
	}
	
	/**
	 * Set the card type
	 * Card type is part of the 'options' parameter, should be one of 'Visa', 'Master Card' or 'Switch'
	 * @param String $type
	 * @return void
	 */
	public function setCardType($type) {
		$this->addOption(self::OPTION_CARD_TYPE,$type);
	}
	
	/**
	 * Set the amount of the transaction
	 * This is cast to a string, as that's what the endpoint expects, and ints can slip in
	 * @param String $amount
	 * @return void
	 */
	public function setAmount($amount) {
		$this->params[self::AMOUNT] = (string)$amount;
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
	 * test_status is part of the 'options' paramater, is turned to a string during the concatenation there
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
		if($test_status) {
			$this->addOption('dups',$test_status);
			$this->addOption('mail_merchants',':');
		}
	}
	
	/**
	 * Sets the parameter that tells SecPay which fields to use in the hash they send back
	 * - this is ignored for repeat callbacks
	 * @param Array $fields
	 * @return void
	 */
	public function setHashFields($fields) {
		$this->addOption('md_flds',implode(':',$fields));
	}
	
	/**
	 * Sets the digest part of the request
	 * Secpay will only test this if you ask them to (email: admin@secpay.com requesting req_digest=true)
	 * @param String $remote_password
	 * @return void
	 */
	public function setDigest($remote_password) {
		$digest = md5($this->params[self::TRANS_ID].$this->params[self::AMOUNT].$remote_password);
		$this->addOption('digest',$digest);
	}
	
	/**
	 * Adds a key-value pair to the 'options' paramater, which is in a query-string format
	 * @param String $key
	 * @param String $value
	 * @return void
	 */
	private function addOption($key,$value) {
		if(!empty($this->params[self::OPTIONS])) {
			$this->params[self::OPTIONS].=',';
		}
		$this->params[self::OPTIONS].=urlencode($key).'='.$value;
	}
	
	/**
	 * Sets the billing address part of the xml
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
		$this->params[self::BILLING] = $xml->billing->asXML();
	}
	
	/**
	 * Perform's the function call
	 * @return SecPaySyncResponse
	 */
	public function send() {
		$this->setupClient();
		try {
			$this->client->call('SECVPN.validateCardFull',$this->params);
		}
		catch(Zend_XmlRpc_Client_FaultException $e) {
			echo "XMLRPC Fault: ".$e->getMessage();
			echo $this->client->getLastRequest()->saveXML();
			exit;
		}
		catch(Zend_XmlRpc_Client_HttpException $e) {
			echo "HTTP Error: ".$e->getMessage();
			exit;
		}

		$response = $this->client->getLastResponse();
		
		//turns the querystring into an assoc array- want to strip the ? from the front:
		parse_str(substr($response->getReturnValue(),1),$response_details);
		
		$secpay_response = new SecPaySyncResponse($response_details);
		return $secpay_response;
	}
	
	/**
	 * Does the setup of the XMLRPC client
	 * @return void
	 */
	private function setupClient() {
		require_once LIB_ROOT.'Zend/XmlRpc/Client.php';
		require_once LIB_ROOT.'Zend/Http/Client/Adapter/Socket.php';
		
		//tell the socket to use sslv3 (2 seems not to work)
		$socket = new Zend_Http_Client_Adapter_Socket();
		$socket->setConfig(array('ssltransport'=>'sslv3'));

		//then tell the Http_Client to use the socket...
		$http_client = new Zend_Http_Client();
		$http_client->setAdapter($socket);
		
		//then tell the XmlRpc_Client to use the Http_Client
		$this->client = new Zend_XmlRpc_Client(self::$url);
		$this->client->setHttpClient($http_client);
	}
}
?>