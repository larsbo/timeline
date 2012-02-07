<?php
session_start();

require_once 'class/admin.class.php';
require_once 'class/timeline.class.php';

// prechecking users table
Admin::checkAndUpdateTableUsers();
$timeline = new Timeline(0, 0);	//0,0 ???

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
  <link rel="stylesheet" type="text/css" href="css/style.css" />
  <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
  <script type="text/javascript" src="js/jquery.message.js"></script>
  <script type="text/javascript" src="js/admin.js"></script>
<?php echo $timeline->getColorClassesHTML(false); ?>
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
<?php echo Admin::getEvents(); ?>
			</div>
		</div>
		<div class="bordered" id="eventDetails"></div>
	</div>
	<div class="config">
		<h2>Konfiguration</h2>
		<div class="bordered">
			<span id="databaseUpdate" class="button">Datenbank Update</a>
		</div>
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

<?php Log::output(); ?>
</body>
</html>
