<?php

require_once('db.class.php');
require_once('event.class.php');
require_once('colorclasses.class.php');

class Timeline {
	private $events;
	private $start_year = null;
	private $end_year = null;
	private $colorclasses;	//this is only cache

	function __Construct() {
		foreach (Events::getEvents() as $event) {
			$this->events[] = array('event' => $event, 'line' => 0);
			if ($this->start_year === null || $event->getStartYear() < $this->start_year)
				$this->start_year = $event->getStartYear();
			if ($this->end_year === null || $event->getEndYear() > $this->end_year)
				$this->end_year = $event->getEndYear();
		}
		$this->colorclasses = ColorClasses::getColorClasses(true);
		$this->start_year -= 1;
		$this->end_year += 2;
	}
	
	function getStartYear() {
		return $this->start_year;
	}
	
	function getEndYear() {
		return $this->end_year;
	}
	
	function getColorClasses() {
		return $this->colorclasses;
	}

	function alignEvents($filtered = false) {
		if($filtered) {
			$matrix = array();
			foreach ($this->events as &$e) {
				$x = $e['event']->getColorclass();
				$e['line'] = 0;
				$year = $e['event']->getStartYear();
				// search for the first free row
				while ($matrix[$x][$year][$e['line']]) {
					$e['line']++;
				}
		//		Log::debug("marking everything used on line: ".$e['line']." from year: ".$year." until ".$e['event']->getEndYear());
				// found free row -> mark columns (=years) of this row in matrix
				for ($j = $year; $j <= $e['event']->getEndYear(); $j++) {
					$matrix[$x][$j][$e['line']] = true;
				}
			}
		}
		else {
			$matrix = array();
			foreach ($this->events as &$e) {
				$e['line'] = 0;
				$year = $e['event']->getStartYear();
				// search for the first free row
				while ($matrix[$year][$e['line']]) {
					$e['line']++;
				}
		//		Log::debug("marking everything used on line: ".$e['line']." from year: ".$year." until ".$e['event']->getEndYear());
				// found free row -> mark columns (=years) of this row in matrix
				for ($j = $year; $j <= $e['event']->getEndYear(); $j++) {
					$matrix[$j][$e['line']] = true;
				}
			}
		}
	}


	function getSimpleEventsOutput() {
		$c = Config::getInstance();

		$this->alignEvents();

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

	private function getMaxLine($filterBy = null) {
		$max = 0;
		foreach ($this->events as $e)
			if (!$filterBy || $e['event']->getColorclass() == $filterBy)
				$max = $e['line']>$max ? $e['line'] : $max;
		return $max;
	}

	function getOrderedEventsOutput() {
		$c = Config::getInstance();
		$this->alignEvents(true);

		$html = <<<EOD
\t<table id="timeline" class="bordered">
\t\t<thead>
\t\t\t<tr>\n
EOD;
		for ($year = $this->start_year; $year < $this->end_year; $year++) {
			$html .= "\t\t\t\t<th class=\"date\" style=\"width: ".$c->tl_column_width."px\">".$year."</th>\n";
		}
		$html .= "\t\t\t</tr>\n\t\t</thead>\n\t\t<tbody>\n";
		foreach ($this->colorclasses->getArray() as $a) {
			$colorclass = $a['color_id'];
			$max = $this->getMaxLine($colorclass) + 1;
			Log::debug('maxline for '.$colorclass.' is '.$max);
			$max *= $c->tl_event_padding_y;
			$html .= "\t\t\t<tr id='content' style='height: ".$max."px;'>\n";
			for ($year = $this->start_year; $year < $this->end_year; $year++) {
				$html .= "\t\t\t\t<td>\n";
				foreach ($this->events as $e) {
					if ($e['event']->getStartYear() == $year) {
						if ($colorclass == $e['event']->getColorclass())
							$html .= $e['event']->toTimelineRepresentation($e['line']);
					}
				}
				$html .= "\t\t\t\t</td>\n";
			}
			$html .= "\t\t\t</tr>\n";
		}
		$html .= "\t\t</tbody>\n\t</table>";
		return $html;
	}
}
?>
