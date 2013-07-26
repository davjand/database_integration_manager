<?php


require_once(dirname(__FILE__).'/config.php');
	
class DatabaseTestingConnector {
	
	var $defaultConfig = array(
		'host' => 'localhost',
		'user' => 'root',
		'password' => '',
		'db' => 'dim-testing-sandbox',
		'port' => '3306',
		'tbl_prefix' => 'sym_'
	);
	var $dbConnection = null;
	var $tablePrefix = "";
	
	
	public function __construct($databaseConfig = null) {
	
		if($databaseConfig == null){
			$databaseConfig = $this->defaultConfig;
		}
	
		$this->dbConnection = new mysqli(
			$databaseConfig["host"],
			$databaseConfig["user"],
			$databaseConfig["password"],
			$databaseConfig["db"],
			$databaseConfig["port"]);

		if ($this->dbConnection->connect_errno) {
			echo "Failed to connect to MySQL: (" . $this->dbConnection->connect_errno . ") " . $this->dbConnection->connect_error;
		}
		
		if(!$this->dbConnection->select_db($databaseConfig['db'])){
			die('Could not select Database [' . $this->dbConnection->error . ']');
		}
		
		
		
	}
	
	public function __destruct(){
		if($this->dbConnection !== null){
			$this->dbConnection->close();
		}
	}
	
	/**
	
		Return the database connection
		
	*/
	public function getConnection(){
		return $this->dbConnection;
	}
	
	/**
	
		Return the config array
	
	*/
	public function getConfig(){
		return $this->defaultConfig;
	}
	
	/**
		
		Function to create a sample table in the database, used for testing connectivity etc
		
	*/
	public function createSampleTable($tableName = "test", $dropTables = false){
		if($dropTables){
			$this->dropAllTables();
		}
		
		$this->query(
				"CREATE TABLE IF NOT EXISTS `$tableName` (
					id INT NOT NULL AUTO_INCREMENT,
					test VARCHAR(100) NOT NULL,
					PRIMARY KEY(id)
				)");
		return;
	}
	
	
	/**
	
		Query the database
		
	*/
	public function query($sql){
		if(!$result = $this->dbConnection->query($sql)){
			die('There was an error running the query [' . $this->dbConnection->error . '] sql: ['.$sql.']');
		}
		return $result;
	}
	
	public function multiQuery($sql){
	
		if(!$this->dbConnection->multi_query($sql)){
			throw new Exception('There was an error running the query [' . $this->dbConnection->error . "]");
		}
		
		$data = array();
		$i = 0;
		do {
        	/* store first result set */
        	$data[$i] = array();
			if ($result = $this->dbConnection->store_result()) {
				$j = 0;
            	while ($row = $result->fetch_row()) {
            	    $data[$i][$j] = $row;
            	    $j++;
				}
				$result->free();
				$i++;
			}
		} while ($this->dbConnection->next_result());
		
		return $data;
	}
	
	
	/**
		
		Queries and returns an assoc array of all rows
		
	*/
	public function queryAndReturnAssoc($sql){
		$result = $this->query($sql);
		
		$data = array();
		
		while($row = $result->fetch_assoc()){
			array_push($data,$row);
		}
		return $data;
	}
	
	
	/**
	
		Function to drop all tables in the database
		
	*/
	public function dropAllTables(){
		$tables = $this->queryAndReturnAssoc('SHOW TABLES');
		
		if(is_array($tables) && count($tables) > 0){
			
			foreach($tables as $t){
				$tableName = array_values($t);
				$tableName = $tableName[0];
				
				$this->query('DROP TABLE `'.$tableName.'`');	
			}
			return true;
		}
		return false;		
	}
	
	/**
	
		Get the baseline SQL
		
	*/
	public function getBaselineSql(){
		$sql = file_get_contents(DIM_TEST_SQL);
		
		if($sql === false){
			die("Error Loading SQL Baseline file: [".DIM_TEST_SQL."]");
		}
		return $sql;	
	}
	
	/**
	
		Imports the baseline sql to use for test data
		
	*/
	public function importBaseline(){
	
		return $this->multiQuery($this->getBaselineSql());	
	}
	
}
	
?>