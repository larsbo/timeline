<?php

require_once('db.class.php');
require_once('event.class.php');
require_once('colorclasses.class.php');

class Timeline {
	private $events;
	private $start_year;
	private $end_year;
	private $colorclasses;	//this is only cache

	function __Construct($start, $end) {
		$this->start_year = $start;
		$this->end_year = $end;
		foreach (Events::getEvents($start, $end) as $event)
			$this->events[] = array('event' => $event, 'line' => 0);
		$this->alignEvents();
		$this->colorclasses = ColorClasses::getColorClasses(true);
	}
	
	function getColorClassesHTML() {
		return $this->colorclasses->toStyleDefinition();
	}

	function alignEvents() {
		$matrix = array();
		foreach ($this->events as &$e) {
			$year = $e['event']->getStartYear();
			// search for the first free row
			while ($matrix[$year][$e['line']]) {
				$e['line']++;
			}
			Log::debug("marking everything used on line: ".$e['line']." from year: ".$year." until ".$e['event']->getEndYear());
			// found free row -> mark columns (=years) of this row in matrix
			for ($j = $year; $j <= $e['event']->getEndYear(); $j++) {
				$matrix[$j][$e['line']] = true;
			}
		}
	}


	function getEventsOutput() {
		$c = Config::getInstance();

		$html = <<<EOD
\t<table id="timeline" class="bordered">
\t\t<thead>
\t\t\t<tr>\n
EOD;
		for ($year = $this->start_year; $year < $this->end_year; $year++) {
			$html .= "\t\t\t\t<th class=\"date\" style=\"width: ".$c->tl_column_width."px\">".$year."</th>\n";
		}
		$html .= "\t\t\t</tr>\n\t\t</thead>\n\t\t<tbody>\n\t\t\t<tr id='content'>\n";
		for ($year = $this->start_year; $year < $this->end_year; $year++) {
			$html .= "\t\t\t\t<td>\n";
			foreach ($this->events as $e) {
				if ($e['event']->getStartYear() == $year) {
					$html .= $e['event']->toTimelineRepresentation($e['line']);
				}
			}
			$html .= "\t\t\t\t</td>\n";
		}
		$html .= "\t\t\t</tr>\n\t\t</tbody>\n\t</table>";
		return $html;
	}
}
?>
