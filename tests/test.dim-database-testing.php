<?php
	
	/**
	
		DIM Database Testing
	
		Tests for the test code!
	
	*/
	class SymphonyTestDimDatabaseTesting extends UnitTestCase {
		
		public function setUp() {
			require_once EXTENSIONS . '/database_integration_manager/tests/lib/database.testing.connector.php';
		}
		
		
		public function testCanConnect() {
			$databaseConnector = new DatabaseTestingConnector();
			$c = $databaseConnector->getConnection();
			
			$this->assertEqual($c->connect_errno,0);
			
		}
		
		/**
		
			Test what happens if multiple occur at the same time
			
		*/
		public function testCanMultiConnect(){
			$db1 = new DatabaseTestingConnector();	
			$db2 = new DatabaseTestingConnector();
			
			$db1C = $db1->getConnection();
			$db2C = $db2->getConnection();
			
			$this->assertEqual($db1C->connect_errno,0);	
			$this->assertEqual($db2C->connect_errno,0);	
			
			
			/*
				Create tables with 1 and query with 2
				
				Used to check that this won't interfere when using multiple mysqli connections for actual testing
			
			*/
				
			$db1->createSampleTable("test",true);
			$db1->createSampleTable("test_two");
			

			$this->assertEqual(count($db2->queryAndReturnAssoc('SHOW TABLES')),2);
			$this->assertEqual(count($db1->queryAndReturnAssoc('SHOW TABLES')),2);
		}
		
		
		public function testCanDropAllTables(){
			$dbConnection = new DatabaseTestingConnector();
			
			$dbConnection->createSampleTable("test",true);
			$dbConnection->createSampleTable("test_two");
			
			//check the test is actually working
			$res = $dbConnection->queryAndReturnAssoc("SHOW TABLES");

			$this->assertEqual(count($res),2);
			
			/*
				test starts
			*/
			
			$dbConnection->dropAllTables();
			
			$resSecond = $dbConnection->queryAndReturnAssoc("SHOW TABLES");
			$this->assertEqual(count($resSecond),0);
			
		}
		
		
		public function testCanImportBaselineSQL(){
			$dbConnection = new DatabaseTestingConnector();
			
			$dbConnection->dropAllTables(); //clear just to make sure
			$dbConnection->importBaseline();
			
			$testRes = $dbConnection->queryAndReturnAssoc('SHOW TABLES');
			$this->assertEqual(count($testRes),DIM_TEST_SQL_TABLE_COUNT);	
			
			$dbConnection->dropAllTables(); 
		}
		
		/**

			Check that it can do it multiple times per connection
			
			May be related to a bug that we're trying to fix
			
		*/
		public function testCanImportBaselineMultipleTimes(){
			$dbConnection = new DatabaseTestingConnector();
			
			$dbConnection->dropAllTables(); //clear just to make sure
			$dbConnection->importBaseline();
			
			$testRes = $dbConnection->queryAndReturnAssoc('SHOW TABLES');
			$this->assertEqual(count($testRes),DIM_TEST_SQL_TABLE_COUNT);
			
			
			/* ROUND 2 */
			$dbConnection->dropAllTables(); //clear just to make sure
			$dbConnection->importBaseline();
			
			$testRes = $dbConnection->queryAndReturnAssoc('SHOW TABLES');
			$this->assertEqual(count($testRes),DIM_TEST_SQL_TABLE_COUNT);	
			
			/* ROUND 3 */
			$dbConnection->dropAllTables(); //clear just to make sure
			$dbConnection->importBaseline();
			
			$testRes = $dbConnection->queryAndReturnAssoc('SHOW TABLES');
			$this->assertEqual(count($testRes),DIM_TEST_SQL_TABLE_COUNT);	
			
			$dbConnection->dropAllTables(); 
		}
		
	}
	
?>