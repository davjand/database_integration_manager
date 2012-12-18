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

require_once(EXTENSIONS . '/symphony_checkout/data-sources/data.available_gateways.php');


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
			
			if(extension_database_integration_manager::testSettings($_POST["settings"])) {
				file_put_contents(extension_database_integration_manager::getExtensionConfigPath(), "<?php \$savedSettings = " . var_export($_POST["settings"], true) . "; ?>");
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

}

