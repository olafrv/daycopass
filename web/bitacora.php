<?php

	session_start();

	require("../libcommon.php");
	require("../config.php");
	require("../sslenforce.php");
	require("../aaa.php");

	require("header.php");

	if (!isMyLevel($DAYCOPASS_ADMIN_LEVEL)){
		$mensaje = "No tiene privilegios para ver la bitacora";
		doLog($mensaje, LOG_ERR);
		doScript("doFlash(\"$mensaje\");");
		require("footer.php");
		exit(1);
	}

	doLog("Acceso al modulo de bitacora");

	$frase = isset($_POST["frase"]) ? substr($_POST["frase"],0,100) : NULL;
	$cantidad = isset($_POST["cantidad"]) ? ((int) $_POST["cantidad"]) : 1000;
	$maxCantidad = 20000;
	if ($cantidad>$maxCantidad) $cantidad = $maxCantidad;

	$where = "";
	if (!is_null($frase)){
		doLog("Consulta de la bitacora por frase: " . $frase);
		$where = " WHERE ";
		$where .= "UCASE(CONCAT(id, ' ', direccion, ' ' , usuario, ' ', log, ' ', fecha))";
		$where .= "LIKE CONCAT(\"%\", UCASE(\"".$DAYCOPASS_DB->escape_string($frase)."\"),\"%\") ";
	}
	$sql = "SELECT * FROM bitacora $where ORDER BY id DESC LIMIT " . ((int) $cantidad);
	$registros = getArrayFromSql($sql);

	require("menu.php");

?>
	<form method="POST"> 
		<b>Frase:</b> <input type="text" name="frase" value="<?php echo $frase; ?>" maxlength="100">
		<b>Limitar resultado a:</b> 
		<input type="text" maxlength="6" size="6" name="cantidad" value="<?php echo $cantidad; ?>">
		<b>registros</b> (M&aacute;ximo <?php echo $maxCantidad; ?>)
		<button id="buscar" type="submit">Buscar</button>
		<script type="text/javascript">
				$("#buscar").button({icons:{primary: "ui-icon-search"}})
		</script>
	</form>

	<br>
<?php if ($DAYCOPASS_READONLY){ ?>
	<div class="ui-state-error ui-corner-all">
		<p>
			<span class="ui-icon ui-icon-alert"></span>
			La bitácora solo se guarda en el <b>archivo local</b> /var/log/daycopass/daycopass.log
      cuando esta activado el modo de <b>solo lectura</b>. 
<?php }else{ ?>
	<div class="ui-state-highlight ui-corner-all">
		<p>
			<span class="ui-icon ui-icon-info"></span>
			La bitácora se guarda en <b>base de datos</b> y en el <b>archivo local</b> /var/log/daycopass/daycopass.log. 
<?php } ?>
		</p>
	</div>
	<br>

	<table id='tablaResultado' class="display cell-border compact">
		<thead>
			<tr>
				<th>Id</th>
				<th>Direcci&oacute;n</th>
				<th>Usuario</th>
				<th>Detalle</th>
				<th>Fecha</th>
			</tr>		
		</thead>
		<tbody>
<?php
		foreach($registros as $registro){
?>
			<tr>
			<td><?= $registro["id"] ?></td>	
			<td><?= $registro["direccion"]; ?></td>
			<td><?= $registro["usuario"]; ?></td>
			<td><?= $registro["log"]; ?></td>
			<td><?= $registro["fecha"]; ?></td>
			</tr>
<?php
		}
?>
		</tbody>
	</table>

	<script type="text/javascript">
		// Comentar para deshabilitar
		dynamicTable(
			"tablaResultado" , [[ 0, 'desc' ]]
		);
	</script>

<?php
	require("footer.php");
?>
