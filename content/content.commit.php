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
        $this->setTitle('Symphony - Database Check In');
		
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
		$versioning = new DIM_Versioning();
		
		$error = false;
		
		if(empty($_POST['checkin']['version']) && $_POST['checkin']['version'] != '0'){
			$_POST['error']['version']='empty';
			$error = true;
		}
		elseif(!is_numeric($_POST['checkin']['version'])){
			$_POST['error']['version']='invalid';
			$error = true;
		}
		elseif(floatval($_POST['checkin']['version']) <= $versioning->getLatestVersion() ){
			$_POST['error']['version']='less-than-version';
			$error = true;
		}
		
		if(empty($_POST['checkin']['message'])){
			$_POST['error']['message']='empty';
			$error = true;
		}
		
		
		if(!$error){
			if($client->requestCheckin($errorStr, $_POST['checkin']['version'], $_POST['checkin']['message'])) {
				
				$_POST['success']='success';
				redirect('../?message=commit-success');
			}
			else {
				$this->pageAlert(__("Checkin Failed - '{$errorStr}'"), Alert::ERROR);					
			}
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
		$this->appendSubheading(__('Database Check In'));	
		
		$checkinFieldset = new XMLElement('fieldset');
		$checkinFieldset->setAttribute("class", "settings picker");
		$checkinFieldset->appendChild(new XMLElement('legend', __("Version Information")));		
		
		if(isset($_POST['success'])){
			$successWrapper = new XMLElement('div','',array('class'=>'two columns'));
			
			$successWrapper->appendChild( new XMLElement('div',
				new XMLElement("h3","Database Checked in"),
				array('class'=>'column')
			));
			
			$successWrapper->appendChild( new XMLElement('div',
				new XMLElement('div','<a class="button" href="../">Return to Configuration</a>'),
				array('class'=>'column')
			));
			
			$checkinFieldset->appendChild($successWrapper);
			$this->Form->appendChild($checkinFieldset);			
		}
		else{
		
			
			
			//pre fill information
			
			$versionData = $versioning->getLatestVersion() + 1;
			
			if(isset($_POST['checkin']['version'])){
				$versionData = $_POST['checkin']['version'];
			}
			
			$versionLabel = Widget::Label("Version");
			$versionLabel->appendChild(Widget::Input("checkin[version]", strval($versionData)));
	
			$messageLabel = Widget::Label("Commit Message");
			$messageLabel->appendChild(Widget::Input("checkin[message]", $_POST['checkin']['message']));
			
			//if errors
			
			if(isset($_POST['error']['version'])){
				$versionLabel->setAttribute('class','invalid');
				
				$versionError = "Please enter a version number (Current Version = ".$versioning->getLatestVersion().')';
				switch($_POST['error']['version']){
					case 'invalid':
						$versionError='Invalid Version Number, must be a number (Current Version = '.$versioning->getLatestVersion().')';
						break;
					case 'less-than-version':
						$versionError='Version already exists or is less than current version( Current Version = '.$versioning->getLatestVersion().')';
						break;
				}
				
				$versionLabel->appendChild(new XMLElement('p',$versionError));
			}
			
			if(isset($_POST['error']['message'])){
				$messageLabel->setAttribute('class','invalid');
				$messageLabel->appendChild(new XMLElement('p','Please enter a commit message'));
			}
			
			
			$columnWrapper = new XMLElement('div','',array('class'=>'two columns'));
			$columnWrapper->appendChild(new XMLElement('div',$versionLabel,array('class'=>'column', "readonly"=>'readonly')));
			$columnWrapper->appendChild(new XMLElement('div',$messageLabel,array('class'=>'column')));
			
			$checkinFieldset->appendChild($columnWrapper);
			$this->Form->appendChild($checkinFieldset);
			
			$saveDiv = new XMLElement('div');
			$saveDiv->setAttribute('class', 'actions');
			$saveDiv->appendChild(Widget::Input('action[checkin]', __('Check-In!'), 'submit', array('accesskey' => 's')));
			$this->Form->appendChild($saveDiv);
		}
	}
}

?>