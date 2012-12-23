<?php

/*
	DIM_Logger
	
	Deals with all the logging requirements of the extension. All functions should be static.
*/
class DIM_Logger {

	/*
		->addLogItem($string, $class)
		Adds an item with the specified class to the log.
		@params
			$string - the string to log
			$class - the class of the log item (optional, defaults to 'generic')
			$data - any additional data relating to the log item (optional, defaults to an empty array)
		@returns
			nothing
	*/
	public function addLogItem($string, $class = 'generic', $data = array()) {
		$newLogItem = array("timestamp" => date("d-m-Y H:i:s"), "text" => $string, "class" => $class, "data" => $data);
		$this->saveRawLogItem($newLogItem);
	}	
	
	/*
		->logException($e)
		A shorthand way of logging an exception in the log.
		@params
			$e - Exception, the caught exception to log.
		@returns
			nothing
	*/
	public function logException($e) {
		$this->addLogItem("A system exception '{$e->getMessage()}' was caught.", "error", array("file" => $e->getFile(), "trace" => $e->getTrace()));
	}
	
	/*
		->readLog($classFilter = 'all')
		Reads the log, with an optional filter.
		@params
			$classFilter - the class of the items that should be returned (optional, defaults to 'all')
		@returns
			array - the log items.
	*/
	public function readLog($classFilter = 'all') {
		$fullLog = $this->readRawLog();
		$output = array();
		foreach($fullLog as $f) {
			if($f["class"] == $classFilter || $classFilter == 'all') {
				$output[] = $f;
			}
		}
		return $output;
	}
	
	/*
		->getLogPath()
		Get the path of the log file
		@returns
			the path of the log file
	*/
	public function getLogPath() {
		return (dirname(__FILE__) . "/../log.php");
	}
	
	/*
		->readRawLog()
		Do the initial read of the log - allows abstraction of function on top that doesn't care about
		the storage medium.	
		@returns
			array - the log items.
	*/
	private function readRawLog() {
		include($this->getLogPath());
		return $theLog;
	}
	
	/*
		->saveRawLogItem($logItem)
		The basic function to add an item to the log - allows abstraction of function on top of this that
		doesn't care about the storage medium.
	*/
	private function saveRawLogItem($logItem) {
		$theLog = $this->readRawLog();
		$theLog[] = $logItem;
		file_put_contents($this->getLogPath(), "<?php \$theLog = " . var_export($logItem, true) . "; ?>");
	}
	
}

?>