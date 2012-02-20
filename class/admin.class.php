<?php
require_once 'db.class.php';
require_once 'colorclasses.class.php';
require_once 'event.class.php';
require_once 'util.class.php';

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
		return Event::getEventFromId($id)->toAdminRepresentation();
	}

	static function getInsertEventForm() {
		return Event::getForm();
	}

	static function saveEvent($data) {
		//-1 indicates, that there is no id yet ... so it will get inserted, when calling save
		$e = new Event(-1, $data['title'], $data['details'], $data['start'], $data['end'], $data['colorclass'], "", $data['type'], $data['image']);
		if ($e->save())
			return "Ereignis erfolgreich eingetragen!";
		else
			return "Ereignis konnte nicht gespeichert werden!";
	}

	static function editEvent($id) {
		$event = Event::getEventFromId($id);
		return Event::getForm($event);
	}

	static function updateEvent($id, $data) {
		$e = new Event($id, $data['title'], $data['details'], $data['start'], $data['end'], $data['colorclass'], "", $data['type'], $data['image']);
		if ($e->save())
			return "Ereignis ".$id." erfolgreich bearbeitet!";
		else
			return "Ereignis ".$id." konnte nicht bearbeitet werden!";
	}

	static function deleteEventConfirmation($id) {
		$e = Event::getEventFromId($id);
		if ($e && $e->delete())
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
		$tablediff = DB::checkForTable('colorclasses', array('color_id', 'css_code', 'description'));

		//the table is in some wrong state .... need to update or create
		if ($tablediff === null) {
			//table is missing
			Log::debug("table '<em>colorclasses</em>' doesn't exist -> creating table ...");
			$sql = <<<EOD
CREATE TABLE IF NOT EXISTS `colorclasses` (
  `color_id` VARCHAR(14) NOT NULL,
  `css_code` TEXT NOT NULL,
  `description` VARCHAR(100) NOT NULL,
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
			if (in_array('description', $tablediff))
				$sql .= "ALTER TABLE `colorclasses` ADD `description` VARCHAR(100) NOT NULL;";

			return DB::execute($sql);
		}
		return true;
	}

	static function checkAndUpdateTableEvents($insertData) {
		$tablediff = DB::checkForTable('events', array('colorclass', 'details', 'enddate', 'event_id', 'startdate', 'title', 'type', 'image'));

		//the table is in some wrong state .... need to update or create
		if ($tablediff === null) {
			//table is missing
			Log::debug("table '<em>events</em>' doesn't exist -> creating table ...");
			$sql = <<<EOD
CREATE TABLE IF NOT EXISTS `events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(30) NOT NULL,
  `details` text NOT NULL,
  `startdate` DATE NOT NULL,
  `enddate` DATE NOT NULL,
  `colorclass` varchar(14) NOT NULL,
  `type` VARCHAR(10) NOT NULL,
  `image` VARCHAR(100) NOT NULL,
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
				$sql .= "ALTER TABLE `events` ADD `colorclass` varchar(14) NOT NULL;";
			if (in_array('details', $tablediff))
				$sql .= "ALTER TABLE `events` ADD `details` text NOT NULL;";
			if (in_array('title', $tablediff))
				$sql .= "ALTER TABLE `events` ADD `title` varchar(30) NOT NULL;";
			if (in_array('startdate', $tablediff))
				$sql .= "ALTER TABLE `events` ADD `startdate` DATE NOT NULL , ADD INDEX ( `startdate` );";
			if (in_array('enddate', $tablediff))
				$sql .= "ALTER TABLE `events` ADD `enddate` DATE NOT NULL , ADD INDEX ( `enddate` );";
			if (in_array('type', $tablediff))
				$sql .= "ALTER TABLE `events` ADD `type` varchar(10) NOT NULL;";
			if (in_array('image', $tablediff))
				$sql .= "ALTER TABLE `events` ADD `image` varchar(100) NOT NULL;";

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
INSERT INTO `colorclasses` (`color_id`, `css_code`, `description`) VALUES
('Politik', 'background-image: linear-gradient(top, #ee0000, #aa0000);
  background-image: -o-linear-gradient(top, #ee0000, #aa0000);
  background-image: -ms-linear-gradient(top, #ee0000, #aa0000);
  background-image: -moz-linear-gradient(top, #ee0000, #aa0000);
  background-image: -webkit-linear-gradient(top, #ee0000, #aa0000);
  color: #fff;
  text-shadow: 0 1px 0 #000;','Politik'),
('Gesellschaft', 'background-image: linear-gradient(top, #eeee00, #aaaa00);
  background-image: -o-linear-gradient(top, #eeee00, #aaaa00);
  background-image: -ms-linear-gradient(top, #eeee00, #aaaa00);
  background-image: -moz-linear-gradient(top, #eeee00, #aaaa00);
  background-image: -webkit-linear-gradient(top, #eeee00, #aaaa00);
  color: #fff;
  text-shadow: 0 1px 0 #000;','Gesellschaft'),
('Wissenschaft', 'background-image: linear-gradient(top, #00ee00, #00aa00);
  background-image: -o-linear-gradient(top, #00ee00, #00aa00);
  background-image: -ms-linear-gradient(top, #00ee00, #00aa00);
  background-image: -moz-linear-gradient(top, #00ee00, #00aa00);
  background-image: -webkit-linear-gradient(top, #00ee00, #00aa00);
  color: #fff;
  text-shadow: 0 1px 0 #000;','Wissenschaft'),
('Religion', 'background-image: linear-gradient(top, #0066ee, #0033aa);
  background-image: -o-linear-gradient(top, #0066ee, #0033aa);
  background-image: -ms-linear-gradient(top, #0066ee, #0033aa);
  background-image: -moz-linear-gradient(top, #0066ee, #0033aa);
  background-image: -webkit-linear-gradient(top, #0066ee, #0033aa);
  color: #fff;
  text-shadow: 0 1px 0 #000;','Religion');
EOD;
			break;

			case 'events':
			$sql = <<<EOD
INSERT INTO `events` (`title`, `startdate`, `enddate`, `details`, `colorclass`,  `type`,  `image`) VALUES
('noch ein langes Ereignis &uuml;ber mehrere Jahre', '1940-03-01', '1944-03-01', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Loremclita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'Religion', 'text', 'bild'),
('Test-Event', '1952-03-01', '0000-00-00', 'dieses event hat kein enddatum', 'Politik', 'image', 'test.jpg'),
('aufregend', '1942-00-00', '1942-00-00', 'Dieses Event hört im gleichen jahr auf, wie es anfängt. es hat zudem keine moats angabe, bzw. tages angabe', 'Wissenschaft', 'quote', ''),
('ein event und so', '1938-03-01', '1943-03-01', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'Religion', 'quote', ''),
('das kanns nicht sein', '1940-03-01', '1941-03-01', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'Religion', 'text', 'bild'),
('wer das liest ist doof', '1936-03-01', '1941-03-01', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'Politik', 'text', 'bild'),
('party', '1940-03-01', '1940-03-01', 'Dieses Event ist nur einen Tag lang', 'Gesellschaft', 'image', 'test.jpg'),
('partylong', '1941-03-00', '1940-03-00', 'Dieses Event ist nur einen Monat lang', 'Gesellschaft', 'quote', ''),
('wo ist der bus', '1941-03-01', '1943-03-01', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'Religion', 'text', ''),
('tralalala', '1936-03-01', '1939-03-01', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'Wissenschaft', 'quote', 'bild');
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
