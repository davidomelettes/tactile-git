<?php
class HTMLFormLoginHandler implements LoginHandler {
	private $gateway;
	
	public function __construct(AuthenticationGateway $gateway) {
		$this->gateway=$gateway;
	}
	public function doLogin() {
		$username=$_POST['username'];
		$password=$_POST['password'];
		$db=DB::Instance();
		return $this->gateway->Authenticate(array('username'=>$username,'password'=>$password,'db'=>$db));
	}
}

?>
