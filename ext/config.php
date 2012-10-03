<?php

//Connessione DB
$con = mysql_connect("localhost","adm_appmaster","h8nggkj-hf39y");
if (!$con)
{
	die('Could not connect: ' . mysql_error());
}

mysql_select_db("appmaster", $con);

//DEFINISCO DIRECTORY

define("DIR_BASE", realpath(dirname(__FILE__)) . "/");

if (!defined("DIR_ROOT"))
    define("DIR_ROOT", DIR_BASE);

define("DIR_FILE", DIR_BASE . "_files/");

define("DIR_FILE_M", DIR_BASE . "image.php/_files/");
define("DIR_IMAGE", DIR_FILE . "immagini/");

define("DIR_IMAGE_M", DIR_FILE_M . "immagini/");


define("DIR_ONLINE", "http://test.vision121.it/appPhoneGap/");
?>