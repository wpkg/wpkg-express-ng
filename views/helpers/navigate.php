<?php
class NavigateHelper extends Helper {
	var $helpers = array('Html'); 
 
/**
 * Highlight a menu option based on path
 *
 * A menu path gets passed and it compares to requestd path and sets the call to be highlighted.
 * Use regular ereg expressions in the pattern matching.
 *
 * @param path for wich the nav item should be highlighted
 * @param optional normal class to be returned, default navLink
 * @param optional highlight class to be returnd, default navLinkSelected
 * @return returns the proper class based on the url
 */		
	function highlight($path, $normal = 'navLink', $selected = 'navLinkSelected') {
		$class = $normal;
		$currentPath = substr($this->Html->here, strlen($this->Html->base));
		// if there is a star in the path we need to do different checking
		$regs = array();
		if (eregi($path,$currentPath,$regs)){
			$class .= " " . $selected;
		}
		return $class;
    }
}
?>