<table style="border: none; background-color: black; margin: 0; padding: 0;" width="100%">

	<?php	if ($DAYCOPASS_READONLY){ ?>
	<div style="background-color: blue;">
  	<div class="blink_me" style="background-color: red;">
    	<div align="center" style="color: white;">
      	<h1>
        AMBIENTE DE SOLO LECTURA<br>
	      </h1>
  	    <h3>USTED NO PUEDE GUARDAR CAMBIOS EN EL SISTEMA</h3>
    	</div>
	  </div>
	</div>
	<?php } ?>

	<tr>
		<td style="border: none;" width="30%">
			<img src="img/daycohost-w.png" height="64px" align="middle">
			<font style="font-size: 20px; color: white;"><b>DaycoPass 2.0</b></font>
		</td>
		<td style="border: none; color: white;" align="right">
			Sesi&oacute;n de <?php echo "<b>" . getUsername() . "</b> (Nivel: " . getLevel() . ")"; ?><br>
			<button id="button_menu_1" onclick="document.location='index.php';">Credenciales</button>
			<script type="text/javascript">
				$("#button_menu_1").button({icons:{primary: "ui-icon-unlocked"}})
			</script>

			<?php if (getLevel()>=$DAYCOPASS_ADMIN_LEVEL){ ?>
			<button id="button_menu_2" onclick="document.location='categories.php';">Categor&iacute;as</button>
			<script type="text/javascript">
				$("#button_menu_2").button({icons:{primary: "ui-icon-tag"}})
			</script>
			<button id="button_menu_3" onclick="document.location='services.php';">Servicios</button>
			<script type="text/javascript">
				$("#button_menu_3").button({icons:{primary: "ui-icon-tag"}})
			</script>
			<button id="button_menu_5" onclick="document.location='users.php';">Usuarios</button>
			<script type="text/javascript">
				$("#button_menu_5").button({icons:{primary: "ui-icon-contact"}})
			</script>
			<button id="button_menu_6" onclick="document.location='bitacora.php';">Bit&aacute;cora</button>
			<script type="text/javascript">
				$("#button_menu_6").button({icons:{primary: "ui-icon-script"}})
			</script>
			<?php } ?>
			<button id="button_menu_7" onclick="doRandomPw();">Clave Aleatoria</button>
			<script type="text/javascript">
				$("#button_menu_7").button({icons:{primary: "ui-icon-key"}})
			</script>
	
			<button id="button_menu_9" onclick="doLogout();">Cerrar</button>
			<script type="text/javascript">
				$("#button_menu_9").button({icons:{primary: "ui-icon-power"}})
			</script>
		</td>
	</tr>
</table>
<br>
