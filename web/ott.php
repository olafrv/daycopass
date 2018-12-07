<?php

session_start();

header("Pragma: no-cache");
header("Expires: 0");

require("../libcommon.php");
require("../config.php");
require("../sslenforce.php");
require("../aaa.php");

echo doOneTimeToken(getUsername()); 
