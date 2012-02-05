<?php

require_once('db.class.php');

class Timeline {
	private $events;
	private $matrix;
	private $start_year;
	private $end_year;
	private $events_output;
	private $details_output;
	private $css_output;

	function __Construct($start, $end) {
		$this->start_year = $start;
		$this->end_year = $end;
		$this->events = $this->getEvents();
		$this->alignEvents();

		$this->css_output = $this->getColorclasses();
		$this->createEventsOutput();
		$this->createDetailsOutput();
	}

	static function checkAndUpdateTable($insertData) {
		$a = Timeline::checkAndUpdateTableColorClasses($insertData);
		$b = Timeline::checkAndUpdateTableEvents($insertData);
		return ($a && $b);
	}

	static function checkAndUpdateTableColorClasses($insertData) {
		$tablediff = DB::checkForTable('colorclasses', 
			array('color_id', 'css_code'));
			//this has to be sorted ...
		
		//the table is in some wrong state .... need to update or create
		if ($tablediff === null) {
			//table is missing?
			Log::debug("we have to create the table");
			$sql = <<<EOD
CREATE TABLE IF NOT EXISTS `colorclasses` (
  `color_id` VARCHAR(10) NOT NULL,
  `css_code` TEXT NOT NULL,
  PRIMARY KEY (`color_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOD;
			$result = DB::execute($sql);		// produce Warning: mysql_fetch_assoc(): supplied argument is not a valid MySQL result resource
			if ($result && $insertData) {
				Timeline::insertColorClassTestData();
			}
			return $result;
		}
		else if (sizeof($tablediff) > 0) {
			//all fields in $tablediff are missing...
			Log::debug("the table is missing these fields: ".implode(",", $tablediff));
			$sql = "";
			if (in_array('css_code', $tablediff))
				$sql .= "ALTER TABLE `colorclasses` ADD `css_code` TEXT NOT NULL;";

			return DB::execute($sql);
		}
		else
			return true;
	}

	static function checkAndUpdateTableEvents($insertData) {
		$tablediff = DB::checkForTable('events', 
			array('colorclass', 'details', 'end_year', 'event_id', 'start_year', 'title'));
			//this has to be sorted ...
		
		//the table is in some wrong state .... need to update or create
		if ($tablediff === null) {
			//table is missing?
			Log::debug("we have to create the table");
			$sql = <<<EOD
CREATE TABLE IF NOT EXISTS `events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(80) NOT NULL,
  `details` text NOT NULL,
  `start_year` int(4) NOT NULL,
  `end_year` int(4) NOT NULL,
  `colorclass` varchar(10) NOT NULL,
  PRIMARY KEY (`event_id`),
  KEY `start_year` (`start_year`),
  KEY `end_year` (`end_year`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOD;
			$result = DB::execute($sql);
			if ($result && $insertData) {
				Timeline::insertEventTestData();
			}
			return $result;
		}
		else if (sizeof($tablediff) > 0) {
			//all fields in $tablediff are missing...
			Log::debug("the table is missing these fields: ".implode(",", $tablediff));
			$sql = "";
			if (in_array('colorclass', $tablediff))
				$sql .= "ALTER TABLE `events` ADD `colorclass` varchar(10) NOT NULL;";
			if (in_array('details', $tablediff))
				$sql .= "ALTER TABLE `events` ADD `details` text NOT NULL;";
			if (in_array('title', $tablediff))
				$sql .= "ALTER TABLE `events` ADD `title` varchar(30) NOT NULL;";

			return DB::execute($sql);
		}
		else
			return true;
	}
	
	static function insertColorClassTestData() {
		$sql = <<<EOD
INSERT INTO `colorclasses` (`color_id`, `css_code`) VALUES
('red', 'background-image: linear-gradient(top, #ee0000, #aa0000);
  background-image: -o-linear-gradient(top, #ee0000, #aa0000);
  background-image: -ms-linear-gradient(top, #ee0000, #aa0000);
  background-image: -moz-linear-gradient(top, #ee0000, #aa0000);
  background-image: -webkit-linear-gradient(top, #ee0000, #aa0000);
  color: #fff;
  text-shadow: 0 1px 0 #000;'),
('yellow', 'background-image: linear-gradient(top, #eeee00, #aaaa00);
  background-image: -o-linear-gradient(top, #eeee00, #aaaa00);
  background-image: -ms-linear-gradient(top, #eeee00, #aaaa00);
  background-image: -moz-linear-gradient(top, #eeee00, #aaaa00);
  background-image: -webkit-linear-gradient(top, #eeee00, #aaaa00);
  color: #fff;
  text-shadow: 0 1px 0 #000;'),
('green', 'background-image: linear-gradient(top, #00ee00, #00aa00);
  background-image: -o-linear-gradient(top, #00ee00, #00aa00);
  background-image: -ms-linear-gradient(top, #00ee00, #00aa00);
  background-image: -moz-linear-gradient(top, #00ee00, #00aa00);
  background-image: -webkit-linear-gradient(top, #00ee00, #00aa00);
  color: #fff;
  text-shadow: 0 1px 0 #000;'),
('blue', 'background-image: linear-gradient(top, #0000ee, #0000aa);
  background-image: -o-linear-gradient(top, #0000ee, #0000aa);
  background-image: -ms-linear-gradient(top, #0000ee, #0000aa);
  background-image: -moz-linear-gradient(top, #0000ee, #0000aa);
  background-image: -webkit-linear-gradient(top, #0000ee, #0000aa);
  color: #fff;
  text-shadow: 0 1px 0 #000;');
EOD;
		Log::debug("got: '".implode(",", DB::execute($sql))."'");
	}

	static function insertEventTestData() {
		$sql = <<<EOD
INSERT INTO `events` (`title`, `start_year`, `end_year`, `details`, `colorclass`) VALUES
('noch ein langes Ereignis &uuml;ber mehrere Jahre', 1940, 1944, 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Loremclita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'red'),
('Test-Event', 1952, 0, '', 'blue'),
('aufregend', 1942, 1942, 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'green'),
('ein event und so', 1938, 1943, 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'yellow'),
('das kanns nicht sein', 1940, 1941, 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'red'),
('wer das liest ist doof', 1936, 1941, 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'green'),
('party', 1940, 1940, 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Loremclita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'blue'),
('wo ist der bus', 1941, 1943, 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'red'),
('tralalala', 1936, 1939, 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.', 'yellow');
EOD;
		Log::debug("got: '".implode(",", DB::execute($sql))."'");
	}

	function getEvents() {
		$sql = <<<EOD
SELECT e.event_id, e.title, e.details, e.start_year, e.end_year, e.colorclass
FROM events AS e;
EOD;
		$events = DB::queryAssoc($sql);
		// sort events by start_year
		usort($events, array($this, 'custom_sort'));
		return $events;
	}
	
	function getColorClasses() {
		$sql = <<<EOD
SELECT DISTINCT c.color_id, c.css_code AS css FROM `colorclasses` AS c
RIGHT JOIN events AS e ON e.colorclass = c.color_id;
EOD;

		$htmlcode = '<style type="text/css">';
		foreach (DB::queryAssoc($sql) as $colorclass) {
			if(!empty($colorclass['color_id']) && !empty($colorclass['css'])) {
				$htmlcode .= ".".$colorclass['color_id']." { \n";
				$htmlcode .= $colorclass['css']." }\n";
			}
		}
		$htmlcode .= '</style>';
		return $htmlcode;
	}


	function alignEvents() {
		$counter = sizeof($this->events);
		$this->matrix = array();
		for ($i=0; $i<$counter; $i++) {
			$year = $this->events[$i]['start_year'];
			$line = 0;
			// search for the first free row
			while ($this->matrix[$year][$line]) {
				$this->events[$i]['line']++;
				$line++;
			}
			// found free row -> mark columns (=years) of this row in matrix
			for ($j = $year; $j <= $this->events[$i]['end_year']; $j++) {
				$this->matrix[$j][$line] = true;
			}
		}
	}


	function createEventsOutput() {
		$c = Config::getInstance();

		$this->events_output = <<<EOD
\t<table id="timeline" class="bordered">
\t\t<thead>
\t\t\t<tr>\n
EOD;
		for ($year = $this->start_year; $year < $this->end_year; $year++) {
			$this->events_output .= "\t\t\t\t<th class=\"date\" style=\"width: ".$c->tl_column_width."px\">".$year."</th>\n";
		}
		$this->events_output .= <<<EOD
\t\t\t</tr>
\t\t</thead>
\t\t<tbody>
\t\t\t<tr id="content">\n
EOD;
		for ($year = $this->start_year; $year < $this->end_year; $year++) {
			$this->events_output .= "\t\t\t\t<td>\n";
			foreach ($this->events as $event) {
				if ($event['start_year'] == $year) {
					$event['length'] = max(1, $event['end_year'] - $event['start_year'] + 1) * $c->tl_column_width - $c->tl_event_padding_x;
					$event['line'] = $event['line'] * $c->tl_event_padding_y;
					$this->events_output .= "\t\t\t\t\t<span class=\"event ".$event['colorclass']."\" ".
						"style=\"width:".$event['length']."px;".
							"top:".$event['line']."px\" ".
						"data-event=\"".$event['event_id']."\" ".
						"data-title=\"".$event['title']."\" ".
						"data-width=\"".$event['length']."\"".
						">".$event['title']."<span class=\"pin\"></span></span>\n";
				}
			}
			$this->events_output .= "\t\t\t\t</td>\n";
		}
		$this->events_output .= <<<EOD
\t\t\t</tr>
\t\t</tbody>
\t</table>\n
EOD;
	}


	function createDetailsOutput() {
		foreach ($this->events as $event) {
			$this->details_output .= "\t<div id=\"event-".$event['event_id']."\" class=\"event-details\">".$event['details']."</div>\n";
		}
	}


	function output($data) {
		if ($data == 'events') echo $this->events_output;
		if ($data == 'details') echo $this->details_output;
		if ($data == 'css') echo $this->css_output;
	}


	function custom_sort($a, $b) {
		return $a['start_year'] > $b['start_year'];
	}
}
?>
