<?php
	require_once(EXTENSIONS . "/database_integration_manager/lib/client.class.php");
	require_once(EXTENSIONS . "/database_integration_manager/lib/base.class.php");
	require_once(EXTENSIONS . "/database_integration_manager/lib/statemanager.class.php");
	require_once(EXTENSIONS . "/database_integration_manager/lib/querymanager.class.php");
	require_once(EXTENSIONS . "/database_integration_manager/lib/versioning.class.php");
	
	class Extension_database_integration_manager extends Extension {

		var $config = null;

		/*
			->__construct()
		*/
		public function __construct() {
			parent::__construct();
			$this->config = new DIM_Base();
		}		
	
	
		/*
			->install()
			Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/extension/#install
		*/
		public function install() {
			/*
			
				MODIFYING THIS? ADD A VERSION UPDATE IN THE update() FUNCTION!
				
			*/
		
			try {
				Symphony::Database()->query('CREATE TABLE IF NOT EXISTS tbl_dim_versions (
											  `id` int(11) NOT NULL AUTO_INCREMENT,
											  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
											  `version` int(11) NOT NULL,
											  `state` varchar(100) NOT NULL,
											  `message` varchar(1024) NOT NULL,
											  PRIMARY KEY (`id`)
											  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;');

			} catch(Exception $e) { return false; }
			
			$this->createFolders();
			
			return true;
		
		}
		
		/*
			->createFolders()
			Create all folders required if they don't already exist
		*/
		public function createFolders(){
			//data folder
			if (!is_dir(DOCROOT.'/data')) {
			    mkdir(DOCROOT.'/data');
			}
			/*if (!is_dir(DOCROOT.'/data/global')) {
			    mkdir(DOCROOT.'/data/global');
			} NOT USED AT THE MOMENT */
			
			//config folder
			if (!is_dir(MANIFEST.'/dim')) {
			    mkdir(MANIFEST.'/dim');
			}
			//cache folder
			if (!is_dir(MANIFEST.'/dim/g_cache')) {
			    mkdir(MANIFEST.'/dim/g_cache');
			}	
		}
		
		public function checkWritableDirectories(){
			
			$dir = array();
			
			if(!is_writable(DOCROOT.'/data')){
				array_push($dir, DOCROOT.'/data');
			}
			if (!is_writable(MANIFEST.'/dim')) {
			    array_push($dir, MANIFEST.'/dim');
			}
			//cache folder
			if (!is_writable(MANIFEST.'/dim/g_cache')) {
			    array_push($dir, MANIFEST.'/dim/g_cache');
			}
			
			if(count($dir) > 0){
				$error = "Database Integration Manager Directories not writable: ";
				foreach($dir as $d){
					$error = $error.$d." , ";
				}
				
				throw new Exception($error);
			}
			
		}
		

		
		/*
			->update($previousVersion)
			Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/extension/#update
		*/
		public function update($previousVersion) {
			
			// fall through sequential updates
			switch($previousVersion) {
				case "0.0.1":
					Symphony::Database()->query('ALTER TABLE tbl_dim_versions ADD `state` varchar(100) NOT NULL;');
				case "0.0.2":
					Symphony::Database()->query('ALTER TABLE tbl_dim_versions ADD `message` varchar(1024) NOT NULL;');	
				case "0.0.3":
					
					break;
			}
			
			$this->createFolders();
	
		}
		
		/*
			->uninstall()
			Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/extension/#uninstall
		*/
		public function uninstall() {
		
			// if they've uninstalled this, then they're outside versioning so we need to delete this
			Symphony::Database()->query("DROP TABLE tbl_dim_versions;");
			
		}		
	
		/*
			->fetchNavigation()
			Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/extension/#fetchNavigation
		*/
		public function fetchNavigation(){ 
			return array(
				array(
					'location'	=> __('System'),
					'name'		=> __('Database Manager'),
					'link'		=> '/',
					'limit'		=> 'developer'
				),
				array(
					'location'	=> __('System'),
					'name'		=> __('Database Log'),
					'link'		=> '/log',
					'limit'		=> 'developer'
				)
			);
		}

		/*
			->getSubscribedDelegates()
			Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/extension/#getSubscribedDelegates
		*/
		public function getSubscribedDelegates() {
			return array(
				array(
					'page' => '/backend/',
					'delegate' => 'AppendPageAlert',
					'callback' => 'appendAlerts'
				),
				array(
					'page'		=> '/backend/',
					'delegate'	=> 'NavigationPreRender',
					'callback'	=> 'modifyNavigation'
				),
				array(
					'page' => '/frontend/',
					'delegate' => 'PostQueryExecution',
					'callback' => 'processQuery'
				),
				array(
					'page' => '/backend/',
					'delegate' => 'PostQueryExecution',
					'callback' => 'processQuery'
				)								
			);
		}
		
		
		/*
			->processQuery($context)
			Marshalls the query into the querymanager for processing
		*/
		public function processQuery($context) {
			$queryManager = new DIM_QueryManager();
			$queryManager->logNewQuery(trim($context["query"]));
		}
		
		/*
			->appendAlerts()
			Adds an alert to the administration pages if DIM is installed but not configured.
		*/
		public function appendAlerts($context) {
			
			$this->createFolders();//just to be on the safe side
			
			$this->checkWritableDirectories();
		
			if(!$this->config->isExtensionConfigured()) {
				Administration::instance()->Page->pageAlert(
					__('Database Integration Manager is installed but not configured. <a href=\'' . SYMPHONY_URL . '/extension/database_integration_manager\'>Configure it now</a>.'),
					Alert::ERROR
				);				
			}
			else {
				$versioning = new DIM_Versioning();
				if($versioning->databaseNeedsUpdating()) {
					Administration::instance()->Page->pageAlert(
						__("<a href='" . SYMPHONY_URL . "/extension/database_integration_manager/update'> Your Database Is Out Of Date Update It</a>."),
						Alert::ERROR
					);
				}
			}
		}
		
		/*
			->modifyNavigation($navigation)
			Modify the Symphony admin navigation according to the current mode.
		*/
		public function modifyNavigation(&$navigation) {
			if($this->config->isExtensionConfigured()) {
				switch($this->config->getExtensionMode()) {
					case "client":
						$stateManager = new DIM_StateManager("client");
						if(!$stateManager->isCheckedOut()) {
							// clear out the blueprints
							$navigation["navigation"][200] = array();	
							
							//remove extensions menu item
							foreach($navigation["navigation"][100]['children'] as $key => $val){
								if($val['link']=='/system/extensions/'){
									$navigation["navigation"][100]['children'][$key]['visible']='no';
								}
							}
						}
						break;
						
					case "server":						
						// clear out the blueprints
						$navigation["navigation"][200] = array();
						
						//remove extensions menu item
						foreach($navigation["navigation"][100]['children'] as $key => $val){
							if($val['link']=='/system/extensions/'){
								$navigation["navigation"][100]['children'][$key]['visible']='no';
							}
						}
						
						
						break;
					case "disabled":
						
						// we're disabled - don't do anything!
						break;
				}			
			}
			else {
				// clear all navigation items - the user will be able to get to the config via the alert.
				$navigation["navigation"] = array();
			}		
		}		

		/*
			::testSettings($settings)
			Run tests on the user-supplied settings to determine their integrity.
			@params
				$settings - the settings array supplied by the user
			@returns
				true/false based on test result
		*/
		public static function testSettings($settings) {
			$config = new DIM_Base();
			if($config->getDatabaseSettings() != null) {
			
				switch($settings["mode"]["mode"]) {
					case "client":
						return DIM_Client::testClientSettings($settings["client"]);
						break;
					case "server":
						// PASSED - no settings needed
						return true;
						break;
					case "disabled":
						// PASSED - no settings needed
						return true;
						break;
					default:
						// FAILED - something weird happened!					
						return false;
						break;
				}
				
			}
			else {
				return false;
			}
		}
		
	}

?>