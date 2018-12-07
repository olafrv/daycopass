<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $DAYCOPASS_TITLE; ?></title>
		<meta charset="UTF-8"/>

		<script type="text/javascript" src="js/jquery-1.12.4.min.js"></script>

		<link rel="stylesheet" href="js/jquery-ui-themes-1.12.1/themes/south-street/jquery-ui.min.css"/> 
		<script type="text/javascript" src="js/jquery-ui-1.12.1/jquery-ui.min.js"></script>

		<script type="text/javascript" src="js/ldapchpw_lib.js"></script>

		<link rel="stylesheet" type="text/css" href="js/DataTables-1.10.12/media/css/dataTables.jqueryui.min.css"/>
 		<script type="text/javascript" src="js/DataTables-1.10.12/media/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="js/DataTables-1.10.12/media/js/dataTables.jqueryui.min.js"></script>

		<link rel="stylesheet" type="text/css" href="js/chosen/chosen.min.css"/>
		<script src="js/chosen/chosen.jquery.min.js" type="text/javascript"></script>
		
		<link rel="stylesheet" href="css/daycopass.css<?php echo "?version=" . time() ;?>"/>
 		<link rel="icon" type="image/jpeg" href="/img/daycohost-m.jpg"/>

		<script type="text/javascript">
			// Dialogo de Mensajes Instantaneos
			function doFlash(message, onclosefunc = null){
				$("#flash").dialog({
					title: "Informacion", modal: true, witdh: 400, height: 200
					, resizable: true, closeOnEscape: true
					, position: {my: "center top", at: "center top", of: window}
					, close: onclosefunc
				});	
				$("#flashMessage").empty();
				$("#flashMessage").append(message);
			}
			// Dialogo de Clave Aleatoria		
			function doRandomPw(){
				$("#ventana").dialog({
					title: "Clave (Aleatoria)", modal: true, width: 300, height: 150
					, resizable: true, closeOnEscape: true
					, position: {my: "center top", at: "center top", of: window}
				});
				$("#ventana").html(
					'Presione <b>CTRL+C</b> para copiar el texto:<br><br>'
					+'<input type="text" class="transparente" id="ventanaClaveAleatoriaText" size="25" value=""/>'
				);
				$("#ventanaClaveAleatoriaText").val(LDAPCHPW_LIB.generarPassword());	
				$("#ventanaClaveAleatoriaText").select();
			}
			// Estilo dinamico de Tablas
			function dynamicTable(id, order)
			{
				var tablePosition = $("#"+id).offset();
				var tableRowHeight = $("#"+id + " tr:nth-child(2)").height();
				var pageLength = Math.round((window.innerHeight  - Math.round(tablePosition.top)) / tableRowHeight) - 3;
				if (pageLength<=0) pageLength = 5;
				$(document).ready(function(){
  			  var t = $('#'+id).DataTable({
						"dom": 'p<irf>t'
						,"order": order
            , "pageLength": pageLength
						, "fnInitComplete": function(oSettings, json) {
							$(".dataTables_wrapper .dataTables_paginate").css({
								"float" : "left"
							});
				    }
						, "language": {
							"emptyTable": "No hay registros para mostrar"
							, "info": "<font style='color: green; font-size: 16px'><b>_TOTAL_</b></font> registros <b>encontrados<b>"
							, "infoEmpty": "No hay registros coincidentes"
							, "infoFiltered": "<i>(de _MAX_ en total)</i>"
			    		, "loadingRecords": "Cargando..."
  			  		, "processing": "Procesando..."
							, "search": "Buscar por esta <b>frase adicional</b>:"
		    			, "zeroRecords": "No hay registros coincidentes"
	  		      , "paginate": {
  	    		      "first":      "Primera"
    	      		  , "previous":   "Anterior"
		      	      , "next":       "Siguiente"
    		    	    , "last":       "Ultima"
	      		  }
		  	   		, "aria": {
    			    	"sortAscending":  ": activar para ordenar ascendente"
      			  	, "sortDescending": ": activar para ordenar descendente"
					    }
						}
			 		});
			 	  t.on('order.dt search.dt', function () {
     			  t.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
    	      		cell.innerHTML = i+1;
	      	  });
  			  }).draw();
				});
			}
			// Cerrar sesion
			function doLogout(){
				$.ajax({
					url: "login.php?logout=1"
				}).done(function(resp) {
					if (resp == "OK"){
						document.location = "logon.php";
					}else{
						doFlash("Error interno al cerrar la sesion");
					}
		 		})
				.fail(function(resp) {
					doFlash("Error de conexion al cerrar la sesion");
				});
			}	
	</script>
	</head>
	<body>
	   <!-- Dialogo de Mensajes Instantaneos -->
		<div id="flash" style="display: none;" class="ui-widget">	
			<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<div id="flashMessage"></div>
				</p>
			</div>
			<br>
		</div>
		<div id="ventana" style="display: none;"></div>

