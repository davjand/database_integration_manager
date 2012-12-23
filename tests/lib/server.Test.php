<?php

require_once(dirname(__FILE__) . "/../../lib/server.class.php");

class ServerTest extends PHPUnit_Framework_TestCase {


	public function testHandleRequest() {
		
		// just test the marshalling... so mock out each
		// of the subhandler functions and
		// make sure requests go to the right place
	
		$mockServer = $this->getMock("DIM_Server");
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
	
	
	
	}
	
	
	public function testHandleCheckin() {
	
	

	}


}

?>