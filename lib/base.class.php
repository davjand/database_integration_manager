<?php

if(!defined("MANIFEST")) {
	define("MANIFEST", dirname(__FILE__) . "/../../../manifest");
}

if(!defined("DIM_ROOT")) {
	define("DIM_ROOT", MANIFEST."/dim");
}
if(!defined("DIM_CONFIG")) {
	define("DIM_CONFIG", DIM_ROOT."/config.php");
}

/*
	DIM_Base
	
	Deals with configuration mainly, but should be inherited by all DIM objects. It cannot
	be declared abstract because Symphony structures will need to instantiate it to access
	the config functions.
*/
class DIM_Base {
	
	/*
		Needs to be stored in the manifest folder.
	*/
	var $_CONFIG_FILE = DIM_CONFIG;

	/*
		->isExtensionConfigured()
		Returns true if a current configuration exists
	*/
	public function isExtensionConfigured() {
		return file_exists(self::getExtensionConfigPath());	
	}
	
	/*
		->getExtensionConfigPath()
		Returns the fully qualified path of the extension configuration file
	*/
	public function getExtensionConfigPath() {
		return ($this->_CONFIG_FILE);
	}	
	
	/*
		->getDatabaseSettings() 
		Gets the Symphony database settings
		@returns
			array("host" => , "port" => , "user" => , "password" => , "db" => , "tbl_prefix" => )
	*/
	public function getDatabaseSettings() {
		include(MANIFEST . "/config.php");
		return $settings["database"];
	}
	
	/*
		->getConfiguration()
		Gets the extension configuration.
		@returns
			array/null - the configuration, if it exists.
	*/
	public function getConfiguration() {
		if(self::isExtensionConfigured()) {
			include(self::getExtensionConfigPath());
			return $savedSettings;
		}
		else{
			return null;
		}		
	}
	
	/*
		->getExtensionMode()
		Get the currently set extension mode.
		@returns
			'server', 'client' or 'disabled'
	*/
	public function getExtensionMode() {
		if($this->isExtensionConfigured()) {
			$cfg = $this->getConfiguration();
			return $cfg["mode"]["mode"];				
		}
		else{
			return "disabled";
		}		
	}
	
	/*
		->saveConfiguration($configuration)
		Saves the passed configuration into the configuration file.
		@params
			$configuration - the array of configuration settings
		@returns
			nothing
	*/
	public function saveConfiguration($configuration) {
		
		// just save stuff for now using var_export - in the future we could maybe do some merging?
		file_put_contents($this->getExtensionConfigPath(), "<?php \$savedSettings = " . var_export($_POST["settings"], true) . "; ?>");	
	
	}
	
}

?>