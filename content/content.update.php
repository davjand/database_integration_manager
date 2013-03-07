<?php

require_once(TOOLKIT . '/class.xsltpage.php');
require_once(TOOLKIT . '/class.administrationpage.php');

require_once(TOOLKIT . '/class.sectionmanager.php');
require_once(TOOLKIT . '/class.fieldmanager.php');
require_once(TOOLKIT . '/class.entrymanager.php');
require_once(TOOLKIT . '/class.entry.php');
//require_once(EXTENSIONS . '/extension_installer/lib/extension-data.class.php');

require_once(TOOLKIT . '/class.datasource.php');
require_once(TOOLKIT . '/class.datasourcemanager.php');

require_once(CORE . '/class.cacheable.php');
require_once(CORE . '/class.administration.php');


require_once(EXTENSIONS . '/database_integration_manager/lib/server.class.php');
require_once(EXTENSIONS . '/database_integration_manager/lib/base.class.php');
require_once(EXTENSIONS . '/database_integration_manager/lib/logger.class.php');
require_once(EXTENSIONS . '/database_integration_manager/lib/querymanager.class.php');
require_once(EXTENSIONS . '/database_integration_manager/lib/versioning.class.php');

class contentExtensionDatabase_integration_managerUpdate extends AdministrationPage	
{	

	var $config = null;

	/*
		->__construct()
	*/
	public function __construct() {
		parent::__construct();
		$this->config = new DIM_Base();
	}

	/*	
		->build()
		Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/administrationpage#build
	*/
    public function build() {
        parent::build();
        $this->setTitle('Symphony - Database Updater');
		
    }

	/*	
		->about()
		Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/administrationpage#about
	*/
	public function about() {
	
	}

	/*	
		->view()
		Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/administrationpage#view
	*/	
    public function view()
    {
		$this->__indexPage();
    }
	
	
	/*	
		->action()
		Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/administrationpage#action
	*/	
	public function action() {

	
	}
	
	/*
		->__indexPage()
		Constructs the index page via nested XMLElements and populates $this->Form.
	*/
	private function __indexPage() {

		$versioning = new DIM_Versioning();
	
		$link = new XMLElement('link');
		$this->addElementToHead($link, 500);	
		
		$this->setPageType('table');
		$this->appendSubheading(__('Database Updating'));
		
		$fieldSet = new XMLElement('fieldset','',array('class'=>'settings'));
		$fieldSet->appendChild(new XMLElement('legend','Database Update'));
		
		$message = new XMLElement('div');	
		
		if($versioning->databaseNeedsUpdating()) {
			
			$querymanager = new DIM_QueryManager();
			$querymanager->beginUpdate();
			$message->appendChild(new XMLElement('h2', "Update Completed!"));
			redirect('../?message=update-success');
		}
		else {
			$message->appendChild(new XMLElement('h2', "Database Already Up To Date"));		
		}
		
		$fieldSet->appendChild($message);
		$this->Form->appendChild($fieldSet);
		
	}
}

?>