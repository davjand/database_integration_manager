<?php

require_once(dirname(__FILE__) . "/../../lib/logger.class.php");

class LoggerTest extends PHPUnit_Framework_TestCase {

	public static $mockedLogger = null;

	public function setUp() {
	
		self::$mockedLogger = $this->getMock("DIM_Logger", array("saveRawLogItem"));
		
		self::$mockedLogger->expects($this->once())
						->method("saveRawLogItem")
						->will($this->returnCallback('throwFilledException'));
	
	
	}
	
	public function throwFilledException($obj) {
		throw new Exception(serialize($obj));
	}
	
	public function testAddLogItem() {
	
		try {
			self::$mockedLogger->addLogItem("Hello");
		}
		catch(Exception $e) {
			print_r(unserialize($e->getMessage()));
		}
	
	}
	
	public function testLogException() {
	
		try {
			self::$mockedLogger->logException(new Exception("testing123"));
		}
		catch(Exception $e) {
			print_r(unserialize($e->getMessage()));
		}		
	
	}
	
	public function readLog() {
	
	
	
	}

}

?>