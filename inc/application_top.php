<?php
$mysql = mysql_connect(DB_HOST, DB_USER, DB_PASS) or die('MySQL Server Error');
mysql_select_db(DB_NAME) or die('MySQL Database Error');
?>