<?php

function doLdapLogin($usuario, $clave, &$mensaje){
	global $DAYCOPASS_LDAP_SERVER;
	global $DAYCOPASS_LDAP_BINDDN;
	global $DAYCOPASS_LDAP_BINDPW;
	global $DAYCOPASS_LDAP_BASEDB;
	global $DAYCOPASS_LDAP_FILTER;
	$ldapconn = ldap_connect("ldap://$DAYCOPASS_LDAP_SERVER");
   if($ldapconn){
		ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		$ldapbind = @ldap_bind($ldapconn, $DAYCOPASS_LDAP_BINDDN, $DAYCOPASS_LDAP_BINDPW);
		if ($ldapbind){
			$sr = ldap_search($ldapconn, $DAYCOPASS_LDAP_BASEDB, 
							str_replace("%U%",$usuario, $DAYCOPASS_LDAP_FILTER));
			if ($sr){
				$info = ldap_get_entries($ldapconn, $sr);
				if($info['count'] <= 0){
		   		$mensaje = "No se encontr&oacute; al usuario $usuario";
		   	}else{
		   		$dn = $info[0]["dn"];
					$ldapbind = @ldap_bind($ldapconn, $dn, $clave);
					if ($ldapbind){
						$mensaje = "Usuario '$usuario' autenticado via LDAP";	
						ldap_close($ldapconn);
						return TRUE;
					}else{
						$mensaje = "Clave incorrecta";
					}
				}
			}else{
				$mensaje = "Error de busqueda (SEARCH) en el servidor LDAP";
			}
		}else{
			$mensaje = "Error de autenticacion (BIND) al servidor LDAP";
		}
		ldap_close($ldapconn);
	}else{
		$mensaje = "Error de conexion (CONNECT) al servidor LDAP";
	}
	return FALSE;
}

function doLdapGetUserInfo($usuario, &$atributos, &$mensaje){
	global $DAYCOPASS_LDAP_SERVER;
	global $DAYCOPASS_LDAP_BINDDN;
	global $DAYCOPASS_LDAP_BINDPW;
	global $DAYCOPASS_LDAP_BASEDB;
	global $DAYCOPASS_LDAP_FILTER;
	$atributos = NULL;
	$ldapconn = ldap_connect("ldap://$DAYCOPASS_LDAP_SERVER");
  if($ldapconn){
		ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		$ldapbind = @ldap_bind($ldapconn, $DAYCOPASS_LDAP_BINDDN, $DAYCOPASS_LDAP_BINDPW);
		if ($ldapbind){
			$sr = ldap_search($ldapconn, $DAYCOPASS_LDAP_BASEDB, 
							str_replace("%U%",$usuario, $DAYCOPASS_LDAP_FILTER));
			if ($sr){
				$info = ldap_get_entries($ldapconn, $sr);
				if($info['count'] == 1){
		   		$atributos = $info[0];
				}else{
					$mensaje = "Resultado multiple de la busqueda (SEARCH) LDAP (FILTRO: $DAYCOPASS_LDAP_FILTER)";
				}
			}else{
				$mensaje = "Error de busqueda (SEARCH) en el servidor LDAP";
			}
		}else{
			$mensaje = "Error de autenticacion (BIND) al servidor LDAP";
		} 
		ldap_close($ldapconn);
	}else{
		$mensaje = "Error de conexion (CONNECT) al servidor LDAP";
	}
	return !is_null($atributos);
}

function doBeginSQL($dblink = NULL){
	global $DAYCOPASS_DB;
	$dblink = is_null($dblink) ? $DAYCOPASS_DB : $dblink;
	return $dblink->autocommit(FALSE) or doDie($dblink->error);
}

function doCommitSQL($dblink = NULL){
	global $DAYCOPASS_DB;
	$dblink = is_null($dblink) ? $DAYCOPASS_DB : $dblink;
	return $dblink->commit() or doDie($dblink->error);
}

function doRollbackSQL($dblink = NULL){
	global $DAYCOPASS_DB;
	$dblink = is_null($dblink) ? $DAYCOPASS_DB : $dblink;
	return $dblink->rollback() or doDie($dblink->error);
}

function doDie($message){
	doLog("DIED: " . substr($message,0,255), LOG_ALERT);
	if (defined('STDERR')){
		fwrite(STDERR, "$message\n");
	}else{
		echo "$message<br>\n";
	}
	exit(1);
}

// Encrypt Function
function doEncrypt($encrypt, $key){
    $encrypt = serialize($encrypt);
    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
    $key = pack('H*', $key);
    $mac = hash_hmac('sha256', $encrypt, substr(bin2hex($key), -32));
    $passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt.$mac, MCRYPT_MODE_CBC, $iv);
    $encoded = base64_encode($passcrypt).'|'.base64_encode($iv);
    return $encoded;
}

// Decrypt Function
function doDecrypt($decrypt, $key){
    $decrypt = explode('|', $decrypt.'|');
    $decoded = base64_decode($decrypt[0]);
    $iv = base64_decode($decrypt[1]);
    if(strlen($iv)!==mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)){ return false; }
    $key = pack('H*', $key);
    $decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_CBC, $iv));
    $mac = substr($decrypted, -64);
    $decrypted = substr($decrypted, 0, -64);
    $calcmac = hash_hmac('sha256', $decrypted, substr(bin2hex($key), -32));
    if($calcmac!==$mac){ return false; }
    $decrypted = unserialize($decrypted);
    return $decrypted;
}

function doCloseDB(){
	global $DAYCOPASS_DB;
	$DAYCOPASS_DB->close() or doDie("Error al cerrar la conexion a la base de datos");
}

function doEsc($str, $dblink = NULL){
	global $DAYCOPASS_DB;
	$dblink = is_null($dblink) ? $DAYCOPASS_DB : $dblink;
	return $DAYCOPASS_DB->escape_string($str);
}

function doLog($message, $level = LOG_INFO){
	return doLogWithoutLogin($message, getUsername(), $level);
}

function doLogWithoutLogin($message, $username, $level = LOG_INFO){
	global $DAYCOPASS_ONLY_SYSLOG;
	global $DAYCOPASS_READONLY;
	$ahoraf = doGetTimeString();
	openlog("daycopass", LOG_PID | LOG_PERROR | LOG_CONS, LOG_SYSLOG);
	syslog($level, "$ahoraf [$username:" . $_SERVER["REMOTE_ADDR"] . "] " . $message);
	closelog();
	if ($DAYCOPASS_ONLY_SYSLOG || $DAYCOPASS_READONLY) return TRUE;
	$sql = "INSERT INTO bitacora VALUES(NULL, \"" 
		. $_SERVER["REMOTE_ADDR"] . "\", \"$username\", \""
			. doEsc($message)
				. "\", " . $level 
					. ",\"$ahoraf\")";
	return execSql($sql);
}

function doEcho($message){
	if (defined('STDERR')){
		fwrite(STDOUT, "$message\n");
	}else{
		echo "$message<br>\n";
	}
}

function doLogEcho($message, $level = LOG_INFO){
	if ($level == LOG_ERR){
		doEcho("ERROR: " . $message);
	}else{
		doEcho($message);
	}
	return doLog($message, $level);
}

function doScript($code, $ret = FALSE){
	$script = '<script type="text/javascript">' . "\n$code\n" . '</script>';
	if ($ret){
		return $script;
	}else{
		echo "\n$script\n";
	}
}

function execSql($sql, $dblink = NULL){
	global $DAYCOPASS_DB;
	global $DAYCOPASS_READONLY;
	if ($DAYCOPASS_READONLY) return FALSE;
	$dblink = is_null($dblink) ? $DAYCOPASS_DB : $dblink;
	return $dblink->query($sql) or doDie($dblink->error);
}

function getArrayFromSql($sql, $dblink = NULL){
	global $DAYCOPASS_DB;
	$dblink = is_null($dblink) ? $DAYCOPASS_DB : $dblink;
	$resultado = $dblink->query($sql) or doDie($dblink->error);
	$info = array();
	while($fila = $resultado->fetch_assoc()) $info[] = $fila;
	return $info;
}

function doFlash($msg){ 
	echo $msg . "<br>";
}

function getLevels($minLevel = NULL, $maxLevel = NULL){ // Por defecto, niveles excepto deshabilitado(s)
	global $DAYCOPASS_DB;
	global $DAYCOPASS_DISABLE_LEVEL;
	if (is_null($minLevel)){
		$minLevel = $DAYCOPASS_DISABLE_LEVEL+1;
	}else{
		$minLevel = (int) $minLevel;
	}
	$maxWhere = !is_null($maxLevel) ? ("AND x.nivel <= ".((int)$maxLevel)) : "";
	$sql = "
select distinct x.nivel as nivel from
(
select nivel from claves
union
select nivel from usuarios
) as x
WHERE x.nivel>=$minLevel $maxWhere
order by x.nivel desc
";
	$resultado = getArrayFromSql($sql, $DAYCOPASS_DB);
	$niveles = array();
	foreach($resultado as $registro) $niveles[] = $registro["nivel"];
	if ($minLevel==$DAYCOPASS_DISABLE_LEVEL && !in_array($DAYCOPASS_DISABLE_LEVEL, $niveles)){
		$niveles[] = $DAYCOPASS_DISABLE_LEVEL;
	}
	return $niveles;
}

function getLevel($usuario = NULL){
	global $DAYCOPASS_DB;
	global $DAYCOPASS_ADMIN_LEVEL; 
	global $DAYCOPASS_DISABLE_LEVEL; 
	global $DAYCOPASS_IS_TOKEN;
	if (is_null($usuario)) $usuario = getUsername();
	if ($usuario == "admin" || $DAYCOPASS_IS_TOKEN) return $DAYCOPASS_ADMIN_LEVEL;
	$sql = "SELECT nivel FROM usuarios WHERE nombre = \"" . $DAYCOPASS_DB->escape_string($usuario). "\" LIMIT 1";
	$resultado = getArrayFromSql($sql, $DAYCOPASS_DB);
	if (count($resultado)>0){
		return ((int) $resultado[0]["nivel"]);
	}else{
		return $DAYCOPASS_DISABLE_LEVEL;
	}
}

function getUsername(){
	global $_SESSION;
	if (isset($_SESSION["usuario"])){
		return $_SESSION["usuario"];
	}else{
		return NULL;
	}
}

function getUsernames(){
	global $DAYCOPASS_DB;
	$sql = "SELECT nombre FROM usuarios";
	$resultado = getArrayFromSql($sql, $DAYCOPASS_DB);
	$usuarios = array();
	foreach($resultado as $registro) $usuarios[] = $registro["nombre"];
	return $usuarios;
}

function isMyLevel($nivel){
	return ((int) $nivel) == getLevel();
}

function isLoggedIn($usuario = NULL){
	global $_SESSION;
	return isset($_SESSION["usuario"]) 
		&& (is_null($usuario) ? TRUE : ($usuario == $_SESSION["usuario"]));
}

function doLogout(){
	global $_SESSION;
	if (isset($_SESSION["usuario"])){
		$_SESSION["usuario"] = NULL;
		unset($_SESSION["usuario"]);
	}
}

function getToken(){
	return strtolower(md5(uniqid(mt_rand(), true)));
}

function getTokenType($token){
	$valores = explode(",", $token); 
	return strtolower($valores[0]);
}

// Token: string in of the following forms:
// - "type,value", with type equal to:
//   - system: static, stored in .ini, used for external applications (crawler) 
//   - csrf: static, stored in session, avoids Cross Site Request Forgery (CSRF)
// - "type,user,value", with type equal to:
//   - user: backward compatibility. Deprecated in favor of ott.
//   - unlock: an ott token if valid is used to unlock and user.
//   - ott: dinamic, stored in table, created with 'doOneTimeToken()' function.
// In all cases "value" is a md5 hash (255 varchar lowercase hexadecimal string).
function doCheckToken($token, $remoteServer = NULL){
	global $_SESSION;
	global $DAYCOPASS_TOKENS;
	$valores = explode(",", $token); 
	if (count($valores)<2){
		doLogWithoutLogin("Token invalido '$token' de '$remoteServer'", LOG_ERR);
		return FALSE;
	}
	$tipo = strtolower($valores[0]);
	$valor = strtolower($valores[1]);
	switch($tipo){
		case "system":
			if (is_null($remoteServer)){
				doLogWithoutLogin("Token recibido de dir. IP invalida", LOG_ERR);
				return FALSE;
			}
			if (isset($DAYCOPASS_TOKENS[$remoteServer])){
				return in_array($valor, explode(",", $DAYCOPASS_TOKENS[$remoteServer]));
			}
			break;
		case "csrf";
			return isset($_SESSION["csrf"]) ? ($_SESSION["csrf"]==$valor) : FALSE;
			break;
		case "user":
		case "unlock":
		case "ott":
			if (count($valores)<3){
				doLogWithoutLogin("Token invalido '$token' usado desde '$remoteServer'", LOG_ERR);
				return FALSE;
			}
			$usuario = strtolower($valores[1]);
			$valor = strtolower($valores[2]);
			$valido = doCheckOneTimeToken($usuario, $valor);
			if ($valido){
				if ($tipo == "unlock") {
					doLog("Usuario '$usuario' desbloqueado con token '$valor'.");
					return doCleanLoginFails($valores[1]);
				}
				return TRUE;
			}
			break;
		default:
			doLogWithoutLogin("No se puede chequear el tipo de token desconocido '$token'");
			return FALSE;
	}
	return FALSE;	
}

function doOneTimeToken($usuario, $interval = "PT30M")
{
	$ahora = new DateTime();
	$ahora->add(new DateInterval($interval)); // ISO 8601 => PHP DateInterval
	$vencimiento = $ahora->format('c');
	$token = getToken();
	$trx = execSql(
		"INSERT INTO usuarios_ott VALUES (NULL, '$usuario', '$vencimiento', '$token')"
	);
	if ($trx){
		return $token;
	}else{
		return FALSE;
	}
}

function doCheckOneTimeToken($usuario, $valor)
{
	global $DAYCOPASS_DB;
	$ahora = new DateTime(); 
  $ahora = $ahora->format('c');
	$usuario = doEsc(strtolower($usuario));
	$valor = doEsc(strtolower($valor));
	$sql  = "SELECT * FROM usuarios_ott WHERE usuario='$usuario' AND token='$valor' AND '$ahora' <= fecha";
	$valido = (count(getArrayFromSQL($sql)) == 1);
	if ($valido){
		$sql  = "DELETE FROM usuarios_ott WHERE usuario='$usuario' AND token='$valor' AND '$ahora' <= fecha";
		if (execSql($sql)){
			return TRUE;
		}
	}
	return FALSE;
}

function doRegisterLoginFail($usuario){
	// Contador de fallos de AAA
	execSql("UPDATE usuarios SET aaa_fallos=aaa_fallos+1 WHERE nombre='$usuario'");			
}

function doCleanLoginFails($usuario){
	// Limpiar contador de historial de fallos de AAA
	return execSql("UPDATE usuarios SET aaa_fallos=0 WHERE nombre='$usuario'");
}

function doLogin($direccion, $usuario, $clave, &$mensaje){
	global $_SESSION;
	global $DAYCOPASS_ADMIN_PW;
	global $DAYCOPASS_IMAP_SERVER;
	global $DAYCOPASS_LDAP_SERVER;
	global $DAYCOPASS_MAX_FAILED_LOGINS;
	global $DAYCOPASS_DISABLE_LEVEL; 
	global $DAYCOPASS_MAIL_UNBLOCK_SUBJECT;
	global $DAYCOPASS_MAIL_UNBLOCK_BODY;
	global $DAYCOPASS_URL;
	global $DAYCOPASS_READONLY;
	global $DAYCOPASS_MAIL_WARN_ADDRESS;

	// Mensaje de error por defecto	
	$mensaje = "Error desconocido al autenticar el usuario '$usuario'";
	
	// Caso especial del usuario administrativo
	if ($usuario == "admin"){
		if ($clave == $DAYCOPASS_ADMIN_PW){
			$_SESSION["usuario"] = "admin";
			$mensaje = doLog("Sesion 'admin' iniciada");
			doLog($mensaje);
			return TRUE;
		}else{
			doLogout();
			$mensaje = "Clave incorrecta";
			doLogWithoutLogin($mensaje, "admin", LOG_ERR);
			return FALSE;
		}
	}

	// Usuario registrado?	
	$registrado = FALSE;
	$usuario_registrado = array();
	foreach(getUsernames() as $usuario_registrado){
		if ($usuario == $usuario_registrado){
			$registrado = TRUE;
			break;
		}
	}	
	if (!$registrado){
		$mensaje = "Usuario no registrado '$usuario'";
		doLogWithoutLogin($mensaje, $usuario, LOG_ERR);
		return FALSE;
	}

	// Usuario deshabilitado/bloqueado?
	$nivel = getLevel($usuario);
	if (!in_array($nivel, getLevels())){
		if ($nivel == $DAYCOPASS_DISABLE_LEVEL){
			$mensaje = "Usuario '$usuario' deshabilitado";
		}else{
			$mensaje = "Usuario '$usuario' bloqueado";
		}
		doLogWithoutLogin($mensaje, $usuario, LOG_ERR);
		return FALSE;
	}

	// Demasiados intentos fallidos de acceso?
	$sql = "SELECT * FROM usuarios WHERE nombre='$usuario' AND aaa_fallos >= $DAYCOPASS_MAX_FAILED_LOGINS";
	if (count(getArrayFromSQL($sql)) == 1){
		$mensaje = "Usuario '$usuario' bloqueado por demasiados fallos de autenticacion";
		doLogWithoutLogin($mensaje, $usuario, LOG_ERR);
		$atributos = array();
		if (doLdapGetUserInfo($usuario, $atributos, $mensaje)){
			doLogWithoutLogin($mensaje, $usuario);
			if (isset($atributos["mail"]["0"])){
				$asunto = str_replace("%user%", $usuario, $DAYCOPASS_MAIL_UNBLOCK_SUBJECT);
				$cuerpo = str_replace("%user%", $usuario, $DAYCOPASS_MAIL_UNBLOCK_BODY);
				$cuerpo = str_replace("%token%", doOneTimeToken($usuario, "PT1H"), $cuerpo);
				$cuerpo = str_replace("%url%", $DAYCOPASS_URL, $cuerpo);
				doMail($atributos["mail"]["0"], $asunto, $cuerpo);
			}
		}
		return FALSE;
	}

	// Autenticacion
	if (isset($_SESSION["usuario"]) && $_SESSION["usuario"] == $usuario){
		// Usuario ya ha iniciado sesion
		$mensaje = "Sesi&oacute;n previamente abierta del usuario '$usuario'";
		doLog($mensaje);
		return TRUE;
	}else{
		if (!empty($DAYCOPASS_LDAP_SERVER)){ // LDAP
			if (doLdapLogin($usuario, $clave, $mensaje)){
				$_SESSION["usuario"] = $usuario;
				$mensaje = "Sesion iniciada para el usuario '$usuario'";
				doLog($mensaje);
				doCleanLoginFails($usuario);
				return TRUE;
			}
		}else if (!empty($DAYCOPASS_IMAP_SERVER)){ // IMAP
			error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_USER_NOTICE);
			$mbox = imap_open(
				"{" . $DAYCOPASS_IMAP_SERVER . "/novalidate-cert/readonly}INBOX" 
				, "$usuario", "$clave"
			);
			if ($mbox) {
				imap_close($mbox);
				$_SESSION["usuario"] = $usuario;
				$mensaje = "Sesion iniciada para el usuario '$usuario'";
				doLog($mensaje);
				doCleanLoginFails($usuario);
				return TRUE;
			}else{
				$mensaje = "Clave incorrecta";
			}
		}
	}		

	if ($DAYCOPASS_READONLY){
		$asunto = "Intento fallido en servidor de solo lectura (DaycoPass)";
		$cuerpo = "Desde " . $_SERVER["REMOTE_ADDR"]. " el usuario '$usuario' genero un fallo de autenticacion.";
		doMail($DAYCOPASS_MAIL_WARN_ADDRESS, $asunto, $cuerpo);
	}else{
		doRegisterLoginFail($usuario);
	}
	doLogWithoutLogin($mensaje, $usuario, LOG_ERR);
	doLogout();
	return FALSE;
	
}

function doMail($to, $subject, $body, $html = FALSE){

	// Servidor de Correo Electronico (Relay)
	global $DAYCOPASS_MAIL_SERVER;
	global $DAYCOPASS_MAIL_PORT;
	global $DAYCOPASS_MAIL_TLS;
	global $DAYCOPASS_MAIL_AUTH;
	global $DAYCOPASS_MAIL_LOGIN; //Solo si AUTH=true
	global $DAYCOPASS_MAIL_PASSWORD; //Solo si AUTH=true
	global $DAYCOPASS_MAIL_FROM;
	global $DAYCOPASS_MAIL_WARN_ADDRESS;

	$mail = new PHPMailer;

	//$mail->SMTPDebug = 3;                               // Enable verbose debug output

	$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
		)
	);

	$mail->isSMTP();                                      // Set mailer to use SMTP
	$mail->Host = $DAYCOPASS_MAIL_SERVER; 							  // Specify main and backup SMTP servers
	$mail->Port = $DAYCOPASS_MAIL_PORT;                   // TCP port to connect to
	$mail->SMTPAuth = $DAYCOPASS_MAIL_AUTH;               // Enable SMTP authentication
	if ($DAYCOPASS_MAIL_AUTH){
		$mail->Username = $DAYCOPASS_MAIL_LOGIN;            // SMTP username
		$mail->Password = $DAYCOPASS_MAIL_PASSWORD;         // SMTP password
	}
	if ($DAYCOPASS_MAIL_TLS) $mail->SMTPSecure = 'tls';   // Enable TLS encryption, `ssl` also accepte

	$mail->From = $DAYCOPASS_MAIL_FROM;
	$mail->FromName = 'Dayco Pass';

	$mail->addAddress($to);     													// Add a recipient
	if ($to != $DAYCOPASS_MAIL_WARN_ADDRESS) $mail->addCC($DAYCOPASS_MAIL_WARN_ADDRESS);					
	
	//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
	//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
	$mail->isHTML($html);                                  // Set email format to HTML

	$mail->Subject = $subject;
	$mail->Body    = $body;
	//$mail->AltBody = $body;

	if (!$mail->send()){
		doLog("Error al enviar el correo a '$to'. " . $mail->ErrorInfo);
	} else {
		$dest = implode(";", array_unique([$to, $DAYCOPASS_MAIL_WARN_ADDRESS]));
		doLog("Correo enviado a '$dest'. Asunto: $subject");
	}
}

function doCleanInvalidChars($string){
	return trim(htmlspecialchars_decode(htmlspecialchars($string, ENT_IGNORE)));
}

function crawlerExtractSocketURL($string, $trimNewLine = true){
   if ($trimNewLine) $string = str_replace("\n", " ", $string);
   $proto_ip_port = '[a-z]+\:\/\/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\:\d{1,5}';
   $proto_ip = '[a-z]+\:\/\/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}';
   $ip_port = '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\:\d{1,5}';
   $ip = '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}';
	 $pattern = "/(" . $proto_ip_port . "|" . $proto_ip . "|" . $ip_port . "|" . $ip .")/i";
   preg_match_all($pattern, $string, $matches);
	 $matches = array_shift($matches);
   return array_unique(array_merge(array_values(array_map('trim',$matches))));
}

function doGetTimeString()
{
	$mt = microtime(true);
	$now = DateTime::createFromFormat("U.u", number_format($mt, 6, '.', ''));
	$nowf = $now->format("Y-m-d H:i:s.u");
	return $nowf;
}

function doGetIniParams(){
	$names = [
		 "DAYCOPASS_ADMIN_LEVEL" => "i"
		,"DAYCOPASS_ADMIN_PW" => "s"
		,"DAYCOPASS_CAPTCHA" => "b"
		,"DAYCOPASS_DISABLE_LEVEL" => "i"
		,"DAYCOPASS_GUACAMOLE_URL" => "s"
		,"DAYCOPASS_IMAP_SERVER" => "s"
		,"DAYCOPASS_LDAP_BASEDB" => "s"
		,"DAYCOPASS_LDAP_BINDDN" => "s"
		,"DAYCOPASS_LDAP_BINDPW" => "s"
		,"DAYCOPASS_LDAP_FILTER" => "s"
		,"DAYCOPASS_LDAP_SERVER" => "s"
		,"DAYCOPASS_MAIL_AUTH" => "b"
		,"DAYCOPASS_MAIL_FROM" => "s"
		,"DAYCOPASS_MAIL_LOGIN" => "s"
		,"DAYCOPASS_MAIL_PASSWORD" => "s"
		,"DAYCOPASS_MAIL_PORT" => "i"
		,"DAYCOPASS_MAIL_SERVER" => "s"
		,"DAYCOPASS_MAIL_TLS" => "b"
		,"DAYCOPASS_MAIL_UNBLOCK_BODY" => "s"
		,"DAYCOPASS_MAIL_UNBLOCK_SUBJECT" => "s"
		,"DAYCOPASS_MAIL_WARN_ADDRESS" => "s"
		,"DAYCOPASS_MAX_FAILED_LOGINS" => "i"
		,"DAYCOPASS_ONLY_SYSLOG" => "b"
		,"DAYCOPASS_READONLY" => "b"
		,"DAYCOPASS_SQLMODES" => "s"
		,"DAYCOPASS_TITLE" => "s"
		,"DAYCOPASS_TOKENS" => "a"
		,"DAYCOPASS_URL" => "s"
	];
	return $names;
}

function doGetMySQLVersion() {
  $output = shell_exec('mysql -V');
  preg_match('@[0-9]+\.[0-9]+\.[0-9]+@', $output, $version);
  preg_match('@[0-9]+\.[0-9]+@', $version[0] , $version);
  return $version[0];
}

