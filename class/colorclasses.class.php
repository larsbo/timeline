<?php

require_once 'db.class.php';

class ColorClasses {
	private $colorclasses;
	
	function __Construct($sqlResult) {
		$this->colorclasses = $sqlResult;
	}
	
	function toStyleDefinition() {
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
	
	function toSelectField($selectedElement = null) {
		$html = "<select name=\"colorclass\" id=\"colorclass\">";
		foreach ($this->colorclasses as $colorclass)
			if ($colorclass['color_id'] == $selectedElement)
				$html .= "<option selected=\"selected\">".$colorclass['color_id']."</option>\n";
			else
				$html .= "<option>".$colorclass['color_id']."</option>\n";
		$html .= "</select>";
		return $html;
	}

	static function getColorClasses($activeOnly = true) {
		$sql = "SELECT DISTINCT c.color_id, c.css_code AS css FROM `colorclasses` AS c";
		if ($activeOnly)
			$sql .= " RIGHT JOIN events AS e ON e.colorclass = c.color_id;";
		else
			$sql .= ";";
		return new ColorClasses(DB::queryAssoc($sql));
	}
}

?>
