<?php

require_once('db.class.php');

class Timeline {
	private $events;
	private $start_year;
	private $end_year;
	private $colorclasses = null;	//this is only cache

	function __Construct($start, $end) {
		$this->start_year = $start;
		$this->end_year = $end;
		$this->events = Timeline::getEvents($start, $end);

		$this->alignEvents();
	}

	function getColorClassesHTML($activeOnly = true) {
		if ($this->colorclasses == null)	//simple cache
			$this->colorclasses = Timeline::getColorClasses($activeOnly);
		
		$html = '<style type="text/css">';
		foreach (Timeline::getColorClasses() as $colorclass) {
			if(!empty($colorclass['color_id']) && !empty($colorclass['css'])) {
				$html .= ".colorclass_".$colorclass['color_id']." { \n";
				$html .= $colorclass['css']." }\n";
			}
		}
		$html .= '</style>';
		return $html;
	}

	function alignEvents() {
		$counter = sizeof($this->events);
		$matrix = array();
		for ($i=0; $i<$counter; $i++) {
			$year = $this->events[$i]['start_year'];
			$line = 0;
			// search for the first free row
			while ($matrix[$year][$line]) {
				$this->events[$i]['line']++;
				$line++;
			}
			// found free row -> mark columns (=years) of this row in matrix
			for ($j = $year; $j <= $this->events[$i]['end_year']; $j++) {
				$matrix[$j][$line] = true;
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
			foreach ($this->events as $event) {
				if ($event['start_year'] == $year) {
					$event['length'] = max(1, $event['end_year'] - $event['start_year'] + 1) * $c->tl_column_width - $c->tl_event_padding_x;
					$event['line'] = $event['line'] * $c->tl_event_padding_y;
					$html .= <<<EOD
\t\t\t\t\t<span 
\t\t\t\t\t	class="event colorclass_{$event['colorclass']}" 
\t\t\t\t\t	style="width:{$event['length']}px;top:{$event['line']}px"
\t\t\t\t\t	data-event="{$event['event_id']}"
\t\t\t\t\t	data-title="{$event['title']}"
\t\t\t\t\t	data-width="{$event['length']}"
\t\t\t\t\t>{$event['title']}
\t\t\t\t\t<span class=pin\"></span></span>
EOD;
				}
			}
			$html .= "\t\t\t\t</td>\n";
		}
		$html .= "\t\t\t</tr>\n\t\t</tbody>\n\t</table>";
		return $html;
	}

	function getEventDetailsOutput() {
		$html = "";
		foreach ($this->events as $event) {
			$html .= "\t<div id=\"event-".$event['event_id']."\" class=\"event-details\">".$event['details']."</div>\n";
		}
		return $html;
	}

	/********** FACTORY **********/
	static function getEvents($start = 0, $end = 0) {
		$sql = <<<EOD
SELECT e.event_id, e.title, e.details, e.start_year, e.end_year, e.colorclass
FROM events AS e
EOD;
		if ($start != 0 && $end != 0)
			$sql .= " WHERE e.start_year >= $start AND e.end_year <= $end";
		else if ($start != 0)
			$sql .= " WHERE e.start_year >= $start";
		else if ($end != 0)
			$sql .= " WHERE e.end_year <= $end";
		$sql .= " ORDER BY e.start_year ASC";
		$events = DB::queryAssoc($sql);
		// sort events by start_year
		return $events;
	}
	
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
