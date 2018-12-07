<?php

	session_start();

	require("../libcommon.php");
	require("../config.php");
	require("../sslenforce.php");
	require("../aaa.php");

	require("header.php");

	if (!isMyLevel($DAYCOPASS_ADMIN_LEVEL)){
		$mensaje = "No tiene privilegios para crear categorias";
		doLog($mensaje, LOG_ERR);
		doScript("doFlash(\"$mensaje\");");
		require("footer.php");
		exit(1);
	}

	doLog("Acceso al modulo de categorias");

	require("menu.php");

	foreach(array("nombre", "id") as $var){
		$$var = !isset($_POST[$var]) ? NULL : $_POST[$var];
	}

	if (!empty($nombre)){
		// CHEQUEO DE DUPLICADOS PARA EVITAR CREDENCIALES HUERFANAS (MYSQL REPLACE + UNIQUE INDEX)
		$sql = "SELECT * FROM categorias WHERE nombre = '".$DAYCOPASS_DB->escape_string($nombre)."'";
		if(!empty(getArrayFromSql($sql))){
			$mensaje = "El registro '$nombre' ya existe!"; 
			doLog($mensaje, LOG_ERR);
			doScript("doFlash(\"$mensaje\");");
		}else{
			$id = empty($id) ? "NULL" : $id;
			$mensaje = "Se registro/actualizo la categoria '$nombre'";
			$sql = "REPLACE INTO categorias VALUES($id, '".$DAYCOPASS_DB->escape_string($nombre)."')";
			if (execSql($sql)){
				doLog($mensaje);
				doScript("doFlash(\"$mensaje\");");
			}else{
				$mensaje = "No se pudo realizar la operacion para la categoria '$nombre'";
				doLog($mensaje, LOG_ERR);
				doScript("doFlash(\"$mensaje\");");
			}
		}
	}

	$categorias = getArrayFromSql("SELECT * FROM categorias ORDER BY nombre");

  if (!$DAYCOPASS_READONLY){
	
?>
	<form method="POST"> 
		<input type="hidden" id="id" name="id">
		<b>Categoria:</b> <input type="text" name="nombre" id="nombre" value="" maxlength="255">
		<button id="modificar" type="submit">Modificar</button>
		<script type="text/javascript">
				$("#modificar").button({icons:{primary: "ui-icon-pencil"}})
		</script>
	</form>

<?php } ?>

	<script type="text/javascript">
		function rellenar(id, nombre){
			$('#nombre').val(nombre);
			$('#id').val(id);
		}
	</script>

  <b>Advertencias:</b><br>
  <ul>
  <li style="color: red;">Si modifica una <b>categoria no existente</b>, se crear&aacute; una nueva.</li>
  <li>Las categorias <b>no pueden ser eliminadas</b> solo modificadas.</li>
  </ul>

	<table id='tablaResultado' class="display cell-border compact">
		<thead>
			<tr>
				<th>Id</th>
				<th>Nombre</th>
				<th>Acciones</th>
			</tr>		
		</thead>
		<tbody>
<?php
		foreach($categorias as $registro){
?>
			<tr>
			<td><?= $registro["id"] ?></td>	
			<td><?= $registro["nombre"]; ?></td>
			<td align="center">
<?php   if (!$DAYCOPASS_READONLY){ ?>
			<button id="editar_<?=$registro["id"];?>" 
				onclick="rellenar('<?=$registro["id"] . "',' " . $registro["nombre"]; ?>');">Editar</button>
			<script type="text/javascript">
				$("#editar_<?=$registro["id"];?>").button({icons:{primary: "ui-icon-pencil"}})
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
