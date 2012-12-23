<?php

require_once(dirname(__FILE__) . "/../../lib/logger.class.php");

class LoggerTest extends PHPUnit_Framework_TestCase {
	
	public function testAddLogItem() {
	
		$logger = new DIM_Logger();
		$logger->addLogItem("TestLogItem");
	
	}
	
	public function testLogException() {
	
		try {
			//self::$mockedLogger->logException(new Exception("testing123"));
		}
		catch(Exception $e) {
			$this->assertTrue(true);
			return;
		}		
		$this->assertTrue(false);
	}

}

?>