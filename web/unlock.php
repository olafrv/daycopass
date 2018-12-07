<?php

session_start();

header("Pragma: no-cache");
header("Expires: 0");

require("../libcommon.php");
require("../config.php");
require("../sslenforce.php");

$DAYCOPASS_IS_TOKEN_ALLOWED = TRUE;

require("../aaa.php"); // AAA procesa el desbloqueo
