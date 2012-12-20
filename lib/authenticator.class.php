<?php

require_once(dirname(__FILE__) "/../extension.driver.php");

/*
	DIM_Authenticator
	
	Deals with all the authentication required by the DIM_Server
*/
class DIM_Authenticator {
	
	/*
		->__construct()
	*/
	public __construct() {
	
	}

	/*
		->userAuthenticates($email, $authKey)
		Checks with the configuration to determine whether or
		not authentication is valid.
		@params
			$email - the user email
			$authKey - the user authentication key
		@returns
			true/false based on whether authentication was succesful
	*/
	public function userAuthenticates($email, $authKey) {
		
	
	}
}

?>