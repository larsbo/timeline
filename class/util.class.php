<?php

class Util {

	static function ArrayToSelect($array, $name, $key = null, $selectedElement = null) {
		$html = "<select name=\"".$name."\" id=\"".$name."\">";
		foreach ($array as $element)
			if (($key===null?$element:$element[$key]) == $selectedElement)
				$html .= "<option selected=\"selected\">".($key===null?$element:$element[$key])."</option>\n";
			else
				$html .= "<option>".($key===null?$element:$element[$key])."</option>\n";
		$html .= "</select>";
		return $html;
	}

}
?>
