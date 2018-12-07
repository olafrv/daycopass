<?php

	session_start();

	require("../libcommon.php");
	require("../config.php");
	require("../sslenforce.php");
	require("../aaa.php");

	require("header.php");

	if (!isMyLevel($DAYCOPASS_ADMIN_LEVEL)){
		$mensaje = "No tiene privilegios para modificar usuarios";
		doLog($mensaje, LOG_ERR);
		doScript("doFlash(\"$mensaje\");");
		require("footer.php");
		exit(1);
	}

	doLog("Acceso al modulo de usuarios");
	
	require("menu.php");

	foreach(array("nombre","nivel", "nnivel", "aaa_fallos", "desbloquear") as $var){
		$$var = !isset($_POST[$var]) ? NULL : $_POST[$var];
	}

	if (!empty($nombre) && (!is_null($nivel) || !is_null($nnivel))){
		$nombre = strtolower(trim($nombre));
		if (preg_match('/^[0-9a-z_]+$/',$nombre)!==1){
			$mensaje = "Formato invalido de nombre de usuario (i.e. '0-9', 'a-z' y '_')";
			doLog($mensaje, LOG_ERR);
			doScript("doFlash(\"$mensaje\");");
		}else{
			$nivel = (int) (!is_null($nnivel) ? $nnivel : $nivel);
			if (empty($desbloquear)){
				$resultado = getArrayFromSql("SELECT aaa_fallos FROM usuarios WHERE nombre='$nombre'");
				if (count($resultado)>0){
					$aaa_fallos = $resultado[0]["aaa_fallos"];
				}else{
					$aaa_fallos = empty($aaa_fallos) ? 0 : ((int) $aaa_fallos);
				}
			}else{
				$aaa_fallos = 0;
			}
			$sql = "REPLACE INTO usuarios VALUES('".$DAYCOPASS_DB->escape_string($nombre)."', $nivel, $aaa_fallos)";
			if (execSql($sql)){
				$mensaje = "Se registro/actualizo el usuario '$nombre' y nivel '$nivel'";
				doLog($mensaje);
				doScript("doFlash(\"$mensaje\");");
			}else{
				$mensaje = "No se pudo realizar la operacion del usuario '$nombre'";
				doLog($mensaje, LOG_ERR);
				doScript("doFlash(\"$mensaje\");");
			}
		}
	}

	$usuarios = getArrayFromSql("SELECT * FROM usuarios ORDER BY nombre");
	$niveles = getLevels($DAYCOPASS_DISABLE_LEVEL);

	if (!$DAYCOPASS_READONLY){

?>
	<form method="POST"> 
		<input type="hidden" id="aaa_fallos" name="aaa_fallos">
		<b>Usuario:</b> <input type="text" name="nombre" id="nombre" value="" maxlength="100">
		<b>Nivel:</b> 
		<select name="nivel" id="nivel">
			<?php
				foreach($niveles as $nivel){
					echo "<option value='".$nivel."'>".$nivel."</option>";
				}
			?> 
		</select>
		<input type="checkbox" name="desbloquear" id="desbloquear" value="1">Desbloquear</input>
		<input type="checkbox" onchange="agregarNivel();" name="agregarNivelCheck" id="agregarNivelCheck">
			Crear nivel
		</input>
		<button id="modificar" type="submit">Modificar</button>
		<script type="text/javascript">
				$("#modificar").button({icons:{primary: "ui-icon-pencil"}})
		</script>
	</form>
	<br>

<?php } ?>

	<script type="text/javascript">
		function rellenar(nombre, nivel, aaa_fallos){
			$('#nombre').val(nombre);
			$('#aaa_fallos').val(aaa_fallos);
			if ($("#agregarNivelCheck").prop("checked")){
				$('#nnivel').val(nivel);
			}else{
				$('#nivel').val(nivel);
			}
		}
		function agregarNivel(){
			if ($("#agregarNivelCheck").prop("checked")){
				$("select[id='nivel']").hide();
				$("select[id='nivel']").after(
					'<input type="text" name="nnivel" id="nnivel" size="10" maxlength="10">'
				);
			}else{
				$("select[id='nivel']").show();			
				$("input[id='nnivel']").remove();
			}
		}
	</script>

	<b>Advertencias:</b><br>
	<ul>
	<li>
		El usuario <b>admin</b> está reservado para <b>ermergencias</b>, tales como: fallas de 
    auntenticacion (LDAP/IMAP), permisos revocados erroneamente u otros.
	</li>
  <li>
	  El nombre de usuario puede contener solamente los siguientes caracteres '0-9', 'a-z' y '_'.
    Las letras mayusculas seran convertidas a letras minusculas.
  </li>
	<li>
		Solamente los usuarios con nivel administrativo <b><?php echo $DAYCOPASS_ADMIN_LEVEL; ?></b>
    pueden gestionar usuarios, categorias, tipos de servicios y ver la bitacoras del sistema.
	</li>
	<li>
		Cada usuario tiene un <b>nivel asociado</b> y solo puede agregar, modificar y visualizar
    credenciales de un nivel igual o inferior al mismo.
	</li>
	<li>
		Los usuarios con <b>nivel <?php echo $DAYCOPASS_DISABLE_LEVEL; ?>
		se mantienen deshabilitados</b> y no pueden iniciar sesion hasta que no sean desbloqueos.
	</li>
	<li>
		Los <b>niveles disponibles en el sistema</b> son iguales a los <b>niveles de las 
    credenciales y usuarios</b> juntos. 
	</li>
	<li>
		Para <b>crear un nuevo nivel</b> debe <b>asignarse dicho nivel</b> a un usuario nuevo
    o existente.
	</li>
	<li>
		Despues de <b><?php echo $DAYCOPASS_MAX_FAILED_LOGINS; ?> intentos
		fallidos de autenticacion (AAA)</b> los usuarios son bloqueados.
	</li>
	<li>Los valores anteriores son parametrizables en el archivo <b>../daycopass.ini</b>
	</li>

	</ul>

	<table id='tablaResultado' class="display cell-border compact">
		<thead>
			<tr>
				<th>Id</th>
				<th>Nombre</th>
				<th>Nivel</th>
				<th>Fallos (AAA)</th>
				<th>Acciones</th>
			</tr>		
		</thead>
		<tbody>

<?php
		foreach($usuarios as $registro){
?>
			<tr>
			<td></td>
			<td><?= $registro["nombre"]; ?></td>
			<td><?= $registro["nivel"]; ?></td>
			<td><?= $registro["aaa_fallos"]; ?></td>
			<td align="center">
<?php   if (!$DAYCOPASS_READONLY){ ?>
			<button id="editar_<?=$registro["nombre"];?>" onclick="rellenar('<?=$registro["nombre"] . "','" . $registro["nivel"] . "','" . $registro["aaa_fallos"]; ?>');">Editar</button>
			<script type="text/javascript">
				$("#editar_<?=$registro["nombre"];?>").button({icons:{primary: "ui-icon-pencil"}})
			</script>
<?php }else{ ?>
      <i>No disponibles</i>
<?php } ?>
			</td>
			</tr>
<?php
		}
?>
		</tbody>
	</table>

	<script type="text/javascript">
		// Comentar para deshabilitar
		dynamicTable(
			"tablaResultado" , [[ 0, 'asc' ]]
		);
	</script>

<?php
	require("footer.php");
?>
