<?php

require_once(dirname(__FILE__) . "/io/database.class.php");

/*
	DIM_Versioning
	
	Encapsulates all the versioning database requests
*/
class DIM_Versioning {

	/*
		::addNewVersion()
		Puts another version into the database.
		@returns
			int - the new version number.
	*/
	public static function addNewVersion() {
		$currentVersion = self::getLatestVersion();
		$newVersion = $currentVersion + 1;
		
		$sql = "INSERT INTO tbl_dim_versions (version) VALUES ({$newVersion})";
		Database_IO::query($sql, RETURN_NONE, true);
		
		return $newVersion;
	}
	
	
	/*
		::getLatestVersion()
		Gets the latest db version from the database
		@returns
			int - the latest database version.
	*/
	public static function getLatestVersion() {
		
		$sql = "SELECT version FROM tbl_dim_versions ORDER BY version DESC LIMIT 1";
		$latestVersion = Database_IO::query($sql, RETURN_VALUE, true);
	
		if(!$latestVersion) {
			$latestVersion = "0";
		}
		
		return $latestVersion;
		
	}
	
}

?>