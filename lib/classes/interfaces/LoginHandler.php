<?php
interface LoginHandler {
	public function __construct(AuthenticationGateway $gateway);
	public function doLogin();

}
?>
