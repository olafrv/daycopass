		<br>
		<div align="center" style="font-size: 10px;">
			<a target="_blank" 
				href="https://es.wikipedia.org/wiki/Venezuela"><img 
					border="0" src="img/venezuela.png" width="25px" align="middle"></a>
			<a href="http://www.daycohost.com">Daycohost</a>
			<?php
				if (date("Y") == "2015"){
					echo " - @Copyright 2015";
				}else{
					echo " - @Copyright 2015 - " . date("Y");
				}
			?>
      <br>
			Documentación: 
      <a href="http://wiki.daycohost.local/tec:pts:sis:daycopass">[WikiDayco]</a>
      <a href="/doc/index.html">[Daycopass]</a>
		</div>
		<?php doCloseDB(); ?>
	</body>
</html>

