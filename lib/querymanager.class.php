<?php

require_once(dirname(__FILE__) . "/base.class.php");
require_once(dirname(__FILE__) . "/statemanager.class.php");

/*
	DIM_QueryManager
	Deals with logging, storing and running all database queries.
*/
class DIM_QueryManager extends DIM_Base {

	var $databaseInfo = null;
	
	/*
		->__construct()
	*/
	public function __construct() {
		$this->databaseInfo = $this->getDatabaseSettings();
	}
	
	/*
		->logNewQuery($query) 
		Logs a new query into the cache
		@params
			$query - the SQL query captured from Symphony
	*/
	public function logNewQuery($query) {
		
		$stateManager = new DIM_StateManager("client");
		
		if($this->getExtensionMode() == "client" && $stateManager->isCheckedOut()) {
		
			$tblPrefix = $this->databaseInfo["tbl_prefix"];
		
			/* FILTERS */
			//Shamelessly stolen from: https://github.com/remie/CDI/blob/master/lib/class.cdilogquery.php
		
			// do not register changes to tbl_database_migrations
			if (preg_match("/{$tblPrefix}database_migrations/i", $query)) return true;
			// only structural changes, no SELECT statements
			if (!preg_match('/^(insert|update|delete|create|drop|alter|rename)/i', $query)) return true;
			// un-tracked tables (sessions, cache, authors)
			if (preg_match("/{$tblPrefix}(authors|cache|forgotpass|sessions|tracker_activity)/i", $query)) return true;
			// content updates in tbl_entries (includes tbl_entries_fields_*)
			if (preg_match('/^(insert|delete|update)/i', $query) && preg_match("/({$tblPrefix}entries)/i", $query)) return true;
			// append query delimeter if it doesn't exist
			if (!preg_match('/;$/', $query)) $query .= ";";			
		
			if($query != "") {
				file_put_contents($this->getCacheFileName(), $query . "\r\n", FILE_APPEND);	
			}
		
		}		
	
	}
	
	/*
		->getCacheFileName()
		@returns
			string - the file name
	*/
	private function getCacheFileName() {
		return (dirname(__FILE__) . "/../../../manifest/dim_q_cache");
	}
	
	/*
		->makeVersionFile($version, $commitMessage)
		Transforms the query cache into a version file and then clears it
		@params
			$version - the new version that the file will represent
			$commitMessage - the commit message sent by the user
	*/
	public function makeVersionFile($version, $commitMessage) {
	
		$versionFilename = dirname(__FILE__) . "/../../../data/version.{$version}.php";
	
		$cacheContents = file_get_contents($this->getCacheFileName());
		
		$versionData = array(
			"version" => $version,
			"commitMessage" => $commitMessage,
			"queries" => base64_encode($cacheContents)
			);
		
		file_put_contents($versionFilename, "<?php \$versionData = " . var_export($versionData, true) . "; ?>");	
	
		$this->clearCache();
		
	}
	
	/*
		->revert()
		Clears the cache without creating a new version file
	*/
	public function revert() {
		$this->clearCache();
	}
	
	
	/*
		->clearCache()
		Clears the current cache
	*/
	private function clearCache() {
		file_put_contents($this->getCacheFileName(), "");	
	}
	

}



?>