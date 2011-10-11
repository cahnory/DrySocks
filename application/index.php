<?php
	
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
	
	require_once	'Config.php';
	$config	= new Config;
	$app	= new DS\Application($config);
	
	$app->dispatch();

?>