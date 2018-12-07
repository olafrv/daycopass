<?php

// Is ajax request?
$DAYCOPASS_IS_AJAX  = 
	isset($_GET["ajax"]) || isset($_POST["ajax"]);

// Have get or post token?
$DAYCOPASS_GP_TOKEN = 
	isset($_POST["token"]) ? $_POST["token"] : (isset($_GET["token"]) ? $_GET["token"] : NULL);

// Is a token authenticated request?
$DAYCOPASS_IS_TOKEN = !is_null($DAYCOPASS_GP_TOKEN);

if ($DAYCOPASS_IS_TOKEN && isset($DAYCOPASS_IS_TOKEN_ALLOWED)) { 
	// TOKEN AUTHENTICATION (TYPE & VALUE)
	if (!doCheckToken($DAYCOPASS_GP_TOKEN, $_SERVER["REMOTE_ADDR"])) {
		$msg = "ERROR: Token invalido";
		doLog($msg);
		if ($DAYCOPASS_IS_AJAX) {  
			doEcho($msg);
		}else{ 
			doFlash($msg . ", redirigiendo...");
			doScript("document.location='logon.php';");
		}
		doCloseDB();
		exit(0); // STOP!
	}else{
		if (getTokenType($DAYCOPASS_GP_TOKEN) == "unlock") {
			$msg = "Usuario desbloqueado, redirigiendo...";
			doFlash($msg);
			doScript("document.location='logon.php';");
			doCloseDB();
			exit(0); // STOP!
		}else{
			doLog("Acceso permitido via token '$DAYCOPASS_GP_TOKEN'");
		}
	}	
}else{ 
	// USER AUTENTICATION (USER & PASSWORD)
	if (!isLoggedIn()){
		$msg = "ERROR: Sesion expirada";
		doLog($msg);
		if ($DAYCOPASS_IS_AJAX) { 
			doEcho($msg);
		}else{ 
			doFlash($msg . ", redirigendo...");
			doScript("document.location='logon.php';");
		}
		doCloseDB();
		exit(0); // STOP! 
	}
}
