<?php

	session_start();

	require("../lib/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");
	require("../libcommon.php");
	require("../config.php");
	require("../sslenforce.php");
	require("securimage/securimage.php");

	if (isset($_GET["logout"])){
		doLog("Sesion cerrada manualmente");
		doLogout();
		echo "OK";
	}else{
		$captchaPassed = FALSE;
		if ($DAYCOPASS_CAPTCHA){
			$captcha_code = 
				isset($_POST["captcha_code"]) && !empty($_POST["captcha_code"]) ? 
					filter_var($_POST["captcha_code"], FILTER_SANITIZE_STRING) : NULL;
			$securimage = new Securimage();
			$captchaPassed = $securimage->check($captcha_code);
		}else{
			$captchaPassed = TRUE; // Obviar no esta activado
		}
		$usuario = 
			isset($_POST["usuario"]) && !empty($_POST["usuario"]) ? 
				filter_var($_POST["usuario"],FILTER_SANITIZE_STRING) : NULL;
		$clave = isset($_POST["clave"]) && !empty($_POST["clave"]) ? $_POST["clave"] : NULL;
		$csrfToken = isset($_POST["csrf"]) && !empty($_POST["csrf"]) ? $_POST["csrf"] : NULL;
		if ($captchaPassed){
			if (doCheckToken('csrf,'.$csrfToken, $_SERVER["REMOTE_ADDR"])){
				if (!is_null($usuario) && !is_null($clave)) {
					dologin($_SERVER["REMOTE_ADDR"], $usuario, $clave, $mensaje);	
				}else{
					$mensaje = "Introduzca un usuario y su clave";
				}
			}else{
				$mensaje = "Token CSRF invalido";
			}
		}else{
			$mensaje = "Codigo (Captcha) invalido";
		}
		if (isLoggedIn()){
			echo "OK";
		}else{
			doLogWithoutLogin($mensaje, $usuario, LOG_ERR);
			echo $mensaje;
		}
	}
	
	doCloseDB();

