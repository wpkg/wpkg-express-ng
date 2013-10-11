<?php
class XmlppHelper extends AppHelper {

	function indent($xml, $html_output = false) {
		//$xml_obj = new SimpleXMLElement($xml);
		$level = 4;
		$indent = 0; // current indentation level
		$pretty = array();
		
		// get an array containing each XML element
		$xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml)); //$xml_obj->asXML()));

		// shift off opening XML tag if present
		if (count($xml) && preg_match('/^<\?\s*xml/', $xml[0]))
		  $pretty[] = array_shift($xml);

		foreach ($xml as $el) {
		  if (preg_match('/^<([\w])+.*?>$/U', $el) && substr($el, -2) != "/>") {
			  // opening tag, increase indent
			  $pretty[] = str_repeat(' ', $indent) . $el;
			  $indent += $level;
		  } else {
			if (preg_match('/^<\/.+>$/', $el))
			  $indent -= $level;  // closing tag, decrease indent
			if ($indent < 0)
			  $indent += $level;
			$pretty[] = str_repeat(' ', $indent) . $el;
		  }
		}   
		$xml = implode("\n", $pretty);   
		return ($html_output) ? htmlentities($xml) : $xml;
	}
}
?>