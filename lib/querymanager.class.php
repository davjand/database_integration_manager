<?php

require_once(dirname(__FILE__) . "/base.class.php");
require_once(dirname(__FILE__) . "/statemanager.class.php");
require_once(dirname(__FILE__) . "/versioning.class.php");

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
				file_put_contents($this->getQueryCacheFilename(), $query . "\r\n", FILE_APPEND);	
			}
		
		}		
	
	}
	
	/*
		->getQueryCacheFileName()
		@returns
			string - the file name of the query cache
	*/
	private function getQueryCacheFilename() {
		return (MANIFEST."/dim/q_cache");
	}
	
	/*
		->getUpdateCacheFileName()
		@returns
			string - the file name of the update cache
	*/
	public function getUpdateCacheFilename() {
		return (MANIFEST."/dim/u_cache");	
	}
	
	/*
		->getVersionFileName()
		@params
			$version - the version filename to get.
		@returns
			string - the file name
	*/		
	private function getVersionFileName($version) {
		return DOCROOT."/data/version.{$version}.php";		
	}
	
	/*
		->checkForVersionFile($versionNumber)
	*/
	public function checkForVersionFile($version) {
		$versionFilename = $this->getVersionFileName($version);
		return file_exists($versionFilename);
	}
	
	/*
		->makeVersionFile($version, $commitMessage)
		Transforms the query cache into a version file and then clears it
		@params
			$version - the new version that the file will represent
			$commitMessage - the commit message sent by the user
		@returns
			string - the version file name
	*/
	public function makeVersionFile($version, $commitMessage) {
	
		$versionFilename = $this->getVersionFileName($version);
	
		$cacheContents = file_get_contents($this->getQueryCacheFileName());
		
		$versionData = array(
			"version" => $version,
			"commitMessage" => $commitMessage,
			"queries" => base64_encode($cacheContents)
			);
		
		file_put_contents($versionFilename, "<?php \$versionData = " . var_export($versionData, true) . "; ?>");	
	
		$this->clearCache();
		
		return $versionFilename;
		
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
		file_put_contents($this->getQueryCacheFileName(), "");	
	}
	
	/*
		->beginUpdate()
		Start a new update.
	*/
	public function beginUpdate() {
	
		// do we need to resume an update?
		if(file_exists($this->getUpdateCacheFilename())) {
			$this->resumeUpdate();
		}
		else {
		
			$versioning = new DIM_Versioning();
			$currentVersion = $versioning->getLatestVersion();
			
			// start by building an update cache..
			$updateCache = array();
			
			for($v = $currentVersion+1; $this->checkForVersionFile($v); $v++) {
				include($this->getVersionFileName($v));
				$updateCache[$v] = $versionData;
			}
			
			$this->saveUpdateCache($updateCache);
		
			// now we have a cache to run our update on...
			$this->resumeUpdate();			
		}
			
	}
	
	/*
		->saveUpdateCache($updateCache)
		Saves the update cache into a the file.
		@params
			$updateCache - the cache array.
	*/
	private function saveUpdateCache($updateCache) {
		file_put_contents($this->getUpdateCacheFilename(), "<?php \$updateCache = " . var_export($updateCache, true) . "; ?>");	
	}
	
	/*
		->resumeUpdate()
		Resume an already-running update - can run on any cache file (old or newly generated)
	*/
	public function resumeUpdate() {
		
		// creates $updateCache
		include($this->getUpdateCacheFilename());

		// sort it backwards so we can POP (which is a nicer sound than SHIFT)
		krsort($updateCache);
		
		$versioning = new DIM_Versioning();
		$database = new Database_IO($this->getDatabaseSettings());
		
		while($update = array_pop($updateCache)) {

			$database->query(base64_decode($update["queries"]), RETURN_NONE, true);
		
			$versioning->addNewVersion($update["version"], $update["commitMessage"]);
			
			// save the update cache now in case we die on the next iteration
			$this->saveUpdateCache($updateCache);
		}
	
		// if we get to here then all is well!
		unlink($this->getUpdateCacheFilename());
	
	}
	
}



?>