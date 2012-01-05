<?php
function custom_sort($a, $b) {
	return $a['start'] > $b['start'];
}

function createEvents() {
	$events = array();
	for ($i=0; $i < 100; $i++) {
		$size = rand(1, 9);
		$start = rand (1, 20);
		$end = $start + $size + 1;
		$events[] = array(
			'start' =>$start, 
			'end' => $end, 
			'size' => $size);
	}
	// sort by start
	usort($events, 'custom_sort');
	return $events;
}

function alignEvents(&$events, &$matrix) {
	$events_count = sizeof($events);

	for ($i=0; $i<$events_count; $i++) {
		$year = $events[$i]['start'];
		$line = 0;
		// suche freie zeile
		while ($matrix[$year][$line]) {
			$events[$i]['line']++;
			$line++;
		}
		// freie zeile gefunden -> matrix belegen
		for ($j = $year; $j <= $events[$i]['end']; $j++) {
			// laenge des events
			$matrix[$j][$line] = true;
		}
	}
}

function printEvents($events) {
	$x = 0;
	foreach ($events as $event) {
		echo "<div style=\"width:".($event['size']*20).";border:1px solid #000;height:20px;position:absolute;left:".($event['start']*20).";opacity:0.2;top:".($event['line']*21)."\">".$x."</div>";
		$x++;
	}
}

function printMatrix($matrix) {
	$lines = sizeof($matrix);
	echo "<pre style=\"position:absolute;left:1000px;top:0\">";
	for ($line = 0; $line<$lines; $line++) {
		for ($year = 1; $year <=20; $year++) {
			if ($matrix[$year][$line]) {
				echo $matrix[$year][$line]." ";
			} else {
				echo "0 ";
			}
		}
		echo "<br>";
	}
	echo "</pre>";
}

function debug($data, $left) {
	echo "<pre style=\"position:absolute;left:".$left."px;top:0\">";
	print_r($data);
	echo "</pre>";
}


$events = createEvents();
$eventMatrix = array();

alignEvents($events, $eventMatrix);

printEvents($events);
printMatrix($eventMatrix);
//debug($alignedEvents, 500);
//debug($events, 1000);
?>