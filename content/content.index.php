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
require_once(EXTENSIONS . '/database_integration_manager/lib/configuration.class.php');

class contentExtensionDatabase_integration_managerIndex extends AdministrationPage	
{	
	/*	
		->build()
		Symphony Override - see http://getsymphony.com/learn/api/2.3/toolkit/administrationpage#build
	*/
    public function build()
    {
        parent::build();
		$this->setPageType('form');
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
					$userArray["auth-key"] = ($passedAuthKey == "save-to-generate" ? DIM_Server::generateAuthenticationKey($userArray) : $passedAuthKey); 
					$transformedArray[] = $userArray;					
				}			
				$_POST["settings"]["server"]["users"] = $transformedArray;
			}			
			
			if(extension_database_integration_manager::testSettings($_POST["settings"])) {
				DIM_Configuration::saveConfiguration($_POST["settings"]);
				$this->pageAlert(__('Configuration Settings updated successfully.'), Alert::SUCCESS);			
			}
			else {
				$this->pageAlert(__('One or more settings were incorrect.'), Alert::ERROR);			
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
		$this->appendSubheading(__('DIM Configuration'));		

		
		// Get the saved settings from the file - this will populate $savedSettings
		$savedSettings = array();
		if(extension_database_integration_manager::isExtensionConfigured()) {
			include(extension_database_integration_manager::getExtensionConfigPath());
		}
		
		// The mode is the 'picker' - nice UI and also necessary for validation functioning
		
		// Add the picker script
		$this->Form->appendChild(new XMLElement('script', 'jQuery(document).ready(function(){jQuery("select").symphonyPickable();});'));
		
		// Build the 'Mode' fieldset
		$modeFieldset = new XMLElement('fieldset');
		$modeFieldset->setAttribute("class", "settings picker");
		$modeFieldset->appendChild(new XMLElement('legend', __("Mode")));
		$modeSelectorLabel = Widget::Label("Mode");
		$modeSelectorOptions = array(
									/* if $savedSettings[mode][mode] is null, the top option will be picked */
									array("disabled", ($savedSettings["mode"]["mode"] == "disabled"), "Disabled"),
									array("client", ($savedSettings["mode"]["mode"] == "client"), "Client"),
									array("server", ($savedSettings["mode"]["mode"] == "server"), "Server")
								);
		$modeSelectorLabel->appendChild(Widget::Select("settings[mode][mode]", $modeSelectorOptions));
		$modeFieldset->appendChild($modeSelectorLabel);
		$this->Form->appendChild($modeFieldset);
	
		// These below are the 'pickable' blocks

		// Client Settings Block
		$clientFieldset = new XMLElement('fieldset');
		$clientFieldset->setAttribute("class", "settings pickable");
		$clientFieldset->setAttribute("id", "client");
		$liveServerUrlLabel = Widget::Label("Live Server URL");
		$liveServerUrlLabel->appendChild(Widget::Input("settings[client][server-url]", $savedSettings["client"]["server-url"]));
		$clientFieldset->appendChild($liveServerUrlLabel);
		$this->Form->appendChild($clientFieldset);

		// Server Settings Block
		$serverFieldset = new XMLElement('fieldset');
		$serverFieldset->setAttribute("class", "settings pickable");
		$serverFieldset->setAttribute("id", "server");
		
		$this->Form->appendChild(new XMLElement('script', 'jQuery(document).ready(function(){jQuery("#users-duplicator").symphonyDuplicator({orderable: true, collapsible: true});});'));		
		
		$serverUserFrame = new XMLElement('div', null, array('class' => 'frame'));
		$ol = new XMLElement('ol');
		$ol->setAttribute('id', 'users-duplicator');
		$ol->setAttribute('data-add', __('Add User'));
		$ol->setAttribute('data-remove', __('Remove User'));

		if(is_array($savedSettings["server"]["users"])) {
			foreach($savedSettings["server"]["users"] as $u) {
				$ol->appendChild($this->__getUserInputBlock($u));
			}
		}
		/*
		$templateWrapper = new XMLElement('li', NULL, array('class' => 'template field-user'));
		$templateWrapper->setAttribute('data-type', 'user');
		$templateHeader = new XMLElement('header', "New User", array("class" => "main"));
		$templateWrapper->appendChild($templateHeader);
		$serverUserFirstnameLabel = Widget::Label("User First Name");
		$serverUserFirstnameLabel->appendChild(Widget::Input("settings[server][users][][firstname]", ""));
		$templateWrapper->appendChild($serverUserFirstnameLabel);		
		$serverUserLastnameLabel = Widget::Label("User Last Name");
		$serverUserLastnameLabel->appendChild(Widget::Input("settings[server][users][][lastname]", ""));
		$templateWrapper->appendChild($serverUserLastnameLabel);		
		$serverUserEmailLabel = Widget::Label("User Email Name");
		$serverUserEmailLabel->appendChild(Widget::Input("settings[server][users][][email]", ""));
		$templateWrapper->appendChild($serverUserEmailLabel);
		$serverUserCreatedByLabel = Widget::Label("Created By");
		$serverUserCreatedByLabel->appendChild(Widget::Input("settings[server][users][][created-by]", ""));
		$templateWrapper->appendChild($serverUserCreatedByLabel);		
		$serverUserAuthKeyLabel = Widget::Label("Authentication Key");
		$serverUserAuthKeyLabel->appendChild(Widget::Input("settings[server][users][][auth-key]", "", "text", array("disabled" => "disabled")));
		$templateWrapper->appendChild($serverUserAuthKeyLabel);		
		*/
		
		// append the template
		$ol->appendChild($this->__getUserInputBlock(array(), true));
		
		$serverUserFrame->appendChild($ol);
		$serverFieldset->appendChild($serverUserFrame);
		
		$this->Form->appendChild($serverFieldset);
	
		// Default/Disabled Settings Block
		$disabledFieldset = new XMLElement('fieldset');
		$disabledFieldset->setAttribute("class", "settings pickable");
		$disabledFieldset->setAttribute("id", "disabled");
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
		$serverUserFirstnameLabel = Widget::Label("First Name");
		$serverUserFirstnameLabel->appendChild(Widget::Input("settings[server][users][firstname][]", $data['firstname']));
		$wrapper->appendChild($serverUserFirstnameLabel);		
		$serverUserLastnameLabel = Widget::Label("Last Name");
		$serverUserLastnameLabel->appendChild(Widget::Input("settings[server][users][lastname][]", $data['lastname']));
		$wrapper->appendChild($serverUserLastnameLabel);		
		$serverUserEmailLabel = Widget::Label("Email");
		$serverUserEmailLabel->appendChild(Widget::Input("settings[server][users][email][]", $data['email']));
		$wrapper->appendChild($serverUserEmailLabel);
		$serverUserCreatedByLabel = Widget::Label("Created By");
		$serverUserCreatedByLabel->appendChild(Widget::Input("settings[server][users][created-by][]", $data['created-by']));
		$wrapper->appendChild($serverUserCreatedByLabel);		
		$serverUserAuthKeyLabel = Widget::Label("Authentication Key");
		$serverUserAuthKeyLabel->appendChild(Widget::Input("settings[server][users][auth-key][]", ($template ? "save-to-generate" : $data['auth-key']), "text"));
		$wrapper->appendChild($serverUserAuthKeyLabel);	

		return $wrapper;
	
	}
}
