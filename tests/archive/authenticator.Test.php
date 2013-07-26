<?php

require_once(dirname(__FILE__) . "/../../lib/authenticator.class.php");

class AuthenticatorTest extends PHPUnit_Framework_TestCase {


	/**
	 * @dataProvider authenticationProvider
	 */
	public function testUserAuthenticates($email, $authKey, $expectation) {
		
		// mock the config
		$authenticator = $this->getMock('DIM_Authenticator', array('getConfiguration'));
		$authenticator->expects($this->any())
						->method('getConfiguration')
						->will($this->returnValue(array(
							"server" => array(
								"users" => array(
									0 => array(
										"email" => "test@test.com",
										"auth-key" => "awdpoijwadpojkawdpok"
										),
									1 => array(
										"email" => "black@hat.net",
										"auth-key" => "awioajoiajwdoijawoij"
										),
									2 => array(
										"email" => "tom@test.blah.com",
										"auth-key" => "awioajoiajwdoijawoij-shouldfail"
										),
									)
								)
							)));
			
		$this->assertEquals($authenticator->userAuthenticates($email, $authKey), $expectation);
	
	}

	
	public function authenticationProvider() {
		return array(
				array("test@test.com", "awdpoijwadpojkawdpok", true),
				array("black@hat.net", "awioajoiajwdoijawoij", true),
				// this one should fail
				array("tom@test.blah.com", "awpokdpawok-failme", false)				
			);	
	}
	
}

?>