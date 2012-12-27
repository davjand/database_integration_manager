<?php

require_once(dirname(__FILE__) . "/../../lib/server.class.php");
require_once(dirname(__FILE__) . "/../../lib/statemanager.class.php");

class ServerTest extends PHPUnit_Framework_TestCase {


	public function testHandleRequest() {
		
		// just test the marshalling... so mock out each
		// of the subhandler functions and
		// make sure requests go to the right place
	
		$mockServer = $this->getMock("DIM_Server", array("handleCheckout", "handleCheckin"));
		$mockServer->expects($this->once())
						->method("handleCheckout")
						->will($this->returnValue("3"));
		$mockServer->expects($this->once())
						->method("handleCheckin")
						->will($this->returnValue("2"));
			
		$this->assertEquals("2", $mockServer->handleRequest(array(
														"action" => "checkin", 
														"email" => "test", 
														"auth-key" => "test")
														));				
						
		$this->assertEquals("3", $mockServer->handleRequest(array("action" => "checkout", "email" => "test", "auth-key" => "test")));
		
		
	}
	
	
	public function testHandleCheckout() {
	
		$server = new DIM_Server();
		
		// should fail authentication
		$failRet = $server->handleRequest(array("action" => "checkout", "email" => "test", "auth-key" => "test"));
		$this->assertEquals("0:unauthed", $failRet);
		
		$mockAuthenticator = $this->getMock("DIM_Authenticator", array("getConfiguration"));
		$mockAuthenticator->expects($this->any()) 
						->method("getConfiguration")
						->will($this->returnValue(array(
									"server" => array(
										"users" => array(
											array(
												"email" => "test",
												"auth-key" => "test"
												)
											)
										)
									)));		
		
		$server->authenticator = $mockAuthenticator;
		
		$stateManager = new DIM_StateManager();
		$stateManager->checkIn();
		
		// should successfully check out
		$successRet = $server->handleRequest(array("action" => "checkout", "email" => "test", "auth-key" => "test"));
		$this->assertEquals("1", $successRet);
		$this->assertTrue($stateManager->isCheckedOut());
		
	}
	
	
	public function testHandleCheckin() {
	
		$server = new DIM_Server();
		
		// should fail authentication
		$failRet = $server->handleRequest(array("action" => "checkin", "email" => "test", "auth-key" => "test"));
		$this->assertEquals("0:unauthed", $failRet);	

		$mockAuthenticator = $this->getMock("DIM_Authenticator", array("getConfiguration"));
		$mockAuthenticator->expects($this->any()) 
						->method("getConfiguration")
						->will($this->returnValue(array(
									"server" => array(
										"users" => array(
											array(
												"email" => "test",
												"auth-key" => "test"
												)
											)
										)
									)));		
		
		$server->authenticator = $mockAuthenticator;
		
		$stateManager = new DIM_StateManager();
		$stateManager->checkOut();
		
		// should successfully check in
		$successRet = $server->handleRequest(array("action" => "checkin", "email" => "test", "auth-key" => "test"));
		$this->assertEquals("1", $successRet);
		$this->assertTrue($stateManager->isCheckedIn());
		
	}
	
}

?>