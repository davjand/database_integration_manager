<?php

/*
	DIM_Configuration
	
	Deals with configuration - try to keep everything static!!
*/
class DIM_Configuration {
	
	/*
		Needs to be stored in the manifest folder.
	*/
	static $_CONFIG_FILE = "/../../manifest/dim_config.php";

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
		return (dirname(__FILE__) . "/../" . self::$_CONFIG_FILE);
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
		::getConfiguration()
		Gets the extension configuration.
		@returns
			array/null - the configuration, if it exists.
	*/
	public static function getConfiguration() {
		if(self::isExtensionConfigured()) {
			include(self::getExtensionConfigPath());
			return $savedSettings;
		}
		else{
			return null;
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
			$cfg = self::getConfiguration();
			return $cfg["mode"]["mode"];				
		}
		else{
			return "disabled";
		}		
	}
	
	/*
		::saveConfiguration($configuration)
		Saves the passed configuration into the configuration file.
		@params
			$configuration - the array of configuration settings
		@returns
			nothing
	*/
	public static function saveConfiguration($configuration) {
		
		// just save stuff for now using var_export - in the future we could maybe do some merging?
		file_put_contents(self::getExtensionConfigPath(), "<?php \$savedSettings = " . var_export($_POST["settings"], true) . "; ?>");	
	
	}
	
}

?>