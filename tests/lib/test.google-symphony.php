<?php
	
	/**
	* Google for Symphony CMS
	*
	* Goes to google.com and searches for "Symphony CMS".
	*/
	class SymphonyTestGoogleSymphony extends WebTestCase {
		public function testExample() {
			$this->get('http://google.com');
			$this->setField('q', 'Symphony CMS');
			$this->click('I\'m Feeling Lucky');
			$this->assertText('Symphony');
			$this->assertNoText('Symfony');
			$this->showHeaders();
		}
	}
	
?>