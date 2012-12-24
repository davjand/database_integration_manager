<?php

require_once(dirname(__FILE__) . "/../../lib/querymanager.class.php");

class QueryManagerTest extends PHPUnit_Framework_TestCase {


	public function testLogNewQuery() {
	
		$mockQueryManager = $this->getMock("DIM_QueryManager", array("getCacheFileName"));
		$mockQueryManager->expects($this->any())
							->method("getCacheFileName")
							->will($this->returnValue(dirname(__FILE__) . "/test_q_cache"));
	
		$mockQueryManager->logNewQuery("THIS IS A QUERY");
	
	}

}


?>