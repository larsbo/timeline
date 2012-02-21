<?php
session_start();

require_once 'class/admin.class.php';

// prechecking users table
Admin::checkAndUpdateTableUsers();

// submit form
if ($_POST['username'] && $_POST['password']) {
	$user = mysql_real_escape_string($_POST['username']);
	$pass = mysql_real_escape_string($_POST['password']);
	$login = Admin::login($user, $pass);
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Timeline - Admin</title>
  <link rel="stylesheet" type="text/css" href="css/admin.css" />
  <link rel="stylesheet" type="text/css" href="css/noty.css" />
  <link rel="stylesheet" type="text/css" href="css/cleditor.css" />
  <link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/themes/flick/jquery-ui.css" />	
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
  <script type="text/javascript" src="js/jquery.ui.datepicker-de.js"></script>
  <script type="text/javascript" src="js/jquery.cleditor.js"></script>
  <script type="text/javascript" src="js/jquery.noty.js"></script>
  <script type="text/javascript" src="js/admin.js"></script>
<?php echo ColorClasses::getColorClasses(false)->toStyleDefinition(); ?>
</head>
<body>
<div class="adminContent">
	<div class="content">
<?php
// logged in
if (Admin::loggedIn()) {
?>
		<h2>Ereignisse</h2>
		<div class="bordered" id="eventList">
			<span id="new" class="button">Neues Ereignis eintragen</span>
			<div>
<?php //echo Admin::getEvents(); ?>
			</div>
		</div>
		<div class="bordered" id="eventDetails"></div>
	</div>
	<div class="config">
		<h2>Konfiguration</h2>
		<ul class="bordered">
			<li id="databaseUpdate" class="button">Datenbank Update</a></li>
			<li id="databaseRestart" class="button">Datenbanken neu anlegen und Testdaten einfügen</a></li>
			<li id="refreshbutton" class="button">Ansicht aktualisieren</a></li>
		</ul>
<?php
} else {
	// show login form

	if ($_POST['username'] && $_POST['password'] && !$login) {
		echo "Login fehlgeschlagen!";
	}
?>
    <form name="login" method="post" action="">
      <label for="username">Benutzername:</label>
      <input type="text" id="username" name="username" />
      <label for="password">Passwort:</label>
      <input type="password" id="password" name="password" />
      <input type="submit" value="Login" />
    </form>
<?php
}
?>
	</div>
</div>
<?php 
  if ($_GET['debug']) {
    // show debug messages only with ?debug=true
    echo "<div id=\"debug\" style=\"display:hidden;\">";
    Log::output();
    echo "</div>";
  }
?>
</body>
</html>
