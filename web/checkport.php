<?php

session_start();

header("Pragma: no-cache");
header("Expires: 0");

require("../libcommon.php");
require("../config.php");
require("../sslenforce.php");
require("../aaa.php");

function isPortOpen($ip, $port, $type, $timeout, &$errno, &$errstr){
	if ($type=="tcp"){
		$url=$ip;
	}else{
		$url="udp://$ip";
	}
  $fp = @fsockopen($url, $port, $errno, $errstr, $timeout);
	if ($fp){
		fclose($fp);
		return TRUE;
	}else{
		return FALSE;
	}
}

$ip = $_GET["ip"];
$port = $_GET["port"];
$errno = NULL;
$errstr = NULL;

if (isPortOpen($ip, $port, "tcp", 1, $errno, $errstr)){
	echo "OK";
}else{
	echo "ERROR:#$errno $errstr";
}
