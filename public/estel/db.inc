<?php

	$mysqli = new CKBMysqli('localhost', 'webitestelmd_estelmddbuser', '&K0o5GDVEQ&e', 'webitestelmd_estelmddata');//'user', 'password', 'db_name'

	if (mysqli_connect_errno()) {
   printf("Could not connect. Error number: %s\n", mysqli_connect_error());
   exit;
	}

  $mysqli->query("SET NAMES utf8");

  mb_internal_encoding("utf-8");

?>