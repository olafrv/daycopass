<?php

session_start();

header("Pragma: no-cache");
header("Expires: 0");

require("../libcommon.php");
require("../config.php");
require("../sslenforce.php");
require("../aaa.php");

$DATA = array();
$FALTANTES = array();
$CAMPOS = array(
	"id" => -1,
	"categoria" => pow(2,8)-1,
	"tipo_servicio" => -1, 
	"servicio" => pow(2,8)-1, 
	"usuario" => pow(2,8)-1, 
	"clave" => pow(2,8)-1, 
	"url" => pow(2,16)-1,
	"nota" => pow(2,16)-1, 
	"nivel" => -1, 
	"valido" => -1,
	"fuente" => -1,
	"fecha" => -1
);
$CAMPOS_OPCIONALES = array(
	"id", "url", "nota", "valido", "fuente", "fecha"
);
$CAMPOS_ENTEROS = array(
	"id", "nivel", "valido"
);

foreach($CAMPOS as $variable => $longitudMax){
	$DATA[$variable] = isset($_GET[$variable]) ? $_GET[$variable] : "";
	if (empty($DATA[$variable]) && !in_array($variable, $CAMPOS_OPCIONALES)){
		// Campo requerido
		$FALTANTES[] = $variable;
	}else{
		if ($longitudMax != -1 && strlen($DATA[$variable])>$longitudMax){
			doLogEcho("El valor '$variable' ha sobrepasado su longitud maxima ($longitudMax)", LOG_ERR);			
			exit(1);
		} 
		if (in_array($variable, $CAMPOS_ENTEROS)){
			// Convertir a entero
			$DATA[$variable] = (int) $DATA[$variable];
		}
	}
}

if (count($FALTANTES)==0){

	if ($DATA["nivel"]>getLevel()){
		doLogEcho("No tiene permisos para agregar credenciales de nivel " . $DATA[$variable_minus], LOG_ERR);
		exit(1);
	}

	$DATA["sumap"] = sha1($DATA["clave"]);
	$DATA["clave"] = doEncrypt($DATA["clave"], DAYCOPASS_DB_SALT);
	$DATA["sumac"] = sha1($DATA["clave"]); // De la clave cifrada (No en texto plano)
	
	// Transaccion SQL
	doBeginSql();	
	
	// TRX1: Guardado en el historial
	$trx1 = FALSE;
	if (!empty($DATA["id"])){
		$sql1 = "SELECT * FROM claves WHERE id=" . ((int) $DATA["id"]);
		$DATAH = getArrayFromSql($sql1);
		$DATAH = array_shift($DATAH);
		$DATAH["id_clave"] = $DATAH["id"];
		if (!empty($DATAH)){
			$sql1  = 'INSERT INTO claves_historial (';
			$sql1 .= implode(',', array_keys($DATAH));
			$sql1 .= ') VALUES (';
			$first = TRUE;
			foreach($DATAH as $variable => $valor){
				$sql1 .= $first ? "" : ",";
				if ($first) $first = FALSE;
				if (strtolower($variable)=="id"){
					$sql1 .= 'NULL';
				}else{
					$sql1 .= '"' . doEsc($valor) . '"';
				}
			}
 			$sql1 .= ");";
			//echo "$sql1\n";
			$trx1 = execSql($sql1);
		}else{
			$trx1 = FALSE;
		}
	}else{
		$trx1 = TRUE;
	}

	// TRX2: Insercion o actualizacion de clave
	unset($DATA["fuente"]);
	unset($DATA["fecha"]);
	$sql2 = 'REPLACE INTO claves (';
	foreach($DATA as $variable => $valor) $sql2 .= $variable . ',';
	$sql2 .= 'fuente, fecha';		
	$sql2 .= ') VALUES(';
	foreach($DATA as $variable => $valor){
		if ($variable=="id"){
			if (empty($DATA["id"])){
				$sql2 .= 'NULL,';
			}else{
				$sql2 .= ((int) $DATA["id"]) . ",";
			}
		}else{
			$sql2 .= '"' . doEsc($valor) . '",';
		}
	}
	$sql2 .= '"DaycoPass", "'.date("Y-m-d H:i:s").'");';
	//echo "$sql\n";
	$trx2 = execSql($sql2);

	if ($trx1 && $trx2){

		if (doCommitSql()) {

			echo "OK";
			if (!empty($DATA["id"])){
				doLog("Se actualizo la credencial ID=" . $DATA["id"]);
			}else{
				doLog("Se registro una nueva credencial");
			}

		}else{
			doRollbackSql();
			doLogEcho("No se pudieron guardar los datos (commit)", LOG_ERR);
		}

	}else{
		doRollbackSql();
		doLogEcho("No se pudieron guardar los datos (exec)", LOG_ERR);
	}

}else{

	$mensaje =  "Faltan los siguientes datos: ";
	foreach($FALTANTES as $indice => $faltante){
		if ($indice>0) $mensaje .= ", ";
		$mensaje .= "$faltante";
	}
	doLogEcho($mensaje, LOG_ERR);

}

doCloseDB();

