<?php
$dbHost= "localhost";
$dbName ="portfolio_builder";
$dbpassword = "";
$dbusername = "root";

//$con = mysqli_connect($dbHost, $dbName, $dbpassword, $dbusername);

$con = mysqli_connect($dbHost, $dbusername, $dbpassword, $dbName);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

?>