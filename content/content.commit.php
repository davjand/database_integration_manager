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


require_once(EXTENSIONS . '/database_integration_manager/lib/client.class.php');
require_once(EXTENSIONS . '/database_integration_manager/lib/base.class.php');
require_once(EXTENSIONS . '/database_integration_manager/lib/logger.class.php');
require_once(EXTENSIONS . '/database_integration_manager/lib/querymanager.class.php');
require_once(EXTENSIONS . '/database_integration_manager/lib/versioning.class.php');

class contentExtensionDatabase_integration_managerCommit extends AdministrationPage	
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
	
		$client = new DIM_Client();
		
		
		
		if($client->requestCheckin(&$errorStr, $_POST['checkin']['version'], $_POST['checkin']['message'])) {
			$this->pageAlert(__('Database checked in!'), Alert::SUCCESS);			
		}
		else {
			$this->pageAlert(__("Checkin Failed - '{$errorStr}'"), Alert::ERROR);					
		}

	}
	
	/*
		->__indexPage()
		Constructs the index page via nested XMLElements and populates $this->Form.
	*/
	private function __indexPage() {

		$versioning = new DIM_Versioning();
	
		$link = new XMLElement('link');
		$this->addElementToHead($link, 500);	
		
		$this->setPageType('form');
		$this->appendSubheading(__('Database Check-In'));	
		
		$checkinFieldset = new XMLElement('fieldset');
		$checkinFieldset->setAttribute("class", "settings picker");
		$checkinFieldset->appendChild(new XMLElement('legend', __("Check-In")));		
		$versionLabel = Widget::Label("Version");
		$versionLabel->appendChild(Widget::Input("checkin[version]", ""));
		$checkinFieldset->appendChild($versionLabel);
		$messageLabel = Widget::Label("Commit Message");
		$messageLabel->appendChild(Widget::Input("checkin[message]", ""));
		$checkinFieldset->appendChild($messageLabel);
		
		$this->Form->appendChild($checkinFieldset);
		
		$saveDiv = new XMLElement('div');
		$saveDiv->setAttribute('class', 'actions');
		$saveDiv->appendChild(Widget::Input('action[checkin]', __('Check-In!'), 'submit', array('accesskey' => 's')));
		$this->Form->appendChild($saveDiv);			
		
	}
}

?>