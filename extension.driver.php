<?php

	require_once(EXTENSIONS . "/database_integration_manager/lib/client.class.php");

	class extension_database_integration_manager extends Extension {

		static $_CONFIG_FILE = "/config.php";

		/*
			->install()
			Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/extension/#install
		*/
		public function install() {
		
		}
		
		/*
			->update($previousVersion)
			Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/extension/#update
		*/
		public function update() {
		
		}
		
		/*
			->uninstall()
			Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/extension/#uninstall
		*/
		public function uninstall() {
		
		}		
	
		/*
			->fetchNavigation()
			Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/extension/#fetchNavigation
		*/
		public function fetchNavigation(){ 
			return array(
				array(
					'location'	=> __('System'),
					'name'		=> __('DIM Configuration'),
					'link'		=> '/',
					'limit'		=> 'developer'
				)
			);
		}

		/*
			->getSubscribedDelegates()
			Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/extension/#getSubscribedDelegates
		*/
		public function getSubscribedDelegates() {
			return array(
				array(
					'page' => '/backend/',
					'delegate' => 'AppendPageAlert',
					'callback' => 'appendAlerts'
				),
				array(
					'page'		=> '/backend/',
					'delegate'	=> 'NavigationPreRender',
					'callback'	=> 'modifyNavigation'
				)
			);
		}
		
		/*
			->appendAlerts()
			Adds an alert to the administration pages if DIM is installed but not configured.
		*/
		public function appendAlerts($context) {
			if(!self::isExtensionConfigured()) {
				Administration::instance()->Page->pageAlert(
					__('Database Integration Manager is installed but not configured. <a href=\'' . SYMPHONY_URL . '/extension/database_integration_manager\'>Configure it now</a>.'),
					Alert::ERROR
				);				
			}
		}
		
		/*
			->modifyNavigation($navigation)
			Modify the Symphony admin navigation according to the current mode.
		*/
		public function modifyNavigation(&$navigation) {
			if(self::isExtensionConfigured()) {
				switch(self::getExtensionMode()) {
					case "client":
						
						break;
					case "server":						
						// clear out the blueprints
						$navigation["navigation"][200] = array();
						break;
					case "disabled":
						
						// we're disabled - don't do anything!
						break;
				}			
			}
			else {
				// clear all navigation items - the user will be able to get to the config via the alert.
				$navigation["navigation"] = array();
			}		
		}		
		
		/*
			::isExtensionConfigured()
			Returns true if a current configuration exists
		*/
		public static function isExtensionConfigured() {
			return file_exists(self::getExtensionConfigPath());	
		}
		
		/*
			::getExtensionConfigPath()
			Returns the fully qualified path of the extension configuration file
		*/
		public static function getExtensionConfigPath() {
			return (dirname(__FILE__) . "/" . self::$_CONFIG_FILE);
		}

		/*
			::getDatabaseSettings() 
			Gets the Symphony database settings
			@returns
				array("host" => , "port" => , "user" => , "password" => , "db" => , "tbl_prefix" => )
		*/
		public static function getDatabaseSettings() {
			include(MANIFEST . "/config.php");
			return $settings["database"];
		}
	
		/*
			::testSettings($settings)
			Run tests on the user-supplied settings to determine their integrity.
			@params
				$settings - the settings array supplied by the user
			@returns
				true/false based on test result
		*/
		public static function testSettings($settings) {
			if(self::getDatabaseSettings() != null) {
				switch($settings["mode"]["mode"]) {
					case "client":
						return DIM_Client::testClientSettings($settings["client"]);
						break;
					case "server":
						// PASSED - no settings needed
						return true;
						break;
					case "disabled":
						// PASSED - no settings needed
						return true;
						break;
					default:
						// FAILED - something weird happened!					
						return false;
						break;
				}
				
			}
			else {
				return false;
			}
		}

		/*
			::getExtensionMode()
			Get the currently set extension mode.
			@returns
				'server', 'client' or 'disabled'
		*/
		public static function getExtensionMode() {
			if(self::isExtensionConfigured()) {
				include(self::getExtensionConfigPath());
				return $savedSettings["mode"]["mode"];				
			}
			else{
				return "disabled";
			}		
		}
		
	}

?>