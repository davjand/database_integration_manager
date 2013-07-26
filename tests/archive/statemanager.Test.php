<?php

require_once(dirname(__FILE__) . "/../../lib/statemanager.class.php");

class StateManagerTest extends PHPUnit_Framework_TestCase {

	public function testReadState() {
	
		// first we check readstate - so we can be sure
		// that we can rely on it
		
		$mockStateManager = $this->getMock("DIM_StateManager", array("readState"));
		// so the system will definitely read as checked out
		$mockStateManager->expects($this->any())
							->method("readState")
							->will($this->returnValue(true));
							
		$this->assertTrue($mockStateManager->isCheckedOut());
		$this->assertFalse($mockStateManager->isCheckedIn());
	
	}

	public function testCheckout() {
	
		$stateManager = new DIM_StateManager();
		$stateManager->checkOut();
		$this->assertTrue($stateManager->isCheckedOut());
		$this->assertFalse($stateManager->isCheckedIn());
	
	}

	public function testCheckin() {
	
		$stateManager = new DIM_StateManager();
		$stateManager->checkIn();
		$this->assertTrue($stateManager->isCheckedIn());
		$this->assertFalse($stateManager->isCheckedOut());	
	
	}

}

?>