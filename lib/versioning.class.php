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
			$commitMessage - the message of the commit to add to the database (optional, defaults to "")
		@returns
			int - the new version number.
	*/
	public function addNewVersion($newVersion = -1, $commitMessage = "") {
	
		$currentVersion = $this->getLatestVersion();
		if($newVersion == -1) {
			$newVersion = $currentVersion + 1;
		}
		
		$commitMessage = Database_IO::sanitize($commitMessage, 3);
		
		//see if needs updating or inserting
		$findSql = "SELECT * FROM tbl_dim_versions WHERE `version`={$newVersion}";
		$findRes = $this->database->query($findSql,RETURN_OBJECTS);

		
		if(count($findRes)>0){
			$sql = "UPDATE tbl_dim_versions SET `state`='completed' WHERE `version`={$newVersion}";	
		}
		else{
			$pendingState = "";
			if($this->getExtensionMode() == "client") {
				$pendingState = "completed";
			}
			elseif($this->getExtensionMode() == "server") {
				$pendingState = "pending";
			}
			$sql = "INSERT INTO tbl_dim_versions (version, state, message) VALUES ({$newVersion}, '{$pendingState}', '{$commitMessage}')";	
		}
		
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
		
		$sql = "SELECT version FROM tbl_dim_versions WHERE `state`='completed' ORDER BY version DESC LIMIT 1";
		$latestVersion = $this->database->query($sql, RETURN_VALUE, true);

		if(empty($latestVersion)) {
			$latestVersion = "0";
		}
		
		return $latestVersion;
	}
	
	public function isUpToDate(){
		$sql = "SELECT * FROM tbl_dim_versions ORDER BY version DESC LIMIT 1";
		$latestVersion = $this->database->query($sql, RETURN_OBJECTS, true);	
		
		if($latestVersion[0]->state == 'completed'){
			
			//check there aren't updates to be run
			if($queryManager->checkForVersionFile($latestVersion + 1)) {
				return false;
			}
			return true;
		}
		else{
			return false;
		}
	}
	
}

?>