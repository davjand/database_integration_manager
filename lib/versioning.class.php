<?php

require_once(dirname(__FILE__) . "/io/database.class.php");
require_once(dirname(__FILE__) . "/base.class.php");
require_once(dirname(__FILE__) . "/querymanager.class.php");

/*
	DIM_Versioning
	
	Encapsulates all the versioning database requests
*/
class DIM_Versioning extends DIM_Base {

	var $database;

	/*
		->construct()
	*/
	public function __construct() {
		$this->database = new Database_IO($this->getDatabaseSettings());
	}
	
	/*
		->databaseNeedsUpdating()
		Determine if the database needs updating.
		@returns
			true/false - based on the query
	*/
	public function databaseNeedsUpdating() {
		
		$latestVersion = $this->getLatestVersion();
		
		$queryManager = new DIM_QueryManager();
		if($queryManager->checkForVersionFile($latestVersion + 1)) {
			return true;
		}		
		else {
			return false;
		}	
	}
	
		

	/*
		->addNewVersion()
		Puts another version into the database.
		@params
			$newVersion - the version to add (optional defaults to -1 which will cause a new number to be generated)
		@returns
			int - the new version number.
	*/
	public function addNewVersion($newVersion = -1) {
		$currentVersion = $this->getLatestVersion();
		if($newVersion == -1) {
			$newVersion = $currentVersion + 1;
		}
		
		$pendingState = "";
		if($this->getExtensionMode() == "client") {
			$pendingState = "completed";
		}
		else {
			$pendingState = "pending";
		}
		
		$sql = "INSERT INTO tbl_dim_versions (version, state) VALUES ({$newVersion}, '{$pendingState}')";
		$this->database->query($sql, RETURN_NONE, true);
		
		return $newVersion;
	}
	
	
	/*
		->getLatestVersion()
		Gets the latest db version from the database
		@returns
			int - the latest database version.
	*/
	public function getLatestVersion() {
		
		$sql = "SELECT version FROM tbl_dim_versions ORDER BY version DESC LIMIT 1";
		$latestVersion = $this->database->query($sql, RETURN_VALUE, true);

		if(!$latestVersion) {
			$latestVersion = "0";
		}
		
		return $latestVersion;
		
	}
	
}

?>