<?php

include('../../AuthClass.php');
$user = authenticateUser();
include('../AdminClass.php');
include('../top.php');

//print_r($user);
$uri = $_SERVER['REQUEST_URI'];
$pieces = explode("/", $uri);
//print_r($pieces);
//awdwad.php
$temp = explode(".", $pieces[3]);
include($temp[0] . "_c.php");
include('../../bottom.php');

?>
