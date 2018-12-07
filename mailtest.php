#!/usr/bin/php
<?php

require("lib/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");
require("libcommon.php");
require("config.php");

$_SERVER["REMOTE_ADDR"] = "127.0.0.1";

doMail($DAYCOPASS_MAIL_WARN_ADDRESS, "Test", "Test", $html = FALSE);

