<?php

require_once(EXTENSIONS . "/database_integration_manager/lib/io/network.class.php");

/*
	DIM_Client
	
	Encapsulates the workings of the DIM client.
*/
class DIM_Client {

	/*
		::testClientSettings($settings)
		Tests the client-specific settings supplied by the user
		@returns
			true/false - based on the test result
	*/
	public static function testClientSettings($settings) {
		if($settings["server-url"] != "") {
			// check if the supplied URL exists
			if(Network_IO::isActiveUrl($settings["server-url"])) {
				if(self::makeTestServerRequest($settings["server-url"])) {
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
	private static function makeTestServerRequest($url) {		
		$serverResponse = Network_IO::makeServerRequest($url, array("action" => "test"));
		echo($serverResponse);
		if($serverResponse == "1") {
			return true;
		}
		else {
			return false;
		}
	}
	
	

}

?>