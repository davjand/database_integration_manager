<?php

require_once(dirname(__FILE__) . "/io/network.class.php");
require_once(dirname(__FILE__) . "/versioning.class.php");
require_once(dirname(__FILE__) . "/base.class.php");

/*
	DIM_Client
	
	Encapsulates the workings of the DIM client.
*/
class DIM_Client extends DIM_Base {

	var $state = null;
	var $logger = null;
	
	/*
		->__construct()
	*/
	public function __construct() {
		$this->state = new DIM_StateManager("client");
		$this->logger = new DIM_Logger();
	}

	/*
		->requestCheckout(&$error)
		Query the server to request a checkout of the database.
		@params
			&$error - any errors will populate this variable
		@returns
			true/false - depending on success or failure.
	*/
	public function requestCheckout(&$error) {
		
		$config = $this->getConfiguration();
		$versioning = new DIM_Versioning();
		
		$requestData = array(
				"action" => "checkout",
				"email" => $config["client"]["user-email"],
				"auth-key" => $config["client"]["auth-key"],
				"version" => $versioning->getLatestVersion()
			);
	
		$rawResponse = Network_IO::makeServerRequest($config["client"]["server-host"], $requestData);
		
		$responseParts = explode(":", $rawResponse);

		if($responseParts[0] == "1") {
			// successful checkout
			$this->state->checkOut();
			$this->logger->addLogItem("Database Checked Out", "state");

			$newVersion = $versioning->addNewVersion();
			$this->logger->addLogItem("Added database version {$newVersion}");
			
			return true;
		}
		else {
			$error = $responseParts[1];
			return false;
		}
	
	}
	
	/*
		->requestCheckin(&$error)
		Query the server to request a checkin of the database.
		@params
			&$error - any errors will populate this variable		
		@returns
			true/false - depending on success or failure
	*/
	public function requestCheckin(&$error) {
	
		$config = $this->getConfiguration();
		$versioning = new DIM_Versioning();

		$requestData = array(
				"action" => "checkin",
				"email" => $config["client"]["user-email"],
				"auth-key" => $config["client"]["auth-key"],
				"version" => $versioning->getLatestVersion(),
				"old-version" => ($versioning->getLatestVersion() - 1)
			);
	
		$rawResponse = Network_IO::makeServerRequest($config["client"]["server-host"], $requestData);
		
		$responseParts = explode(":", $rawResponse);
		
		if($responseParts[0] == "1") {
			// successful checkin!
			$this->state->checkIn();
			$this->logger->addLogItem("Database Checked In", "state");
			
			return true;
		}
		else {
			$error = $responseParts[1];
			return false;
		}
		
	}
	
	/*
		::testClientSettings($settings)
		Tests the client-specific settings supplied by the user
		@returns
			true/false - based on the test result
	*/
	public static function testClientSettings($settings) {
		if($settings["server-host"] != "") {
			// check if the supplied URL exists
			if(Network_IO::isActiveUrl($settings["server-host"])) {
				if(self::makeTestServerRequest($settings["server-host"])) {
					// PASSED
					return true;
				}
				// FAILED - not a server
				return false;
			}
			else {
				// FAILED - inactive URL
				return false;
			}
		}
		else {
			// FAILED - no URL given
			return false;
		}
	}
	

	/*
		::makeTestServerRequest()
		Makes a test request to the configured server.
		@returns
			true/false - based on the test result
	*/
	private static function makeTestServerRequest($host) {		
		$serverResponse = Network_IO::makeServerRequest($host, array("action" => "test"));
		if($serverResponse == "1") {
			return true;
		}
		else {
			return false;
		}
	}
	
	

}

?>