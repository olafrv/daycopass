#!/usr/bin/php
<?php

require("libcommon.php");
require("config.php");

function doExeCmd($cmd, $archivos)
{
	global $codigo_todos;
	$codigo = system($cmd);
	if ($codigo != 0){
		foreach ($archivos as $archivo){
			echo "ERROR: Al crear/cifrar $archivo, sera eliminado por seguridad\n";
			if (is_file($archivo)) unlink($archivo);
		}
	}
	$codigo_todos += $codigo;
}

$mysql_cmd_prefix = "mysqldump -h "
	. $DAYCOPASS_DB_AUTH["server"]
	. " -u " . $DAYCOPASS_DB_AUTH["user"]
	. " -p\"" . $DAYCOPASS_DB_AUTH["password"] . "\""
	. " --add-drop-database --single-transaction --triggers --routines --events"
	. " " . ((doGetMySQLVersion()!="5.1" && doGetMySQLVersion()!="5.5") ? "--set-gtid-purged=OFF" : "");

$ssl_cmd_prefix = "openssl aes-256-cbc -a -salt -pass pass:" . md5(DAYCOPASS_DB_SALT);

if ($argc<=1 || !isset($argv[1]) || !is_dir($argv[1])){
	echo "ERROR: Debe especificar un directorio valido como parametro\n";
	exit(1);
}

$directorio = $argv[1];
$fecha=date("Ymd_His");
$codigo_todos = 0;

# Respaldo de la base de datos de Daycopass
$archivo = "$directorio/mysql-" . $DAYCOPASS_DB_AUTH["database"] . "_" . $fecha . ".sql";
$cmd = "$mysql_cmd_prefix --databases " . $DAYCOPASS_DB_AUTH["database"] . " --result-file $archivo";
doExeCmd($cmd, array($archivo));
$cmd = "$ssl_cmd_prefix -in $archivo -out " . $archivo . ".enc";
doExeCmd($cmd, array($archivo, $archivo . ".sql.enc"));
unlink($archivo);

# Respaldo del esquema de base de datos de Daycopass
$archivo = "$directorio/mysql-" . $DAYCOPASS_DB_AUTH["database"] . "_nodata_" . $fecha . ".sql";
$cmd = "$mysql_cmd_prefix --no-data --databases " . $DAYCOPASS_DB_AUTH["database"] . " --result-file $archivo";
doExeCmd($cmd, array($archivo));
$cmd = "$ssl_cmd_prefix -in $archivo -out " . $archivo . ".enc";
doExeCmd($cmd, array($archivo, $archivo . ".sql.enc"));
unlink($archivo);

# Respaldo de las tablas de la base de datos de Daycopass
foreach(getArrayFromSql("SHOW TABLES") as $registro){
	$tabla = array_shift($registro);
	$archivo = "$directorio/mysql-" . $DAYCOPASS_DB_AUTH["database"] . "_" . $tabla . "_" . $fecha . ".sql";
	$cmd = "$mysql_cmd_prefix --databases " . $DAYCOPASS_DB_AUTH["database"] . " --tables $tabla --result-file $archivo";
	doExeCmd($cmd, array($archivo));
	$cmd = "$ssl_cmd_prefix -in $archivo -out " . $archivo . ".enc";
	doExeCmd($cmd, array($archivo, $archivo . ".sql.enc"));
	unlink($archivo);
}

if ($codigo_todos!=0) echo "ERROR: Hubo errores al vaciar las bases de datos";

exit($codigo_todos);
