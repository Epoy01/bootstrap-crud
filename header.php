<?php

function __autoload($class){
	include_once($class.".php");
}

$model= new model("model.php");

?>

<!DOCTYPE html>
<html>
<head>
	<title>CRUD APPLICATION</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="bootstrap/app.css">
	<!-- Latest compiled JavaScript -->
	<script src="bootstrap/app.js"></script>
</head>
<br/>

