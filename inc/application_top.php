<?php
$db_link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
if (!$db_link) echo '<div class="msg error immediate">
  <h3>Fehler!</h3>
  <p>Datenbank-Server <em><b>'.DB_HOST.'</b></em> nicht erreichbar!</p>
</div>'."\n";

$db_selected = mysql_select_db(DB_NAME, $db_link);
if (!$db_selected) echo '<div class="msg error immediate">
  <h3>Fehler!</h3>
  <p>'.mysql_error().'</p>
</div>'."\n";
?>