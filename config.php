<?php

// .ini file parsing...
$DAYCOPASS_INI_FILE = 
	is_file("../../daycopass.ini") ? "../../daycopass.ini" : "../daycopass.ini";
$DAYCOPASS_INI = parse_ini_file($DAYCOPASS_INI_FILE, true) 
	or die("Config file not found " . $DAYCOPASS_INI_FILE);

// .ini - database section
$DAYCOPASS_DB_AUTH = $DAYCOPASS_INI["database"]; // Backward Compatibility
$DAYCOPASS_DB = new mysqli(
	$DAYCOPASS_INI["database"]["server"], $DAYCOPASS_INI["database"]["user"],
	$DAYCOPASS_INI["database"]["password"], $DAYCOPASS_INI["database"]["database"]
) or doDie(mysqli_error());

// .ini - security section
define("DAYCOPASS_DB_SALT", $DAYCOPASS_INI["security"]["salt"]); // 64 hex digits key

// .ini - general section
$DAYCOPASS_INI_PARAMS = doGetIniParams();
foreach($DAYCOPASS_INI["override"] as $override_name => $value){
	$name = "DAYCOPASS_" . strtoupper($override_name);
	if (!isset($DAYCOPASS_INI_PARAMS[$name])) doDie("Parametro desconocido '$override_name'");
	$type = $DAYCOPASS_INI_PARAMS[$name];
	switch($type)
	{
		case "i":	
			$value = (int) $value;
			break;
		case "b":
			$value = empty($value) ? FALSE : TRUE;
			break;
	}
	// x=y, by default for "s:string" and "a:array"
	$$name = $value;
}
foreach($DAYCOPASS_INI_PARAMS as $name => $type) 
	if (!isset($$name)) 
		doDie("No esta definida las variable $name");

if (!mysqli_query($DAYCOPASS_DB, "SET @@SESSION.sql_mode = '$DAYCOPASS_SQLMODES'")){
    doDie("Error definiendo SQL MODES para la session MySQL. " . mysql_error());
}

