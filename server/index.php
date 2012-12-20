<?php

	/* 
		This is the server base file that contains the base processing
		and output.
		NB - as little code as possible should be here! This will be the
		root file that is called
	*/
	
	require_once(dirname(__FILE__) . "/../lib/server.class.php");
	
	$theServer = new DIM_Server();
	echo($theServer->handleRequest($_POST));
	
?>