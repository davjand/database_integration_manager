<?php
	require_once(EXTENSIONS . "/database_integration_manager/lib/client.class.php");
	require_once(EXTENSIONS . "/database_integration_manager/lib/base.class.php");
	
	class Extension_database_integration_manager extends Extension {

		var $config = null;

		/*
			->__construct()
		*/
		public function __construct() {
			parent::__construct();
			$this->config = new DIM_Base();
		}		
	
	
		/*
			->install()
			Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/extension/#install
		*/
		public function install() {
			// MODIFYING THIS? ADD A VERSION UPDATE IN THE update() FUNCTION!
		
			try {
				Symphony::Database()->query('CREATE TABLE IF NOT EXISTS tbl_dim_versions (
											  `id` int(11) NOT NULL AUTO_INCREMENT,
											  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
											  `version` int(11) NOT NULL,
											  PRIMARY KEY (`id`)
											  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;');

			} catch(Exception $e) { return false; }

			return true;
		
		}
		

		
		/*
			->update($previousVersion)
			Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/extension/#update
		*/
		public function update($previousVersion) {
		
			if($previousVersion = "0.0.1") {
			
			
			}
		
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
			if(!$this->config->isExtensionConfigured()) {
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
			if($this->config->isExtensionConfigured()) {
				switch($this->config->getExtensionMode()) {
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
			::testSettings($settings)
			Run tests on the user-supplied settings to determine their integrity.
			@params
				$settings - the settings array supplied by the user
			@returns
				true/false based on test result
		*/
		public static function testSettings($settings) {
			if($this->config->getDatabaseSettings() != null) {
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
		
	}

?>