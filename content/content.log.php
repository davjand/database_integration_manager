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

class contentExtensionDatabase_integration_managerLog extends AdministrationPage	
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
        $this->setTitle('Symphony - DIM Log');
		
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
		
		$link = new XMLElement('link');
		$this->addElementToHead($link, 500);	
		
		$this->setPageType('table');
		$this->appendSubheading(__('DIM Log'));		
		
		$aTableHead = array(
			array("Text", "col"),
			array("Type", "col"),
			array("Timestamp", "col")
			);
		
		$logger = new DIM_Logger();

		$aTableBody = array();
		foreach($logger->readLog() as $i) {
			$td1 = Widget::TableData($i["text"]);
			$td2 = Widget::TableData($i["class"]);
			$td3 = Widget::TableData($i["timestamp"]);
		
			$aTableBody[] = Widget::TableRow(array($td1, $td2, $td3));
		
		}
		
		$this->Form->appendChild(
			Widget::Table(
				Widget::TableHead($aTableHead), null, Widget::TableBody($aTableBody), ""
				)
			);
		
	}
}

?>