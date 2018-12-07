<?php

session_start();

require("../libcommon.php");
require("../config.php");
require("../sslenforce.php");
require("../aaa.php");

doLog("Acceso al modulo de credenciales");

$frase = isset($_POST["frase"]) ? substr($_POST["frase"],0,100) : NULL;
$frase_categoria = isset($_POST["frase_categoria"]) ? ((int) $_POST["frase_categoria"]) : NULL;
$historial = (isset($_POST["historial"]) && $_POST["historial"]=="1") ? "checked" : "";
$invalido = (isset($_POST["invalido"]) && $_POST["invalido"]=="1") ? "checked" : "";

$tabla = "claves";
$concat_historial = "";
// if (!empty($historial) && getLevel()>=$DAYCOPASS_ADMIN_LEVEL){
if (!empty($historial)){
	$tabla = "claves_historial"; 
	$concat_historial = "x.id_clave, ";
}

$resultado_maximo = -1; // Por defecto, ilimitado (Paginación)
$limit_registros = "";
$where_categoria = "";
if (!empty($frase_categoria)){
	// Con filtro de categoria (ilimitado)
	$where_categoria = " AND y.id = $frase_categoria"; 
}else if (empty($frase)){
	// Sin filtro de categoria (limitado)
	// $resultado_maximo = 250;
	// $limit_registros = "LIMIT " . $resultado_maximo; 
}

if (empty($invalido)){
	$where_valido = " AND x.valido = 1"; 
}else{
	$where_valido = " AND x.valido = 0"; 
}

$resultado = array();
$resultado_cantidad = 0;
if (!empty($frase) || !empty($frase_categoria)){
	doLog("Consulta de credenciales por frase: "  . $frase);
	$sql = "
SELECT SQL_CALC_FOUND_ROWS x.*, y.nombre as categoria_nombre, z.nombre as servicio_tipo_nombre
FROM $tabla AS x, categorias AS y, tipos_servicios AS z
WHERE x.nivel <= " . getLevel() . " AND x.categoria = y.id AND x.tipo_servicio=z.id 
$where_categoria
$where_valido
AND (UCASE(CONCAT(x.id, ' ', $concat_historial x.servicio, ' ' ,  z.nombre, ' '
, x.usuario, ' ', y.nombre , ' ', x.nivel, ' ', x.url, ' ', x.fecha))
LIKE CONCAT(\"%\", UCASE(\"" . doEsc($frase) . "\"),\"%\") 
OR x.nota LIKE CONCAT(\"%\", UCASE(\"" . doEsc($frase) . "\"),\"%\"))
ORDER BY y.nombre, z.nombre, x.servicio, x.usuario, x.nivel DESC $limit_registros
";
	$resultado = getArrayFromSql($sql);	
	$resultado_cantidad = getArrayFromSql("SELECT FOUND_ROWS() AS cantidad");
	$resultado_cantidad = $resultado_cantidad[0]["cantidad"];
}

$sql1 = "SELECT id, nombre FROM categorias ORDER BY nombre;";
$categorias = getArrayFromSql($sql1);

$sql1 = "SELECT id, nombre FROM tipos_servicios ORDER BY nombre;";
$tipos_servicios = getArrayFromSql($sql1);

$niveles = getLevels(NULL, getLevel());

// <Campo> => width, align, maxlength (input)
// Id_Clave solo aplica en Historial!
$campos = array(
			"Id" => array(20, "center", 0, "")
			, "Id_Clave" => array(20, "left", 0, "")
			, "Valido" => array(20, "center", 0, "")
			, "Categoria" => array(200, "center", 0, "e.g. Nombre del cliente")
			, "Clave" => array(50, "center", (pow(2,8)-1), "")
			//, "Operaciones" => array(50, "center", 0, "") 
			, "Tipo_Servicio" => array(200, "center", 0, "")
			, "Servicio" => array(200, "center", (pow(2,8)-1), "e.g. FDQN, Nombre DNS")
			, "Usuario" => array(200, "center", (pow(2,8)-1), "e.g. root, accdayco")
			, "Nivel" => array(50, "center", 0, "")
			, "URL" => array(100, "center", (pow(2,8)-1), "e.g. 172.16.1.2, https://192.168.1.10:443")
			, "Nota" => array(200, "center", 0, "e.g. Direcciones IP secundarias, instrucciones adicionales, etc.")
			, "Fecha" => array(60, "center", 0, "")
			, "Fuente" => array(100, "center", 0, "")
);

$campos_ocultos = array(
		"Clave", "Nivel", "Fuente"
);

require("header.php"); 

?>
		<div id="datosConectar" style="display: none;">
			Listado de direcciones y puertos (TCP/IP) probados y abiertos:<br>
			<ul id="datosConectarListado"></ul>
		</div>

		<div id="datosAgregar" style="display: none;">
			<form id="datosAgregarForm">
				<input type="hidden" id="id" name="id"></input>
				<table style="border: none;" align="center">
					<tr>
						<td style="border: none;"><b>Categoria(*):</b></td>
						<td colspan="3" style="border: none;">					
							<select name="categoria" id="categoria">
<?php
									foreach($categorias as $registro){
										echo "<option value='".$registro["id"]."'>".$registro["nombre"]."</option>";
									}
?> 
							</select>
						</td>
					</tr>
					<tr>
						<td style="border: none;"><b>Valido(*):</b></td>
						<td colspan="3" style="border: none;">
							<select name="valido" id="valido">
<?php
									foreach(array(1=>"Si", 0=>"No") as $indice=>$valor){
										echo "<option value='".$indice."'>".$valor."</option>";
									}
?> 
							</select>
						</td>
					</tr>
					<tr>
						<td style="border: none;"><b>Tipo de Servicio(*):</b></td>
						<td colspan="3" style="border: none;">					
							<select name="tipo_servicio" id="tipo_servicio">
<?php
									foreach($tipos_servicios as $registro){
										echo "<option value='".$registro["id"]."'>".$registro["nombre"]."</option>";
									}
?> 
							</select>
						</td>
					</tr>
<?php
		foreach($campos as $campo => $valores)
		{
			if (in_array($campo, array("Servicio", "Usuario", "Clave", "URL")))
			{
				$maxlength = (int) $valores[2];
				$maxlengthstr = ($maxlength==0) ? "" : "maxlength=\"$maxlength\"";
				$campo_minus = strtolower($campo);
?>
					<tr>
						<td style="border: none;"><b><?=$campo;?> (*):</b></td>
						<td colspan="3" style="border: none;">
						<input type="text" name="<?=$campo_minus;?>" id="<?=$campo_minus;?>" <?=$maxlengthstr;?>>
<?php
				if ($campo == "Clave"){
?>
						<button id="button_regenerar" type="button" 
							onclick="$('#clave').val(LDAPCHPW_LIB.generarPassword());">Regenerar</button>
						<script language="Javascript">
							$("#button_regenerar").button({icons:{primary: "ui-icon-gear"}})
						</script>
<?php					
				} 
			 	echo $valores[3]; //Comentario
?>
						</td>
					</tr>
<?php
			}
		}
?>
					<tr>
						<td style="border: none;"><b>Nivel(*):</b></td>
						<td colspan="3" style="border: none;">
							<select name="nivel" id="nivel">
<?php
		foreach($niveles as $nivel){
			echo "<option value='".$nivel."'>".$nivel."</option>";
		}
?> 
							</select>
							<hr/>
						</td>
					</tr>
					<tr>
						<td style="border: none;"><b>Nota(*):</b></td>
						<td colspan="3" style="border: none;">
							<textarea rows="6" cols="30" name="nota" id="nota"></textarea>
							<br>
							<?= $campos["Nota"][3]; ?>
							<hr/>
						</td>
					</tr>
					<tr id="datosAgregarFechaFuente" style="display: none;">
						<td style="border: none;"><i>Fecha:</i></b></td>
						<td style="border: none;"><input type="text" name="fecha" id="fecha" disabled></td>
						<td style="border: none;"><i>Fuente:</i></b></td>
						<td style="border: none;"><input type="text" name="fuente" id="fuente" disabled></td>
					</tr>
					<tr>
						<td colspan="4" align="center" style="border: none;">
							<br>
							<button id="button_guardar" type="submit">Guardar</button>	
							<script language="Javascript">
								$("#button_guardar").button({icons:{primary: "ui-icon-disk"}})
							</script>
						</td>
					</tr>
				</table>	
			</form>		
		</div>

		<script type="text/javascript">
		
			function mostrarClave(id){
				url = "pw_get.php?ajax=1&id=" + id;
				if ($('#historial').attr("checked")) url = url + "&historial=1";
				$.ajax({
					url: url,
					dataType: "json"
				}).done(function(data) {
					$("#ventana").html(
						'Presione <b>CTRL+C</b> para copiar el texto:<br><br>'
						+'<input type="text" class="transparente" id="ventanaClaveText" size="25" value=""/>'
					);
					$.each(data, function(key, val) {
						if (key=="clave") $("#ventanaClaveText").val(val);	
					});
					$("#ventana").dialog({
						title: "Clave", modal: true, width: 300, height: 150
						, resizable: true, closeOnEscape: true
						, position: {my: "center top", at: "center top", of: window}
					});
					$("#ventanaClaveText").select();
				})
				.fail(function(resp) {
					doFlash('<font style="color:red;">Error: ' + resp.responseText + '</font>');
				});

			}

			function mostrarNota(id){
				url = "pw_get.php?ajax=1&id=" + id;
				if ($('#historial').attr("checked")) url = url + "&historial=1";
				$.ajax({
					url: url,
					dataType: "json"
				}).done(function(data) {
					$.each(data, function(key, val) {
							if (key=="nota"){
									$("#ventana").empty();
									$("#ventana").append(
										'<div align="center">'+
										'<textarea id="ventanaNota" rows="15" cols="50"></textarea>'+
										'</div>'
									);
									valReplaced = val.replace(/&lt;13&gt;&lt;10&gt;/g,"\n"); // => PasswordMax \r\n
									$("#ventanaNota").text(valReplaced); // => PHP htmlentities()
							}
  				});
					$("#ventana").dialog({
						title: "Nota", modal: true, width: 500, height: 375
						, resizable: true, closeOnEscape: true
						, position: {my: "center top", at: "center top", of: window}
					});
				})
				.fail(function(resp) {
					doFlash('<font style="color:red;">Error: ' + resp.responseText + '</font>');
				});
			}

			function agregarCredencial(){
				$("#datosAgregarFechaFuente").hide();
				$("#datosAgregarForm")[0].reset();
				$("#clave").val(LDAPCHPW_LIB.generarPassword());
				$("#id").val("");
				$("#datosAgregar").dialog({
					title: "Agregar Credencial", modal: true, width: 550, height: 475
					, resizable: false, closeOnEscape: true
					, position: {my: "center top", at: "center top", of: window}
				});
			}
			
			function guacamole(finalUrlEncoded, needToken)
			{		
				if (needToken){	
					token = null;
					$.ajax({
						url: "ott.php",
						dataType: "text",
						async: false
					}).done(function(text) {
						token = text;
					}).fail(function(resp) {
						doFlash('<font style="color:red;">Error: ' + resp.responseText + '</font>');
					});
					if (token != null){
						window.open(decodeURI(finalUrlEncoded)  + "&token=ott,<?=getUsername();?>," + token);
					}else{
						doFlash('<font style="color:red;">Error: ' + resp.responseText + '</font>');
					}
				}else{
					window.open(decodeURI(finalUrlEncoded));
				}
			}	

			function checkPort(ip, port, parentId, childId, finalUrl, protocol, needToken)
			{
				var finalUrlEncoded = encodeURI(finalUrl);
				if (needToken) { needToken = "true" } else { needToken = "false" };				
				var childHtml = 
					'&nbsp; - <span style="display: inline-block;" id="' + childId + '"/>' + 
					'<a target="blank" href="#" onclick="guacamole(\'' + finalUrlEncoded + 
					'\', ' +  needToken + '); return false;">[' + protocol + ']</a>';
				$('#'+parentId).append(childHtml);
				$.ajax({
					url: "checkport.php?ip=" + ip + "&port=" + port,
					dataType: "text"
				}).done(function(text) {
					if (text == "OK"){
						$('#'+childId).addClass('ui-icon ui-icon-circle-check');
					}else{
						$('#'+childId).addClass('ui-icon ui-icon-circle-close');
					}
				}).fail(function(text) {
					$('#'+childId).addClass('ui-icon ui-icon-help');
				});
			}	

			function conectarCredencial(id){
				$.ajax({
					url: "pw_get.php?ajax=1&id=" + id,
					dataType: "json"
				}).done(function(data) {
					var urls = null;
					var protocols = { "http":80, "https":443, "ssh":22, "rdp":3389, "vnc":5900, "telnet":23 };
					var baseUrl = "<?=$DAYCOPASS_GUACAMOLE_URL;?>";
					var testCount = 0;
					$('#datosConectarListado').empty();
					$.each(data, function(key, val) {
						if (key=="crawler" && val.length>0){  urls = val.split(",");  }
					});
					if (urls == null){
						doFlash('<font style="color:red;">No hay direcciones IPv4 asociadas</font>');
						return false;
					}
					$.each(urls, function(urlIndex, url){
						if (url.length==0) return true;
						var urlHasHttp = (url.indexOf("http") == 0);
						var ip = url.split(":"); ip = ip[0];
						$('#datosConectarListado').append("<li id='datosConectarUrl"+urlIndex+"'></li>");
						if (urlHasHttp){
							$('#datosConectarUrl'+urlIndex).append(
								"<a target=\"blank\" href=\"" + url + "\">" + url + "</a>"
							);
						}else{
							$('#datosConectarUrl'+urlIndex).append(url);
							$.each(protocols, function(protocol, port){
								var finalUrl = "";
								var needToken = false;
								if (protocol == "http" || protocol == "https"){
									needToken = false;
									finalUrl = protocol + "://" + url;
								}else{
									needToken = true;
									finalUrl = baseUrl + "?id="+id+"&protocol=" + protocol + "&hostname=" + ip;
								}
								var parentId  = "datosConectarUrl" + urlIndex;
								var childId   = "datosConectarUrlTest" + testCount++;
								checkPort(ip, port, parentId, childId, finalUrl, protocol, needToken);
							});
							
						}
					});
					$("#datosConectar").dialog({
						title: "Conectar (Credencial Id="+id+")", modal: true, width: 650, height: 300
						, resizable: false, closeOnEscape: true
						, position: {my: "center top", at: "center top", of: window}
					});
				}).fail(function(resp) {
					doFlash('<font style="color:red;">Error: ' + resp.responseText + '</font>');
				});
			}
			
			function editarCredencial(id){
				$("#datosAgregarFechaFuente").show();
				$.ajax({
					url: "pw_get.php?ajax=1&id=" + id,
					dataType: "json"
				}).done(function(data) {
					$.each(data, function(key, val) {
					   $('#'+key).val(val);
  					});
					$("#datosAgregar").dialog({
						title: "Editar (Credencial Id="+id+")", modal: true, width: 550, height: 500
						, resizable: false, closeOnEscape: true
						, position: {my: "center top", at: "center top", of: window}
					});
				})
				.fail(function(resp) {
					doFlash('<font style="color:red;">Error: ' + resp.responseText + '</font>');
				});
			}
			
			$("#datosAgregarForm").submit(function(evento){
				$.ajax({
					url: "pw_insert.php?ajax=1", 
					data: $("#datosAgregarForm").serializeArray()
				}).done(function(resp) {
					if (resp == "OK"){
						$("#datosAgregar").dialog("close");
						doFlash('Credencial agregada', function(event, ui) { $("#filtroForm").submit(); });
					}else{
						doFlash('<font style="color:red;">' + resp + '</font>');
					}
				})
				.fail(function(resp) {
					doFlash('<font style="color:red;">' + resp + '</font>');
				});
				return false;
			});
		</script>

<?php 
		
	require("menu.php"); 

?>

		<form method="POST" name="filtroForm" id="filtroForm">
			<b>Frase:</b>
			<input type="text" name="frase" value="<?php echo $frase; ?>" maxlength="100">
			<b>Categoria:</b>
			<select name="frase_categoria" id="frase_categoria" length="100">
<?php
	$selected = $frase_categoria == 0 ? "selected" : "";
	echo "<option $selected value=''>--- TODAS ---</option>";
	foreach($categorias as $registro)
	{
		$selected = ($frase_categoria == $registro["id"]) ? "selected" : "";
		echo "<option $selected value='".$registro["id"]."'>".$registro["nombre"]."</option>";
	}
?> 
			</select>
			<script language="Javascript">
				$("#frase_categoria").chosen();
			</script>

<?php // if (getLevel()>=$DAYCOPASS_ADMIN_LEVEL) { ?>

			<input type="checkbox" <?php echo $historial; ?> name="historial" id="historial"
				value="1" onchange="document.filtroForm.submit();">Historial

<?php // } ?>

			<input type="checkbox" <?php echo $invalido; ?> name="invalido" id="invalido"
				value="1" onchange="document.filtroForm.submit();">Inv&aacute;lido(s)
			<button id="button_buscar" type="submit">Buscar</button>
			<script language="Javascript">
				$("#button_buscar").button({icons:{primary: "ui-icon-search"}})
			</script>

<?php if (empty($historial) && !$DAYCOPASS_READONLY){ ?>

			<button id="button_agregar" type="button" onclick="agregarCredencial();">Agregar</button>
			<script language="Javascript">
				$("#button_agregar").button({icons:{primary: "ui-icon-plus"}})
			</script>

<?php } ?>

<?php
	if ($resultado_maximo > -1){
		if ($resultado_cantidad > $resultado_maximo){
		echo "<br><i>";
		echo "B&uacute;squeda con resultado de $resultado_cantidad registros truncada a $resultado_maximo";
		echo " porque no se ha introducido una frase y/o seleccionado una categor&iacute;a.</i>";
		echo "</i>";
		}
	}
?>
		</form>

		<br>
		
		<table id="tablaResultado" class="display cell-border compact" style="table-layout: fixed;" width="100%">
			<thead>
				<tr class="enc6">
					<th>#</th>
<?php

	foreach($campos as $campo => $attributos){
		if (in_array($campo, $campos_ocultos)) continue;
		if (empty($historial) && $campo == "Id_Clave") continue;	
		echo "<th align='center' class='dfl'>".strtoupper($campo)."</th>";	
	}
?>
				</tr>
			</thead>
			<tbody>
<?php
		foreach($resultado as $registro){
			$jsid = $registro["id"];
			echo "<tr>";
			echo "<td align='center'></td>";
			foreach($campos as $campo => $atributos){
				if (in_array($campo, $campos_ocultos)) continue;
				if (empty($historial) && $campo == "Id_Clave") continue;	
				$campo_minus = strtolower($campo);
				echo "<td width=\"".$atributos[0]."px\" align=\"".$atributos[1]."\" style=\"word-wrap:break-word\">";
				if ($campo == "Id"){
					echo $registro[$campo_minus];	
					if (empty($historial)){	
?>
					<br>
<?php
						if (empty($historial) && !$DAYCOPASS_READONLY){ 
?>
						<button id="button_edit_<?=$jsid;?>" onclick="editarCredencial(<?=$jsid;?>);">Editar</button>
						<script language="Javascript">
						$("#button_edit_"+"<?=$jsid;?>").button({icons:{primary: "ui-icon-pencil"}})
						</script>
					
<?php
						}
					}

				}else if ($campo == "Categoria"){
					echo $registro["categoria_nombre"];	
				}else if ($campo == "Tipo_Servicio"){
					echo $registro["servicio_tipo_nombre"];	
				}else if ($campo == "Servicio"){
					echo $registro[$campo_minus];
					if (empty($historial) && !empty($DAYCOPASS_GUACAMOLE_URL) && !$DAYCOPASS_READONLY){	
?>
					<button id="button_connect_<?=$jsid;?>" onclick="conectarCredencial(<?=$jsid;?>);">Conectar</button>
					<script language="Javascript">
						$("#button_connect_"+"<?=$jsid;?>").button({icons:{primary: "ui-icon-play"}})
					</script>
<?php
					}
				}else if ($campo == "Usuario"){
					echo $registro[$campo_minus] . "<br>";	
?>
					<button id="button_spw_<?=$jsid;?>" onclick="mostrarClave(<?=$jsid;?>);">Clave</button>
					<script language="Javascript">
				   $("#button_spw_" + "<?=$jsid;?>" ).button({
				   		icons: {
        				primary: "ui-icon-key"
			      	}
			    	})
					</script>
<?php
				}else if ($campo == "Nota"){
					if (strlen($registro[$campo_minus])<=30){
						echo htmlentities($registro[$campo_minus]);
					}else{
						echo htmlentities(substr($registro[$campo_minus],0,30));
?>
						<button id="button_ntn_<?=$jsid;?>" onclick="mostrarNota(<?=$jsid;?>);">...</button>				
						<script language="Javascript">
							$("#button_ntn_"+"<?=$jsid;?>").button({icons:{primary: "ui-icon-comment"}})
						</script>
<?php
					}
				}else if ($campo == "Valido"){
					if ($registro[$campo_minus] == 1){
?>
						<div class="ui-widget">
							<div class="ui-state-highlight ui-corner-all">
								<p>
									<span class="ui-icon ui-icon-circle-check"></span>
									V&aacute;lido
								</p>
							</div>
						</div>
<?php
					}else{
?>
						<div class="ui-widget">
							<div class="ui-state-error ui-corner-all">
								<p>
									<span class="ui-icon ui-icon-circle-close"></span>
									<strong>Inv&aacute;lido</strong>
								</p>
							</div>
						</div>
<?php
					}
				}else{
					echo $registro[$campo_minus];
				}
				echo "</td>";
			}
			echo "</tr>";
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
