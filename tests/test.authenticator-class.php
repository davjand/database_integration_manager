<?php
	
	require_once(dirname(__FILE__).'/lib/config.php');
	
	/**
	
		Authenticator Class Tests
	
		lib/authenticator.class.php tests
	
	*/
	class SymphonyTestAuthenticatorClass extends UnitTestCase {
		
		public function setUp() {
			require_once DIM_TEST_ROOT . '/../lib/authenticator.class.php';
		}
		
		public function testTestSettings(){
			include DIM_TEST_CONFIG_SERVER;
			
			$this->assertEqual(is_array($savedSettings),true);	
		}
		
		/**
		
			test that a server user authenticates
			
		*/
		public function testCanAuthenticateServerUser(){
			$auth = new DIM_Authenticator();
			
			include DIM_TEST_CONFIG_SERVER; //gives us $savedSettings
			
			$this->assertEqual(
				$auth->authenticateUserWithConfig('dave@veodesign.co.uk','test123',$savedSettings),
				true
			);
			
			$this->assertEqual(
				$auth->authenticateUserWithConfig('jon@veodesign.co.uk','ABCDE',$savedSettings),
				true
			);
		}
		
		public function testCanAuthenticateClientUser(){
			$auth = new DIM_Authenticator();	
			
			include DIM_TEST_CONFIG_CLIENT; //gives us $savedSettings
			
			$this->assertEqual(
				$auth->authenticateUserWithConfig('dave@veodesign.co.uk','test123',$savedSettings),
				true
			);
		}
		
		
	}
?>