<?php
require_once 'Zend/XmlRpc/Client.php';
require_once 'Zend/Http/Client/Adapter/Test.php';
require_once 'Zend/Http/Client.php';


AutoLoader::Instance()->addPath(FILE_ROOT.'omelette/lib/payment/');

Mock::Generate('PaymentCard', 'MockPaymentCard', array('getCardName'));
Mock::Generate('Zend_XmlRpc_Client');

class TestOfSecpayFull extends UnitTestCase {
	
	/**
	 * The test adapter to use
	 *
	 * @var Zend_Http_Client_Adapter_Test
	 */
	protected $adapter;
	
	/**
	 * The http_client to use
	 *
	 * @var Zend_XmlRpc_Client
	 */
	protected $client;
	
	function setup() {
		$this->adapter = new Zend_Http_Client_Adapter_Test();
		$http_client = new Zend_Http_Client();
		$http_client->setAdapter($this->adapter);
		$this->client = new MockZend_XmlRpc_Client('http://localhost');
		//$this->client->setHttpClient($http_client);
	}
	
	function testDeferredRequest() {
		$request = new SecPayFull(SECPAY_MID,SECPAY_VPN_PASSWORD);
		
		$request->setClient($this->client);
		
		$card = new MockPaymentCard();
		
		$card->setReturnValue('getCardNumber', 5555444444444444);
		$card->setReturnValue('getCardName', 'Mr Fred Bloggs');
		$card->setReturnValue('getCardType', PaymentCard::MASTERCARD);
		$card->setReturnValue('getCV2', '458');
		$card->setReturnValue('getAddress', array(
			'addr_1'=>'12 Some Street',
			'addr_2'=>'',
			'city'=>'Sometown',
			'state'=>'Someshire',
			'post_code'=>'SO12 3GH',
			'country'=>'GB',
			'phone'=>'0121 348 3849'
		));
		
		/*
		 * $this->setCardExpiry($card->getExpiry());
		$this->setCardName($card->getCardholderName());
		$this->setCardType($card->getCardType());
		$this->setCV2($card->getCV2());
		$this->setBillingAddress($card->getAddress());
		 */
		
		$trans_id = 'Tactile'.date('Ymdhis');
		$request->addPaymentCard($card);
		$request->setTransId($trans_id);
		$request->setCustomerIP('127.0.0.1');
		
		$request->setAmount(5.00);
		$request->setDeferred(SecPayRequest::DEFERRED_FULL);
				
		$request->setTest('true');
		
		//this needs to be done after other things!!!
		$request->setDigest(SECPAY_REMOTE);
		
		$response_data = array(
			'valid'=>'true',
			'trans_id'=>$trans_id,
			'code'=>'A',
			'auth_code'=>'9999',
			'message'=>'Test',
			'amount'=>'5.00',
			'test_status'=>'true'
		);
		$response_string = '?'.http_build_query($response_data);
		$xml_response = new Zend_XmlRpc_Response();
		$xml_response->setReturnValue($response_string);
		
		$this->client->setReturnValue('getLastResponse', $xml_response);
				
		$response = $request->send();
		
		$this->assertIsA($response, 'SecpayResponse');
	}
	
}

?>
