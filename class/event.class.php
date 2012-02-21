<?php

require_once 'config.class.php';
require_once 'db.class.php';

class Event {

	private $event_id;
	private $title;
	private $details;
	private $startdate;
	private $enddate;
	private $colorclass;
	private $colordescription;
	private $type;
	private $image;
	private $source;
	
	private $width = -1;	//cache for event representations width
	private $offset = -1;	//cache for event representations offset

	function __Construct($event_id, $title, $details, $startdate, $enddate, $colorclass, $colordescription, $type, $image, $source) {
		$this->event_id = $event_id;
		$this->title = $title;
		$this->details = $details;
		if (strlen($startdate) <= 4)
			$startdate .= "-00-00";//real dirty
		if (strlen($enddate) <= 4)
			$enddate .= "-00-00";
		$this->startdate = $startdate;
		$this->enddate = $enddate;
		$this->colorclass = $colorclass;
		$this->colordescription = $colordescription;
		$this->type = $type;
		$this->image = $image;
		$this->source = $source;
	}
	
	/** getter **/
	function getId() {
		return $this->event_id;
	}
	function getColorclass() {
		return $this->colorclass;
	}
	function getColorDescription() {
		return $this->colordescription;
	}
	function getTitle() {
		return $this->title;
	}
	function getType() {
		return $this->type;
	}
	function getImage() {
		if (!empty($this->image) && substr($this->image,0,4)=='http')
			return $this->image;
		if (!empty($this->image) && file_exists("data/".$this->image))
			return "data/".$this->image;
		else
			return false;
	}
	function getSource() {
		$output = "";
		$links = explode("\n", $this->source);
		foreach ($links as $link) {
			if (substr($link, 0, 1) == '-') {
				// link found
				$link = substr($link, 2);
				$output .= "<a href=\"".$link."\" class=\"extern\">".$link."</a>\n";
			}
		}
		return $output;
	}

	function getStartYear() {
		return intval(substr($this->startdate, 0, 4));
	}
	function getEndYear() {
		$startyear = $this->getStartYear();
		$endyear = intval(substr($this->enddate, 0, 4));
		if ($endyear < $startyear) //this can happen, if the enddate is not properly entered 0000-... or if lies before its startingdate
			return $startyear;
		else
			return $endyear;
	}
	
	function getPixelWidth() {
		if ($this->width == -1) {
			$c = Config::getInstance();
			$minwidth = $c->tl_column_width - $c->tl_event_padding_x; //minwidth is one year...

			if ($this->getStartYear() == $this->getEndYear()) {
				$this->width = $minwidth;	//we have minimal width
				return $minwidth;
			}
			
			/*$datetime1 = new DateTime($this->startdate);
			$datetime2 = new DateTime($this->enddate);
			$interval = $datetime1->diff($datetime2);
			$daysdiff = max(1,intval($interval->format('%a')));*/ //it seems i dont have php 5.3 :(
			
			$diff = abs(strtotime($this->enddate) - strtotime($this->startdate));
			$daysdiff = floor($diff/ (60*60*24));
			Log::debug("#days: ".$daysdiff." for event ".$this->title." [".$this->event_id."]");
			
			$pixelsPerYear = $c->tl_column_width;
			$pixelsPerDay = $pixelsPerYear / 365.0; //we have 365 days
			
			$pixelDays = round($pixelsPerDay * $daysdiff);
			$this->width = max($minwidth, $pixelDays);
		}
		return $this->width;
	}

	function getPixelOffset() {
		if ($this->offset == -1) {
			$c = Config::getInstance();
			if ($this->getStartYear() == $this->getEndYear() || substr($this->startdate, 5, 2) == '00')
				return 0;	//if we have minimal width or no proper date => offset = 0

			//how many months offset?
			$months = intval(substr($this->startdate, 5, 2));
			//how many days in this month?
			$days = intval(substr($this->startdate, 8, 2));
		
			$pixelsPerYear = $c->tl_column_width;
			$pixelsPerMonth = $pixelsPerYear / 12.0; //we have 12 months
			$pixels = round($months * $pixelsPerMonth + $days * $pixelsPerMonth / 30.0);
			$this->offset = $pixels;
		}
		return $this->offset;
	}
	
	static function getTypes() {
		return array('text', 'quote', 'image');
	}
	
	private function escape() {
		$this->title = DB::escape($this->title);
		$this->details = DB::escape($this->details);
		$this->startdate = DB::escape($this->startdate);
		$this->enddate = DB::escape($this->enddate);
		$this->colorclass = DB::escape($this->colorclass);
		$this->type = DB::escape($this->type);
		$this->image = DB::escape($this->image);
		$this->source = DB::escape($this->source);
	}
	
	function save() {
		$this->escape();
		
		//if we have no id, insert, otherwise do update...
		if ($this->event_id == null || $this->event_id == -1) {
			$sql = <<<EOD
INSERT INTO `events` (`title`, `startdate`, `enddate`, `details`, `colorclass`, `type`, `image`, `source`) 
VALUES ('{$this->title}', '{$this->startdate}', '{$this->enddate}', '{$this->details}', '{$this->colorclass}', '{$this->type}', '{$this->image}', '{$this->source}');
EOD;
		}
		else {
			$sql = <<<EOD
UPDATE `events` 
SET `title` = '{$this->title}', 
	`startdate` = '{$this->startdate}',
	`enddate` = '{$this->enddate}',
	`details` = '{$this->details}', 
	`colorclass` = '{$this->colorclass}', 
	`type` = '{$this->type}', 
	`image` = '{$this->image}', 
	`source` = '{$this->source}' 
WHERE `event_id` = '{$this->event_id}';
EOD;
		}

		return DB::execute($sql);

	}
	
	function delete() {
		return DB::execute("DELETE FROM `events` WHERE `event_id` = '".$this->event_id."' LIMIT 1;");
	}
	
	/** representations **/
	function toTimelineRepresentation($line) {
		$c = Config::getInstance();
	
		$length = $this->getPixelWidth();
		$line = $line * $c->tl_event_padding_y;
		$colorclass = $this->colorclass != "" ? " custom colorclass_".$this->colorclass : "";
		$offset = $this->getPixelOffset();
		$source = $this->getSource() != "" ? "<div class=\"source\">".$this->getSource()."</div>" : "";
		switch ($this->type) {
		case 'quote':
			$text = $this->getQuoteRepresentation();
			break;
		case 'image':
			$text = $this->getImageRepresentation();
			break;
		case 'text':
			default:
			$text = $this->getTextRepresentation();
			break;
		}
		$html = <<<EOD
<div class="event-preview" style="zIndex: 0">
	<span 
		id="event{$this->event_id}"
		class="event{$colorclass}" 
		style="width:{$length}px;top:{$line}px;left:{$offset}px;z-index:2;" 
		data-event="{$this->event_id}" 
		data-title="{$this->title}" 
		data-offset="{$offset}" 
		data-width="{$length}"
		data-colorclass="{$this->colorclass}"
	>{$this->title}
		<span class="pin"></span>
	</span>
	<div class="event-details" style="zIndex: 1">
		<div class="time">{$this->getStartYear()}</div>
		{$text}
		{$source}
	</div>
</div>
EOD;
		return $html;
	}

	private function getTextRepresentation() {
		$image = $this->resizeImage($this->getImage(), 150, $this->title, 'class="img"');

		return $image.$this->details;
	}

	private function getQuoteRepresentation() {
		return <<<EOD
<blockquote>{$this->details}</blockquote>
EOD;
	}

	private function getImageRepresentation() {
		$image = $this->resizeImage($this->getImage(), 150, 'alt="'.$this->title.'" title="'.$this->title.'"');
		if ($this->details) {
			return <<<EOD
	<div class="big-img">{$image}</div>
	<div class="img-text">{$this->details}</div>
EOD;
		} else {
			return;
		}
	}

	private function resizeImage($image, $max, $title, $extra = "") {
		if ($image && file_exists($image)) {
			list($width, $height) = getimagesize($image);
			if ($width > 2*$max) {
				return "<a title=\"".$title."\" href=\"".$image."\"><img src=\"".$image."\" width=\"".$max."\" ".$extra."/></a>";
			} elseif ($height > $max) {
				return "<a title=\"".$title."\" href=\"".$image."\"><img src=\"".$image."\" height=\"".$max."\" ".$extra."/></a>";
			} else {
				return "<a title=\"".$title."\" href=\"".$image."\"><img src=\"".$timage."\" ".$extra."/></a>";
			}
		} else {
			return;
		}
	}

	function toAdminRepresentation() {
		return <<<EOD
<p><b>Titel:</b> {$this->title}</p>
<p><b>Start:</b> {$this->startdate}</p>
<p><b>Ende:</b> {$this->enddate}</p>
<p><b>Kategorie:</b> {$this->colordescription}</p>
<p><b>Typ:</b> {$this->type}</p>
<p><b>Bild:</b> {$this->image}</p>
<p>{$this->details}</p>
<p><b>Quelle:</b> {$this->source}</p>
EOD;
	}


	static function getForm($e = null) {
		$colorclassesSelectField = ColorClasses::getColorClasses(false)->toSelectField($e===null?null:$e->colorclass);
		$typeSelectField = Util::ArrayToSelect(Event::getTypes(), 'type', null, $e===null?null:$e->type);
		$title = $e===null?"":$e->title;
		$startdate = $e===null?"":$e->startdate;
		$enddate = $e===null?"":$e->enddate;
		$image = $e===null?"":$e->image;
		$source = $e===null?"":$e->source;
		$details = $e===null?"":$e->details;
		$action = $e===null?"save":"update";
		$eventIdInput = $e===null?"":"<input type=\"hidden\" name=\"id\" value=\"".$e->event_id."\" />";
		$html = <<<EOD
<form data-action="$action">$eventIdInput
<p class="validateTips"></p>
<table class="adminForm">
	<tr>
		<td width="50"><label for="title">Titel:</label></td>
		<td><input type="text" name="title" id="title" maxlength="30" value="{$title}" /></td>
	</tr>
	<tr>
		<td><label for="start">Start:</label></td>
		<td>
			<input type="text" name="start" class="dateentry" id="start" maxlength="10" value="{$startdate}" />
			<label for="end">Ende:</label>
			<input type="text" name="end" class="dateentry" id="end" maxlength="10" value="{$enddate}" />
		</td>
	</tr>
	<tr>
		<td><label for="colorclass">Kategorie:</label></td>
		<td>{$colorclassesSelectField}</td>
	</tr>
	<tr>
		<td><label for="type">Typ:</label></td>
		<td>{$typeSelectField}</td>
	</tr>
	<tr>
		<td><label for="image">Bild:</label></td>
		<td><input type="text" name="image" id="image" maxlength="100" value="{$image}" /></td>
	</tr>
	<tr>
		<td colspan="2"><textarea name="details" id="details" rows="10" cols="50">{$details}</textarea></td>
	</tr>
	<tr>
		<td><label for="source">Quelle:</label></td>
		<td><textarea name="source" id="source">{$source}</textarea></td>
	</tr>
</table>
</form>
EOD;
		return $html;
	}

	/** FACTORY **/
	static function getEventFromId($id) {
		$sql = <<<EOD
SELECT 
  e.event_id, e.title, e.details, e.colorclass, DATE(e.startdate) AS startdate, DATE(e.enddate) AS enddate, e.type, e.image, e.source, c.description AS colordescription 
FROM `events` AS e 
LEFT JOIN `colorclasses` AS c ON c.color_id = e.colorclass
WHERE `event_id` = '$id' 
LIMIT 1;
EOD;
		$r = DB::queryAssocAtom($sql);
		return new Event($r['event_id'], $r['title'], $r['details'], 
				$r['startdate'], $r['enddate'], $r['colorclass'], $r['colordescription'], $r['type'], $r['image'], $r['source']);
	}

}

class Events {
	static function getEvents() {
		$sql = <<<EOD
SELECT 
  e.event_id, e.title, e.details, e.colorclass, DATE(e.startdate) AS startdate, DATE(e.enddate) AS enddate, e.type, e.image, e.source, c.description AS colordescription 
FROM `events` AS e
LEFT JOIN `colorclasses` AS c ON c.color_id = e.colorclass
EOD;
		$sql .= " ORDER BY e.startdate ASC";
		$events = Array();
		foreach (DB::queryAssoc($sql) as $r)
			$events[] = new Event($r['event_id'], $r['title'], $r['details'], 
					$r['startdate'], $r['enddate'], $r['colorclass'], $r['colordescription'], $r['type'], $r['image'], $r['source']);
		return $events;
	}
}

?>
