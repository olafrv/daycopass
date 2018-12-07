<?php

session_start();

header("Pragma: no-cache");
header("Expires: 0");

require("../libcommon.php");
require("../config.php");
require("../sslenforce.php");

$DAYCOPASS_IS_TOKEN_ALLOWED = TRUE;

require("../aaa.php");

$id = isset($_GET["id"]) ? $_GET["id"] : NULL;
$historial = isset($_GET["historial"]) && !empty($_GET["historial"]) ? $_GET["historial"] : NULL;
$servicio = isset($_GET["servicio"]) ? substr($_GET["servicio"],0,64) : NULL;
$formato = isset($_GET["formato"]) && !empty($_GET["formato"]) ? $_GET["formato"] : NULL;

$resultado = array();

if (!empty($id)){
	$nivel = getLevel();
	$tabla = "claves";
	if (!empty($historial)) $tabla = "claves_historial";  
	$sql = "SELECT * FROM $tabla WHERE nivel <= $nivel AND id = \"" . doEsc((int) $id) . "\"";
	doLog("Visualizacion de credencial = $id");
	$resultado = getArrayFromSql($sql);
	$resultado = array_shift($resultado);
	foreach($resultado as $indice => $valor){
		if ($indice=="clave"){
			$resultado[$indice] = doCleanInvalidChars(doDecrypt($resultado[$indice], DAYCOPASS_DB_SALT));
		}else{	
			$resultado[$indice] = doCleanInvalidChars($resultado[$indice]);
		}
	}
	$resultado["crawler"] = implode(",", crawlerExtractSocketURL(
		$resultado["servicio"] . " " .  $resultado["url"] . " " . $resultado["nota"]
	));
}else{
	if ($DAYCOPASS_IS_TOKEN){
		$sql = "SELECT id, servicio, usuario, categoria, nivel, url, nota FROM claves";
		doLog("Visualizacion (c/token) de todas las claves");
		$resultado = getArrayFromSql($sql);
	}
}

if (!empty($resultado)){
	if (!empty($formato))	{ 
		switch($formato){
			case "properties":
				foreach($resultado as $indice => $valor) echo $indice . "=" . $valor . "\n";
				break;
			case "base64":
				$base64 = base64_encode(serialize($resultado));
				echo sha1($base64) . ":" . $base64;		
				break;
		}
	}else{  
		echo json_encode($resultado);
	}
}else{
	doLogEcho("Solicitud invalida o privilegios insuficientes (Credencial=$id)", LOG_ERR);
}

doCloseDB();

