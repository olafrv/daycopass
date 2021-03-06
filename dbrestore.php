#!/usr/bin/php
<?php

require("libcommon.php");
require("config.php");

if ($DAYCOPASS_READONLY){
	echo "ERROR: Sistema en modo de solo lectura\n";
	exit(2);
}

$cmd_prefix = "mysql -h "
	. $DAYCOPASS_DB_AUTH["server"]
	. " -u " . $DAYCOPASS_DB_AUTH["user"]
	. " -p\"" . $DAYCOPASS_DB_AUTH["password"] . "\""
  . " -D\"" . $DAYCOPASS_DB_AUTH["database"] . "\"";

if ($argc<=1 || !isset($argv[1]) || !is_file($argv[1])){
	echo "ERROR: Debe especificar un archivo de respaldo (.sql)\n";
	exit(1);
}

$cmd = "$cmd_prefix < " . $argv[1];

//echo $cmd . "\n";

$codigo = system($cmd); 

exit($codigo);
