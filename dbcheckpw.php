#!/usr/bin/php
<?php

require("libcommon.php");
require("config.php");

$claves = getArrayFromSql("SELECT * FROM claves");
$nro_claves = count($claves);

$codigo = 0;
$procesados = 0;
$fallos = 0;
foreach($claves as $registro){
	$r_cifrada = $registro["clave"];
	$r_plana = doDecrypt($r_cifrada, DAYCOPASS_DB_SALT);
	$r_sumac = $registro["sumac"];
	$r_sumap = $registro["sumap"];
	$sumac = sha1($r_cifrada);
	$sumap = sha1($r_plana);
	
	$procesados++;
	if ($r_sumac != $sumac){
		echo "[Error] SHA1 [CIFRADA] de clave con ID=" . $registro["id"] . "\n";
		$fallos++;
	}else{
		if ($r_sumap != $sumap){
			echo "[Error] SHA1 [PLANA] de clave con ID=" . $registro["id"] . "\n";
			$fallos++;
		}
	}
}
$codigo += $fallos;

doCloseDB();

if ($codigo>0 || $procesados==0) exit(1);
