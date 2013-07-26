<?php
	
	require_once(dirname(__FILE__).'/lib/config.php');
	
	/**
	
		DIM Database IO testing
	
		lib/io/database.class.php tests
	
	*/
	class SymphonyTestIoDatabaseClass extends UnitTestCase {
		
		public function setUp() {
			require_once DIM_TEST_ROOT . '/lib/database.testing.connector.php';
			require_once DIM_TEST_ROOT . '/../lib/io/database.class.php';
		}
		
		
		public function testCanConnect() {
			$databaseConnector = new DatabaseTestingConnector();
			$databaseIO = new Database_IO($databaseConnector->getConfig());
			
			/**
				check connectivity to both DB
			*/
			
			$c = $databaseConnector->getConnection();			
			$this->assertEqual($c->connect_errno,0);
			
			
			$d = $databaseIO->getConnection();			
			$this->assertEqual($d->connect_errno,0);
			
			/*
				check that the databaseIO can actually perform operations
			*/
			$res = $databaseIO->query("CREATE TABLE IF NOT EXISTS `test` (
					id INT NOT NULL AUTO_INCREMENT,
					test VARCHAR(100) NOT NULL,
					PRIMARY KEY(id)
				)",
					RETURN_VALUE,true);
				
			$this->assertEqual(count($databaseConnector->queryAndReturnAssoc('SHOW TABLES')),1);
			$databaseConnector->dropAllTables();
			
		}
		
		public function testCanMultiQuery(){
			$dbC = new DatabaseTestingConnector();
			$databaseIO = new Database_IO($dbC->getConfig());
			$dbC->dropAllTables();

			$databaseIO->query($dbC->getBaselineSql(),MULTI_QUERY, true);
			$this->assertEqual(count($dbC->queryAndReturnAssoc('SHOW TABLES')),DIM_TEST_SQL_TABLE_COUNT);
			
			$dbC->dropAllTables();
			$databaseIO->query($dbC->getBaselineSql(),MULTI_QUERY, true);
			$this->assertEqual(count($dbC->queryAndReturnAssoc('SHOW TABLES')),DIM_TEST_SQL_TABLE_COUNT);
			
			
			
			//to finish
			$dbC->dropAllTables();
		}
	}
	
?>