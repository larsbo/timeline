<?php
require_once 'db.class.php';
require_once 'colorclasses.class.php';
require_once 'event.class.php';

class Admin {
	private $user;

	static function login($name, $pass) {
		$result = DB::queryAssocAtom("SELECT name FROM `users` WHERE `name` = '".$name."' AND `pass` = '".md5($pass)."' LIMIT 1;");

		if ($result) {
			Admin::setSession('admin', $result['name']);
			return true;
		} else {
			return false;
		}
	}

	static function logout() {
		Admin::unsetSession('admin');
	}

	static function loggedIn() {
		return isset($_SESSION['admin']);
	}

	private static function setSession($name, $value) {
		$_SESSION[$name] = $value;
	}

	private static function unsetSession($name) {
		unset($_SESSION[$name]);
	}


	static function getEvents() {
		$events = Events::getEvents($start, $end);

		$output = "<ul>\n";
		foreach ($events as $event) {
			$output .= "<li class=\"eventContainer\" data-id=\"".$event->getId()."\">
										<span title=\"anzeigen\" class=\"event colorclass_".$event->getColorclass()."\">".$event->getTitle()."</span>
										<span title=\"bearbeiten\" class=\"button edit\"></span>
										<span title=\"l&ouml;schen\" class=\"button delete\"></span>
									</li>";
		}
		$output .= "</ul>";
		return $output;
	}

	static function showEvent($id) {
		$event = DB::queryAssocAtom("SELECT * FROM `events` WHERE `event_id` = '".$id."';");
		return "<p><b>Titel:</b> ".$event['title']."</p>
						<p><b>Start:</b> ".$event['startdate']."</p>
						<p><b>Ende:</b> ".$event['enddate']."</p>
						<p><b>Kategorie:</b> ".$event['colorclass']."</p>
						<p>".$event['details']."</p>";
	}

	static function getInsertEventForm() {
		$html = "<form data-action=\"save\">
							<label for=\"title\">Titel:</label>
							<input type=\"text\" name=\"title\" id=\"title\" size=\"62\" />
							<label for=\"start\">Start:</label>
							<input type=\"text\" name=\"start\" class=\"dateentry\" id=\"start\" size=\"10\" />
							<label for=\"end\">Ende:</label>
							<input type=\"text\" name=\"end\" class=\"dateentry\" id=\"end\" size=\"10\" />
							<label for=\"colorclass\">Kategorie:</label>";
		$html .= ColorClasses::getColorClasses(false)->toSelectField();
		$html .= "<textarea name=\"details\" rows=\"10\" cols=\"50\"></textarea>
							<input type=\"submit\" value=\"Speichern\" />
						</form>";
		return $html;
	}

	static function saveEvent() {
		$title = mysql_real_escape_string($_GET['title']);
		$start = mysql_real_escape_string($_GET['start']);
		$end = mysql_real_escape_string($_GET['end']);
		$details = mysql_real_escape_string($_GET['details']);
		$colorclass = mysql_real_escape_string($_GET['colorclass']);
		$sql = "INSERT INTO `events` (`title`, `startdate`, `enddate`, `details`, `colorclass`) 
							VALUES ('".$title."', '".$start."', '".$end."', '".$details."', '".$colorclass."');";
		if (DB::execute($sql))
			return "Ereignis erfolgreich eingetragen!";
		else
			return "Ereignis konnte nicht gespeichert werden!";
	}

	static function editEvent($id) {
		$event = DB::queryAssocAtom("SELECT * FROM `events` WHERE `event_id` = '".$id."'");
		$html = "<form data-action=\"update\">
							<input type=\"hidden\" name=\"id\" value=\"".$event['event_id']."\" />
							<label for=\"title\">Titel:</label>
							<input type=\"text\" name=\"title\" id=\"title\" value=\"".$event['title']."\" size=\"62\" />
							<label for=\"start\">Start:</label>
							<input type=\"text\" name=\"start\" class=\"dateentry\" id=\"start\" value=\"".$event['startdate']."\" size=\"10\" />
							<label for=\"end\">Ende:</label>
							<input type=\"text\" name=\"end\" class=\"dateentry\" id=\"end\" value=\"".$event['enddate']."\" size=\"10\" />
							<label for=\"colorclass\">Kategorie:</label>";
		$html .= ColorClasses::getColorClasses(false)->toSelectField($event['colorclass']);
		$html .= "<textarea name=\"details\" rows=\"10\" cols=\"50\">".$event['details']."</textarea>
							<input type=\"submit\" value=\"Speichern\" />
						</form>";
		return $html;
	}

	static function updateEvent($id) {
		$title = mysql_real_escape_string(trim($_GET['title']));
		$start = mysql_real_escape_string($_GET['start']);
		$end = mysql_real_escape_string($_GET['end']);
		$details = mysql_real_escape_string(trim($_GET['details']));
		$colorclass = mysql_real_escape_string(trim($_GET['colorclass']));
		$result = DB::execute("UPDATE `events` 
															SET `title` = '".$title."', 
																	`startdate` = '".$start."',
																	`enddate` = '".$end."',
																	`details` = '".$details."', 
																	`colorclass` = '".$colorclass."' 
														WHERE `event_id` = '".$id."';");
		if ($result)
			return "Ereignis ".$id." erfolgreich bearbeitet!";
		else
			return "Ereignis ".$id." konnte nicht bearbeitet werden!";
	}

	static function deleteEventConfirmation($id) {
		$result = DB::execute("DELETE FROM `events` WHERE `event_id` = '".$id."' LIMIT 1;");
		if ($result)
			return "Ereignis ".$id." erfolgreich gel&ouml;scht!";
		else
			return "Ereignis ".$id." konnte nicht gel&ouml;scht werden!";
	}

/************ DATABASE FUNCTIONS ****************/

	static function checkAndUpdateTable($insertData = false) {
		$a = Admin::checkAndUpdateTableColorClasses($insertData);
		$b = Admin::checkAndUpdateTableEvents($insertData);
		$c = Admin::checkAndUpdateTableUsers();
		return ($a && $b && $c);
	}

	static function checkAndUpdateTableColorClasses($insertData) {
		$tablediff = DB::checkForTable('colorclasses', array('color_id', 'css_code'));

		//the table is in some wrong state .... need to update or create
		if ($tablediff === null) {
			//table is missing
			Log::debug("table '<em>colorclasses</em>' doesn't exist -> creating table ...");
			$sql = <<<EOD
CREATE TABLE IF NOT EXISTS `colorclasses` (
  `color_id` VARCHAR(10) NOT NULL,
  `css_code` TEXT NOT NULL,
  PRIMARY KEY (`color_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOD;
			$result = DB::execute($sql);

			if ($result && $insertData) {
				Admin::insertTestData('colorclasses');
			}
			return $result;
		}
		else if (sizeof($tablediff) > 0) {

			// all fields in $tablediff are missing...
			Log::debug("table '<em>colorclasses</em>' is missing these fields: ".implode(", ", $tablediff));

			$sql = "";
			if (in_array('css_code', $tablediff))
				$sql .= "ALTER TABLE `colorclasses` ADD `css_code` TEXT NOT NULL;";

			return DB::execute($sql);
		}
		return true;
	}

	static function checkAndUpdateTableEvents($insertData) {
		$tablediff = DB::checkForTable('events', array('colorclass', 'details', 'enddate', 'event_id', 'startdate', 'title'));

		//the table is in some wrong state .... need to update or create
		if ($tablediff === null) {
			//table is missing
			Log::debug("table '<em>events</em>' doesn't exist -> creating table ...");
			$sql = <<<EOD
CREATE TABLE IF NOT EXISTS `events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(80) NOT NULL,
  `details` text NOT NULL,
  `startdate` DATE NOT NULL,
  `enddate` DATE NOT NULL,
  `colorclass` varchar(10) NOT NULL,
  PRIMARY KEY (`event_id`),
  INDEX `startdate` (`startdate`),
  INDEX `enddate` (`enddate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOD;
			$result = DB::execute($sql);

			if ($result && $insertData) {
				Admin::insertTestData('events');
			}
			return $result;
		}
		else if (sizeof($tablediff) > 0) {
			//all fields in $tablediff are missing...
			Log::debug("table '<em>events</em>' is missing these fields: ".implode(", ", $tablediff));

			$sql = "";
			if (in_array('colorclass', $tablediff))
				$sql .= "ALTER TABLE `events` ADD `colorclass` varchar(10) NOT NULL;";
			if (in_array('details', $tablediff))
				$sql .= "ALTER TABLE `events` ADD `details` text NOT NULL;";
			if (in_array('title', $tablediff))
				$sql .= "ALTER TABLE `events` ADD `title` varchar(30) NOT NULL;";
			if (in_array('startdate', $tablediff))
				$sql .= "ALTER TABLE `events` ADD `startdate` DATE NOT NULL , ADD INDEX ( `startdate` );";
			if (in_array('enddate', $tablediff))
				$sql .= "ALTER TABLE `events` ADD `enddate` DATE NOT NULL , ADD INDEX ( `enddate` );";

			return DB::execute($sql);
		}
		return true;
	}

	static function checkAndUpdateTableUsers() {
		$tablediff = DB::checkForTable('users', array('id', 'name', 'pass', 'email'));

		//the table is in some wrong state .... need to update or create
		if ($tablediff === null) {
			//table is missing
			Log::debug("table '<em>users</em>' doesn't exist -> creating table ...");
			$sql = <<<EOD
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `pass` varchar(32) NOT NULL,
  `email` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOD;
			$result = DB::execute($sql);

			return $result;
		}
		else if (sizeof($tablediff) > 0) {
			//all fields in $tablediff are missing...
			Log::debug("table '<em>users</em>' is missing these fields: ".implode(", ", $tablediff));

			$sql = "";
			if (in_array('email', $tablediff))
				$sql .= "ALTER TABLE `users` ADD `email` varchar(64) NOT NULL;";
			if (in_array('name', $tablediff))
				$sql .= "ALTER TABLE `users` ADD `pass` varchar(32) NOT NULL;";
			if (in_array('title', $tablediff))
				$sql .= "ALTER TABLE `users` ADD `name` varchar(32) NOT NULL;";

			return DB::execute($sql);
		}
		return true;
	}

	static function dropTables() {
		$sql = "DROP TABLE `colorclasses`, `events`;";
		if (DB::execute($sql)) {
			Log::debug("insert data into '<em>".$table."</em>' successful!");
			return true;
		} else {
			Log::error("insert data into '<em>".$table."</em>' failed!");
			return false;
		}
	}

	static function insertTestData($table) {
		switch ($table) {
			case 'colorclasses':
			$sql = <<<EOD
INSERT INTO `colorclasses` (`color_id`, `css_code`) VALUES
('Politik', 'background-image: linear-gradient(top, #ee0000, #aa0000);
  background-image: -o-linear-gradient(top, #ee0000, #aa0000);
  background-image: -ms-linear-gradient(top, #ee0000, #aa0000);
  background-image: -moz-linear-gradient(top, #ee0000, #aa0000);
  background-image: -webkit-linear-gradient(top, #ee0000, #aa0000);
  color: #fff;
  text-shadow: 0 1px 0 #000;'),
('Gesellschaft', 'background-image: linear-gradient(top, #eeee00, #aaaa00);
  background-image: -o-linear-gradient(top, #eeee00, #aaaa00);
  background-image: -ms-linear-gradient(top, #eeee00, #aaaa00);
  background-image: -moz-linear-gradient(top, #eeee00, #aaaa00);
  background-image: -webkit-linear-gradient(top, #eeee00, #aaaa00);
  color: #fff;
  text-shadow: 0 1px 0 #000;'),
('Wissenschaft', 'background-image: linear-gradient(top, #00ee00, #00aa00);
  background-image: -o-linear-gradient(top, #00ee00, #00aa00);
  background-image: -ms-linear-gradient(top, #00ee00, #00aa00);
  background-image: -moz-linear-gradient(top, #00ee00, #00aa00);
  background-image: -webkit-linear-gradient(top, #00ee00, #00aa00);
  color: #fff;
  text-shadow: 0 1px 0 #000;'),
('Religion', 'background-image: linear-gradient(top, #0066ee, #0033aa);
  background-image: -o-linear-gradient(top, #0066ee, #0033aa);
  background-image: -ms-linear-gradient(top, #0066ee, #0033aa);
  background-image: -moz-linear-gradient(top, #0066ee, #0033aa);
  background-image: -webkit-linear-gradient(top, #0066ee, #0033aa);
  color: #fff;
  text-shadow: 0 1px 0 #000;');
EOD;
			break;

			case 'events':
			$sql = <<<EOD
INSERT INTO `events` (`title`, `startdate`, `enddate`, `details`, `colorclass`) VALUES
('noch ein langes Ereignis &uuml;ber mehrere Jahre', '1940-03-01', '1944-03-01', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Loremclita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'Religion'),
('Test-Event', '1952-03-01', '0000-00-00', 'dieses event hat kein enddatum', 'Politik'),
('aufregend', '1942-00-00', '1942-00-00', 'Dieses Event hört im gleichen jahr auf, wie es anfängt. es hat zudem keine moats angabe, bzw. tages angabe', 'Wissenschaft'),
('ein event und so', '1938-03-01', '1943-03-01', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'Religion'),
('das kanns nicht sein', '1940-03-01', '1941-03-01', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'Religion'),
('wer das liest ist doof', '1936-03-01', '1941-03-01', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'Politik'),
('party', '1940-03-01', '1940-03-01', 'Dieses Event ist nur einen Tag lang', 'Gesellschaft'),
('partylong', '1941-03-00', '1940-03-00', 'Dieses Event ist nur einen Monat lang', 'Gesellschaft'),
('wo ist der bus', '1941-03-01', '1943-03-01', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'Religion'),
('tralalala', '1936-03-01', '1939-03-01', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'Wissenschaft');
EOD;
			break;
		}

		if (DB::execute($sql)) {
			Log::debug("insert data into '<em>".$table."</em>' successful!");
			return true;
		} else {
			Log::error("insert data into '<em>".$table."</em>' failed!");
			return false;
		}
	}

}
?>
