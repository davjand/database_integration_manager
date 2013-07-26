<?php

require_once(dirname(__FILE__) . "/../../../lib/io/network.class.php");


class NetworkTest extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider hostProvider
	 */
	public function testGetUrlFromHost($host, $result) {
		$this->assertEquals($result, Network_IO::getUrlFromHost($host));
	}	
	
	public function hostProvider() {
		return array(
			array("127.0.0.1/test", "http://127.0.0.1/test/extensions/database_integration_manager/server/index.php"),
			array("www.google.com", "http://www.google.com/extensions/database_integration_manager/server/index.php"),
			array("localhost", "http://localhost/extensions/database_integration_manager/server/index.php"),
			array("173.194.34.97", "http://173.194.34.97/extensions/database_integration_manager/server/index.php")
		);	
	}

}

?>