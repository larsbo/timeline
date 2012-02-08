<?php

require_once('db.class.php');
require_once('event.class.php');

class Timeline {
	private $events;
	private $start_year;
	private $end_year;
	private $colorclasses = null;	//this is only cache

	function __Construct($start, $end) {
		$this->start_year = $start;
		$this->end_year = $end;
		foreach (Events::getEvents($start, $end) as $event)
			$this->events[] = array('event' => $event, 'line' => 0);
		$this->alignEvents();
	}

	function getColorClassesHTML($activeOnly = true) {
		if ($this->colorclasses == null)	//simple cache
			$this->colorclasses = Timeline::getColorClasses($activeOnly);
		
		$html = '<style type="text/css">';
		foreach ($this->colorclasses as $colorclass) {
			if(!empty($colorclass['color_id']) && !empty($colorclass['css'])) {
				$html .= ".colorclass_".$colorclass['color_id']." { \n";
				$html .= $colorclass['css']." }\n";
			}
		}
		$html .= '</style>';
		return $html;
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

	/********** FACTORY **********/
	static function getColorClasses($activeOnly = true) {
		$sql = "SELECT DISTINCT c.color_id, c.css_code AS css FROM `colorclasses` AS c";
		if ($activeOnly)
			$sql .= " RIGHT JOIN events AS e ON e.colorclass = c.color_id;";
		else
			$sql .= ";";
		return DB::queryAssoc($sql);
	}
}
?>
