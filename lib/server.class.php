<?php

require_once(dirname(__FILE__) . "/logger.class.php");
require_once(dirname(__FILE__) . "/authenticator.class.php");
require_once(dirname(__FILE__) . "/versioning.class.php");
require_once(dirname(__FILE__) . "/statemanager.class.php");
require_once(dirname(__FILE__) . "/base.class.php");

/*
	DIM_Server
	
	Encapsulates the workings of the DIM server.
*/
class DIM_Server extends DIM_Base {

	var $authenticator = null;
	var $versioning = null;
	var $state = null;
	var $logger = null;
	
	/*
		->__construct()
	*/
	public function __construct() {
		$this->authenticator = new DIM_Authenticator();
		$this->versioning = new DIM_Versioning();
		$this->state = new DIM_StateManager();
		$this->logger = new DIM_Logger();
	}
	
	/*
		->handleRequest($requestData)
		The main server method! Is responsible for shuffling everything
		round and returning a value to the client.
		@params
			$requestData - the data sent by the client
		@returns
			mixed - the result of the request.
	*/
	public function handleRequest($requestData) {
		// this is a system entry point so we need to grab exceptions here
		try {
			switch($requestData["action"]) {
				case "checkout":
					return $this->handleCheckout($requestData);
					break;
				case "checkin":
					return $this->handleCheckIn($requestData);
					break;
				case "test":
					return "1";
					break;
				default:
					return "0:error";
					break;
			}
		}
		catch(Exception $e) {
			$this->logger->logException($e);
			return "-1:error";
		}
	}
	
	/*
		->handleCheckout($requestData)
		Handles a checkout request
		@params
			$requestData - the data sent by the client
		@returns
			mixed - the result of the request
	*/
	private function handleCheckout($requestData) {
		if($this->authenticator->userAuthenticates($requestData["email"], $requestData["auth-key"])) {
			if($this->state->isCheckedIn()) {
				$this->state->checkOut();
				$this->logger->addLogItem("Checked Out By {$requestData["email"]}", "state");
				return "1";
			}
			else {
				return "0:wrong-state";
			}
		}
		else {
			$this->logger->addLogItem("Unauthorised checkout attempt by {$requestData["email"]}", "security");
			return "0:unauthed";
		}
	}
	
	/*
		->handleCheckin($requestData)
		Handles a checkout request
		@params
			$requestData - the data sent by the client
		@returns
			mixed - the result of the request
	*/
	private function handleCheckin($requestData) {
		if($this->authenticator->userAuthenticates($requestData["email"], $requestData["auth-key"])) {
			if($this->state->isCheckedOut()) {
				$this->state->checkIn();
				$this->logger->addLogItem("Checked In By {$requestData["email"]}", "state");
				return "1";
			}
			else {
				return "0:wrong-state";
			}		
		}
		else {
			$this->logger->addLogItem("Unauthorised checkin attempt by {$requestData["email"]}", "security");
			return "0:unauthed";
		}
	}

	/*
		::generateAuthenticationKey($userData)
		Generates a secure key partially based on the user data supplied
		@params
			$userData - the array of user data
		@returns
			string - the authentication key
	*/
	public static function generateAuthenticationKey($userData) {
	
		$tmpA = "";
		foreach($userData as $d) {
			$tmpA .= sha1($d);
		}
		$tmpA .= sha1(mt_rand());
		$tmpA .= sha1(mt_rand());
		for($i=1;$i<10;$i++) {
			$tmpA = sha1($tmpA);
		}
		return $tmpA;
		
	}
	
}



?>