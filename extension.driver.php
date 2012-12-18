<?php

	class extension_database_integration_manager extends Extension {

		static $_CONFIG_FILE = "/config.php";
	
	
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
			->install()
			Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/extension/#install
		*/
		public function install() {
		
		}
		
		/*
			->uninstall()
			Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/extension/#uninstall
		*/
		public function uninstall() {
		
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
						if($settings["client"]["server-url"] != "") {

							// check if the supplied URL exists
							if(self::isActiveUrl($settings["client"]["server-url"])) {
								// PASSED ALL THE TESTS
								return true;
							}
							else {
								// FAILED - inactive URL
								return false;
							}
							
						}
						else {
							// FAILED - no URL given
							return false;
						}
						
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
			::isActiveUrl($url)
			Determines if the current URL is alive - taken from http://www.secondversion.com/blog/php-check-if-a-url-is-valid-exists/
			@params
				$url - the URL to test
			@returns
				true/false based on success or failure
		*/
		private static function isActiveUrl($url)
		{
			if (!($url = @parse_url($url)))
			{
				return false;
			}
		 
			$url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];
			$url['path'] = (!empty($url['path'])) ? $url['path'] : '/';
			$url['path'] .= (isset($url['query'])) ? "?$url[query]" : '';
		 
			if (isset($url['host']) AND $url['host'] != @gethostbyname($url['host']))
			{
				if (PHP_VERSION >= 5)
				{
					$headers = @implode('', @get_headers("$url[scheme]://$url[host]:$url[port]$url[path]"));
				}
				else
				{
					if (!($fp = @fsockopen($url['host'], $url['port'], $errno, $errstr, 10)))
					{
						return false;
					}
					fputs($fp, "HEAD $url[path] HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
					$headers = fread($fp, 4096);
					fclose($fp);
				}
				return (bool)preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers);
			}
			return false;
		}
		
	}

?>