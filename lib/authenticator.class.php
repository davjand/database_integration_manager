<?php

require_once(dirname(__FILE__) . "/base.class.php");

/*
	DIM_Authenticator
	
	Deals with all the authentication required by the DIM_Server
*/
class DIM_Authenticator extends DIM_Base {
	
	/*
		->__construct()
	*/
	public function __construct() {
		parent::__construct();
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
		return $this->authenticateUserWithConfig($email,$authKey,$this->getConfiguration());
	}
	
	
	
	/**
	
		Function to authenticate the user against the config file
		
	*/
	public function authenticateUserWithConfig($email,$authKey,$config){
		
		if(!$config || !is_array($config) || !is_array($config['mode'])){
			return false;
		}
		$MODE = $config['mode']['mode'];
		
		/*
			Server Mode
		*/
		if($MODE == 'client'){
			$user = $config['client'];
			if($user['user-email'] == $email && $user['auth-key'] == $authKey){
				return true;
			}
			return false;
		
		
		}
		/*
			Server Mode
		*/
		else{
	
			if(is_array($config["server"]["users"])) {
				foreach($config["server"]["users"] as $u) {
					if($email == $u['email'] && $authKey == $u['auth-key']) {
						return true;
					}			
				}
				// we haven't matched yet
				return false;
			}
			else {
				return false;
			}	
		}
	}
	
}

?>