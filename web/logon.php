<?php

	session_start();

	require("../libcommon.php");
	require("../config.php");
	require("../sslenforce.php");
	require("header.php"); 
?>
<script language="Javascript">
	function recaptcha()
	{
		$("#captcha").prop("src", '/securimage/securimage_show.php?' + Math.random());
		$("#clave").val("");
		$("#captcha_code").val("");
	}
	function login()
	{
		if ($("#usuario").val() == ""){
 			doFlash('<font style="color:red;">Debe introducir un nombre de usuario</font>');
			return false;
		}
    if ($("#clave").val() == ""){
 			doFlash('<font style="color:red;">Debe introducir una clave</font>');
			return false;
		}
		<?php if ($DAYCOPASS_CAPTCHA) { ?>
    if ($("#captcha_code").val() == ""){
 			doFlash('<font style="color:red;">Debe introducir el codigo de la imagen</font>');
			return false;
		}
	  <?php } ?>
		$("#entrar").prop("disabled", true);
		$("#ventana").dialog({ title:"Autenticando", modal: true });
		$("#ventana").empty();
		$("#ventana").append(
			'<img src="img/loading.gif" width="50px" align="middle">'
			+ '<font style="color:green;">Espere...</font>'
		);
		$.ajax({
			url: "login.php", 
			type: "POST",
			data: { 
				usuario: $("#usuario").val(), 
				clave: $("#clave").val(),	
				captcha_code: $("#captcha_code").val(),
				csrf: $("#csrf").val()	
			}
		}).done(function(resp) {
			$("#ventana").dialog("close");
			if (resp == "OK"){	
				document.location = "index.php";
			}else{
				doFlash('<font style="color:red;">'+resp+'</font>', function (){
					// Forzar generacion de CSRF and Captcha en caso de error
					document.location = "logon.php?reload="+Math.random(); 
				});
			}
 		})
 		.fail(function(resp) {
 			$("#ventana").dialog("close");
 			doFlash('<font style="color:red;">Error de conexion (Ajax)</font>');
		});
		recaptcha();
		$("#entrar").prop("disabled", false);
	}
</script>
<form id="datos">
	<?php $_SESSION["csrf"] = getToken(); ?>
	<input type="hidden" id="csrf" name="csrf" value="<?php echo $_SESSION["csrf"];?>"/>
	<table align="center">
		<thead>
			<tr>
				<th colspan="2" style="font-size: 16px; padding: 3px;" align="left">
					<img src="img/daycohost-w.png" height="64px" align="middle">
					<i>DaycoPass 2.0&nbsp;&nbsp;</i>
				</th>						
			</tr>
		</thead>
		<tr>
			<td>					
				<table align="center">
					<tr>
						<td colspan="2">
							<br>
						</td>
					</tr>
					<tr>
						<td><b>Usuario:</b></td>
						<td>
							<input type="text" id="usuario" name="usuario">
						</td>
					</tr>
					<tr>
						<td><b>Clave:</b></td>
						<td>
							<input type="password" id="clave" name="clave" AUTOCOMPLETE="off">
							<div id="bloqmayu" style="display: none;">
								<img src="img/exclamation.png" height="24px" width="24px" align="middle"/>
								 <b>BLOQ. MAY&Uacute;S.</b>
							</div>
						</td>
					</tr>
					<?php if ($DAYCOPASS_CAPTCHA) { ?>
					<tr>
						<td><b>Captcha:</b></td>
						<td>
							<img id="captcha" src="securimage/securimage_show.php" alt="Captcha"/>
							<br>
							<input type="text" id="captcha_code" name="captcha_code" size="10" maxlength="6" />
							<a href="#" onclick="recaptcha();return false;">[Regenerar]</a>
						</td>
					</tr>
					<?php } ?>
					<tr>
						<td colspan="2" align="center">
							<br>
							<button type="button" id="entrar" onclick="login();">Entrar</button>
							<script language="Javascript">
								$("#entrar").button({icons:{primary: "ui-icon-play"}})
							</script>
						</td>
					</tr>
				</table>
			</td>													
		</tr>
	</table>
</form>

<script language="Javascript">
	// https://developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent/getModifierState
	document.addEventListener('keydown', function(event){
  	var isOn = event.getModifierState && event.getModifierState( 'CapsLock' );
		if (isOn){
	 		$('#bloqmayu').show();
		}else{
			$('#bloqmayu').hide();
		}
	});
	$("#clave").keyup(function(e){
		if (e.keyCode==13){ //ENTER
			login();
		}
	});
	$("#captcha_code").keyup(function(e){
		if (e.keyCode==13){ //ENTER
			login();
		}
	});
</script>

<?php 
	require("footer.php");
?>


