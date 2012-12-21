<?php

/*
	DIM_Database
	
	Our own database driver - to take us away from having to use Symphony as it
	causes conflicts!
*/

class DIM_Database {

	var $dbConnection = null;
	var $tablePrefix = "";

	/*
		query constants
	*/
	const RETURN_VALUE = "1";
	const RETURN_OBJECTS = "2";
	const RETURN_NONE = "3";
	
	
	/*
		->__construct($databaseParams)
		Constructor
		@params
			$databaseParams - the details of the database to connect to. Usually just what is in the Symphony config.
	*/
	public function __construct($databaseParams) {
		$this->dbConnection = mysql_connect($databaseParams["host"] . ":" . $databaseParams["port"], $databaseParams["user"], $databaseParams["password"]);
		mysql_select_db($databaseParams["db"], $this->dbConnection);
		
		$this->tablePrefix($databaseParams["tbl_prefix"]);
	}
	
	/*
		->query($sql, $returnMode)
		Run a query against the current database.
		@params
			$sql - the SQL statement to run.
			$mode - the return mode of the query, see constants defined above
	*/
	public function query($sql, $returnMode) {
	
		// transform the table prefixes first..
		$sql = str_replace("tbl_", $this->tablePrefix, $sql);

	
	}
	
	/*
		::sanitize($sql, $level)
		Sanitize the SQL string passed.
		@params 
			$sql - the SQL statement or partial statement to santize
			$level - the level to which the statement should be sanitized from 1 - weak to 4 - strong (optional, defaults to 1)
		@returns
			string - the sanitized SQL
	*/
	public static function sanitize($sql, $level = 1) {
		// deliberately fall through the cases, ensures one level doesn't accidentally create an attack vector that would
		// then bypass another filter.
		switch($level) {
			case 4:
				// only allow specific characters through!
				$allowedChars = explode("", "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789");
				$sqlSplit = explode("", $sql);
				
				// we can go forwards because we're not going to remove the array elements, just set them to empty (makes
				// it easier for my poor brain)
				for($a=0;$a<count($sqlSplit);$a++) {
					if(!in_array($sqlSplit[$a], $allowedChars)) {
						$sqlSplit[$a] = "";
					}
				}
				
				//rebuild the SQL
				$sql = "";
				foreach($sqlSplit as $s) {
					$sql .= $s;
				}
				
			case 3:
				// remove common (and almost always legitimate) SQL keywords... and then fall through to remove the risky ones
				
				$sql = str_ireplace(array("SELECT", "WHERE", "DISTINCT", "ORDER BY"), "", $sql);
				
			case 2:
				// remove more risky SQL keywords
				
				$sql = str_ireplace(array("UPDATE", "DELETE", "TRUNCATE", "INSERT", "JOIN", "UNION", "HAVING", "CREATE"), "", $sql);
				
			case 1:
				// just escape the string
				$sql = mysql_real_escape_string($sql);
				break;
		}
		return $sql;	
	}		
		
}


?>