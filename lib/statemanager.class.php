<?php
/*
	DIM_StateManager
	Responsible for maintaining the system state (checked out/in etc). This is mainly
	here in case we want to expand it in future to allow section-level checking out!
*/
class DIM_StateManager {

	/*
		where we will hold the state for now
	*/
	var $STATE_FILE;

	/*
		->__construct()
	*/
	public function __construct($mode) {
		$this->STATE_FILE = dirname(__FILE__) . "/../../../manifest/dim_{$mode}_state.php";
	}
	
	/*
		->isCheckedIn()
		@returns
			true if the system is checked in, false if not
	*/
	public function isCheckedIn() {
		return (!$this->readState());
	}
	
	/*
		->isCheckedOut()
		@returns
			true if the system is checked out, false if not
	*/
	public function isCheckedOut() {
		return $this->readState();
	}
	
	/*
		->checkOut()
		Checks the system out
	*/
	public function checkOut() {
		$this->writeState(1);
	}
	
	/*
		->checkIn()
		Checks the system in
	*/
	public function checkIn() {
		$this->writeState(0);
	}
	
	/*
		->readState()
		Reads the raw state of the system
		@returns
			1 = checked out, 0 = checked in
	*/
	private function readState() {
		if(file_exists($this->STATE_FILE)) {
			$contents = file_get_contents($this->STATE_FILE);
			return ($contents == "1" ? true : false);
		}
		else {
			return false;
		}
	}

	/*
		->writeState($state)
		@params
			1 = checked out, 0 = checked in
	*/
	private function writeState($state) {
		file_put_contents($this->STATE_FILE, $state);
	}
	
}

?>