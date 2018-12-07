<?php

if (!isset($_SERVER["HTTPS"]) || empty($_SERVER["HTTPS"])){
	doFlash("Debe usar HTTPS para acceder a este sistema.");
	doScript('document.location="' . $DAYCOPASS_URL . $_SERVER["REQUEST_URI"] . '";');	
	exit(0);	
}
