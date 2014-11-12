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
require_once(EXTENSIONS . '/database_integration_manager/lib/client.class.php');
require_once(EXTENSIONS . '/database_integration_manager/lib/base.class.php');
require_once(EXTENSIONS . '/database_integration_manager/lib/statemanager.class.php');
require_once(EXTENSIONS . '/database_integration_manager/lib/logger.class.php');

class contentExtensionDatabase_integration_managerIndex extends AdministrationPage	
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
        $this->setTitle('Symphony - DIM Configuration');
		
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
		if(isset($_POST["action"]["save"])) {

			// transform the users array...
			if(is_array($_POST["settings"]["server"]["users"])) {
				$transformedArray = array();
				for($i=0;$i<count($_POST["settings"]["server"]["users"]["firstname"]);$i++) {
					$userArray = array(
							"firstname" => $_POST["settings"]["server"]["users"]["firstname"][$i],
							"lastname" => $_POST["settings"]["server"]["users"]["lastname"][$i],
							"email" => $_POST["settings"]["server"]["users"]["email"][$i],
							"created-by" => $_POST["settings"]["server"]["users"]["created-by"][$i],
						);
					// generate the authentication key
					$passedAuthKey = $_POST["settings"]["server"]["users"]["auth-key"][$i];
					$userArray["auth-key"] = ($passedAuthKey == "" ? DIM_Server::generateAuthenticationKey($userArray) : $passedAuthKey); 
					$transformedArray[] = $userArray;					
				}			
				$_POST["settings"]["server"]["users"] = $transformedArray;
			}			
			
			//ensure the mode is set in the settings
			if(!isset($_POST['settings']['mode']['mode'])){
				$ss = $this->config->getConfiguration();
				$_POST['settings']['mode']['mode'] = $ss['mode']['mode'];
			}
			
			
			if(extension_database_integration_manager::testSettings($_POST["settings"])) {
				$logger = new DIM_Logger();
				$logger->addLogItem("Configuration Updated", "system");
				
				$this->config->saveConfiguration($_POST["settings"]);
				$this->pageAlert(__('Configuration Settings updated successfully.'), Alert::SUCCESS);			
			}
			else {
				$this->pageAlert(__('One or more settings were incorrect.'), Alert::ERROR);
				$_POST['error']='error';	
			}

		}
	}
	
	/*
		->__indexPage()
		Constructs the index page via nested XMLElements and populates $this->Form.
	*/
	private function __indexPage() {

		$link = new XMLElement('link');
		$this->addElementToHead($link, 500);	
		
		$this->setPageType('form');
		$this->appendSubheading(__('Database Integration Manager'));		
		
		// Checkout/in?
		if(isset($_GET["try"])) {
			$client = new DIM_Client();
			$errorStr = "";
			switch($_GET["try"]) {
				case "checkout":
					
					if($client->requestCheckout($errorStr)) {
						redirect('?message=checkout-success');			
					}
					else {
						$this->pageAlert(__("Checkout Failed - '{$errorStr}'"), Alert::ERROR);					
					}
					break;
			}
		}
		
		if(isset($_GET["message"])){
			switch($_GET['message']){
				case "checkout-success":
					$this->pageAlert(__('Database Checkout Out Successfully'), Alert::SUCCESS);
					break;
				case "commit-success":
					$this->pageAlert(__('Database Checked In Successfully'), Alert::SUCCESS);
					break;
				case "update-success":
					$this->pageAlert(__('Database Updated Successfully'), Alert::SUCCESS);
					break;
			}
		}	
		
		// Get the saved settings from the file - this will populate $savedSettings
		$savedSettings = $this->config->getConfiguration();
		
		
		/***************************************
		
			!Mode Selector
			
		***************************************/
		
		
		// The mode is the 'picker' - nice UI and also necessary for validation functioning
		
		//Should the mode be locked or not
		$modeLocked = $savedSettings && ($savedSettings['mode']['mode'] == $_POST['settings']['mode']['mode'] || !isset($_POST['settings']['mode']['mode']));
		
		// Build the 'Mode' fieldset
		
		$modeFieldset = new XMLElement('fieldset','',array('class'=>'settings picker'));
		$modeFieldset->appendChild(new XMLElement('legend', __("Mode")));
		$modeSelectorLabel = Widget::Label("Mode");
		

		//fill with POST data if possible
		$modeSelected = '';
		if(isset($_POST['settings']['mode']['mode'])){
			$modeSelected = $_POST['settings']['mode']['mode'];
		}
		elseif(isset($savedSettings['mode']['mode'])){
			$modeSelected = $savedSettings['mode']['mode'];
		}
		
		$modeSelectorOptions = array(
									array("disabled", ($modeSelected == "disabled"), "Disabled"),
									array("client", ($modeSelected == "client"), "Client"),
									array("server", ($modeSelected == "server"), "Server")
								);


		$selectOptions = array("id" => "mode-selector");
		if($modeLocked) {$selectOptions["disabled"] = "disabled";}
		
		$modeSelectorLabel->appendChild(Widget::Select("settings[mode][mode]", $modeSelectorOptions, $selectOptions));
		
		$modeWrapper = new XMLElement('div',
				new XMLElement('div',$modeSelectorLabel, array('class'=>'column')),
			array('class'=>'two columns')
			);
		
		if($modeLocked) {
			// The enabler button
			$modeChangerLabel = Widget::Label("Mode Currently Locked");
			$modeChangerLabel->appendChild(new XMLElement('br'));
			$modeChangerLabel->appendChild(
				Widget::Input("mode-enabler", "Allow Mode to Be Changed", "button", array("id" => "mode-enabler", "class" => "button"))
			);
			
			$modeWrapper->appendChild(new XMLElement('div',
					$modeChangerLabel,
				array('class'=>'column')
			));
			// The enabler script
			$this->Form->appendChild(new XMLElement('script', 'jQuery(document).ready(function(){jQuery("#mode-enabler").click(function() {  jQuery("#mode-selector").removeAttr("disabled"); jQuery(this).parent().hide(); });});'));
		}
		
		$modeFieldset->appendChild($modeWrapper);
		
		$this->Form->appendChild($modeFieldset);
		$this->Form->appendChild(new XMLElement('script', 'jQuery(document).ready(function(){jQuery("#mode-selector").symphonyPickable();});'));
		
		
		// These below are the 'pickable' blocks

		/***************************************
		
			!Client Settings
			
		***************************************/
		
		//Build an array of settings so that the form can be prepopulated with settings || $_POST
		if(is_array($savedSettings['client'])){
			$clientSettings = $savedSettings['client'];	
		}
		else{
			$clientSettings = array();
		}
		
		//fill the form with post data if possible
		if(is_array($_POST['settings']['client'])){
			foreach($_POST['settings']['client'] as $cKey => $cVal){
				if(!array_key_exists($cKey, $clientSettings)){
					$clientSettings[$cKey]=$cVal;
				}
			}
		}
		
		//an encompassing object for the entire client settings
		$clientWrapper = new XMLElement('div','',array('id'=>'client', "class" => "pickable", 'style'=>'border-top: 1px solid rgba(0, 0, 0, 0.1);'));
		
		
		//Check in / out interface
		if($savedSettings && $savedSettings['mode']['mode']=='client'){
			
			$clientActionFieldset = new XMLElement('fieldset','',array('class'=>'settings'));
			$clientActionFieldset->appendChild(new XMLElement('legend',__('Status')));
		
			$stateManager = new DIM_StateManager("client");
			$stateText = "";
			$linkText = "";
			if($stateManager->isCheckedOut()) {
				$stateText = "Checked Out";
				$linkText = "<a class='button' href='" . SYMPHONY_URL . "/extension/database_integration_manager/commit'>Check In</a>";		
			}
			else {
				$stateText = "Checked In";
				$linkText = "<a class='button' href='?try=checkout'>Check Out</a>";			
			}
			$clientActionFieldset->appendChild(new XMLElement('div', "{$linkText} &nbsp;&nbsp;&nbsp;&nbsp; Current State: <strong>{$stateText}</strong>", array("class" => "frame")));
			
			$clientWrapper->appendChild($clientActionFieldset);			
		}
		
		
		//Client Authentication Settings
		$clientFieldset = new XMLElement('fieldset','',array('class'=>'settings'));
		$clientFieldset->appendChild(new XMLElement('legend',__('Client')));
		
		$liveServerUrlLabel = Widget::Label("Live Server Host or IP (can append a subdirectory if required)");
		$liveServerUrlLabel->appendChild(Widget::Input("settings[client][server-host]", $clientSettings["server-host"]));
		
		$emailAddressLabel = Widget::Label("Email Address");
		$emailAddressLabel->appendChild(Widget::Input("settings[client][user-email]", $clientSettings["user-email"]));
		
		$authKeyLabel = Widget::Label("Authentication Key");
		$authKeyLabel->appendChild(Widget::Input("settings[client][auth-key]", $clientSettings["auth-key"]));
		
		
		$clientFieldWrapper = new XMLElement('div');
		$clientFieldWrapper->appendChild($liveServerUrlLabel);
		
		$clientCredentials = new XMLElement('div','',array('class'=>'two columns'));
		
		$clientCredentials->appendChild(new XMLElement('div',$emailAddressLabel,array('class'=>'column','style'=>'margin-bottom: 0px;')));
		$clientCredentials->appendChild(new XMLElement('div',$authKeyLabel,array('class'=>'column', 'style'=>'margin-bottom: 0px;')));
		$clientFieldWrapper->appendChild($clientCredentials);
		
		//see if error
		if(isset($_POST['error']) && $_POST['settings']['mode']['mode'] == 'client'){
			$clientFieldWrapper->setAttribute('class','invalid');
			$clientFieldWrapper->appendChild(
				new XMLElement('p','Invalid Settings: Please reconfigure')
			);	
		}
		$clientFieldset->appendChild($clientFieldWrapper);		
		
		
		
		$clientWrapper->appendChild($clientFieldset);
		$this->Form->appendChild($clientWrapper);

		
		/***************************************
		
			!Server Settings
			
		***************************************/
			
		$serverFieldset = new XMLElement('fieldset');
		$serverFieldset->setAttribute("class", "settings pickable");
		$serverFieldset->setAttribute("id", "server");

		$stateManager = new DIM_StateManager("server");
		$stateText = ($stateManager->isCheckedOut() ? "Checked Out" : "Checked In");
		
		$serverFieldset->appendChild(new XMLElement('div', "<a class='button' href='log'>View Log</a> &nbsp;&nbsp;&nbsp;&nbsp; Current State: <strong>{$stateText}</strong>", array("class" => "frame", "style"=>"padding: 20px;")));	
		
		$this->Form->appendChild(new XMLElement('script', 
			'jQuery(document).ready(function(){
					jQuery("#users-duplicator").symphonyDuplicator({
						orderable: true, 
						collapsible: true
					});
					
					//jQuery("li.field-user.instance").addClass("collapsed").find("div.content").hide();
					
				});				
			'));		
		
		$serverUserFrame = new XMLElement('div');
		
		$usersWrapper = new XMLElement('div',null, array(
			'class'=>'frame',
			'id'=>'users-duplicator'
		));
		$usersWrapper->setAttribute('data-add', __('Add User'));
		$usersWrapper->setAttribute('data-remove', __('Remove User'));
		
		
		$ol = new XMLElement('ol');

		if(is_array($savedSettings["server"]["users"])) {
			foreach($savedSettings["server"]["users"] as $u) {
				$ol->appendChild($this->__getUserInputBlock($u));
			}
		}
		
		// append the template
		$ol->appendChild($this->__getUserInputBlock(array(), true));
		
		$usersWrapper->appendChild($ol);
		$serverUserFrame->appendChild($usersWrapper);
		$serverFieldset->appendChild($serverUserFrame);
		
		$this->Form->appendChild($serverFieldset);
		
		/***************************************
		
			!Default Settings
			
		***************************************/
		
		// Default/Disabled Settings Block
		$disabledFieldset = new XMLElement('div','', array('class'=>'pickable','id'=>'disabled'));
		$this->Form->appendChild($disabledFieldset);		
		
		// Add the 'Save' button
		$saveDiv = new XMLElement('div');
		$saveDiv->setAttribute('class', 'actions');
		$saveDiv->appendChild(Widget::Input('action[save]', __('Save Settings'), 'submit', array('accesskey' => 's')));
		$this->Form->appendChild($saveDiv);		
	}
	
	/*
		->__getUserInputBlock($data)
		Returns an XML element containing all the user elements populated with the data specified
		@params
			$data - an array of user data, can be empty
			$template - should this be a template block?
		@return
			XMLElement - the li for the block
	*/
	private function __getUserInputBlock($data, $template = false) {
	
		$wrapper = new XMLElement('li', NULL, array('class' =>  ($template ? 'template' : null) . ' field-user'));
		$wrapper->setAttribute('data-type', 'user');
		$header = new XMLElement('header', ($template ? '<strong>New User</strong>' : $data['firstname'] . ' ' . $data['lastname']) , array("class" => "main"));
		$wrapper->appendChild($header);
		
		$columnsWrapper = new XMLElement('div','',array('class'=>'two columns'));
		
		$serverUserFirstnameLabel = Widget::Label("First Name",
			Widget::Input("settings[server][users][firstname][]", $data['firstname']),
			'column');
		
		$serverUserLastnameLabel = Widget::Label("Last Name",
			Widget::Input("settings[server][users][lastname][]", $data['lastname']),
			'column');

		$serverUserEmailLabel = Widget::Label("Email",
			Widget::Input("settings[server][users][email][]", $data['email']),
			'column');

		
		$createdBy = $data['created-by'];
		if(empty($createdBy)){
			$createdBy = Administration::instance()->Author->getFullName();	
		}
		$serverUserCreatedByLabel = Widget::Label("Created By",
			Widget::Input("settings[server][users][created-by][]",$createdBy),
			'column');
		
		$serverUserAuthKeyLabel = Widget::Label("Authentication Key (leave blank to auto-generate)",
			Widget::Input("settings[server][users][auth-key][]", ($template ? "" : $data['auth-key']), "text"),
			'column');
		
		$columnsWrapper->appendChild($serverUserFirstnameLabel);
		$columnsWrapper->appendChild($serverUserLastnameLabel);
		$columnsWrapper->appendChild($serverUserEmailLabel);
		$columnsWrapper->appendChild($serverUserCreatedByLabel);
		$columnsWrapper->appendChild($serverUserAuthKeyLabel);
		
		$wrapper->appendChild($columnsWrapper);
		
		return $wrapper;
	
	}
}

?>