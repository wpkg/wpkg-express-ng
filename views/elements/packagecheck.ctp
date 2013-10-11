<?php
/*
 * wpkgExpress : A web-based frontend to wpkg
 * Copyright 2009 Brian White
 *
 * This file is part of wpkgExpress.
 *
 * wpkgExpress is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * wpkgExpress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with wpkgExpress.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
?>
<?php
include_once('../wpkg_constants.php');
extract($data);
if (!isset($PackageCheck))
	return;

/*if (some logic) {
     $tree->addItemAttribute('class', 'highlight'); // highlight this li
} else {
     $tree->addTypeAttribute('style', 'display', 'none'); // hide this ul completely
}*/

$out = "";
switch ($PackageCheck['type']) {
	case CHECK_TYPE_LOGICAL:
		$out .= ucwords(constValToLCSingle('check_condition_', $PackageCheck['condition'], " "));
		break;
	case CHECK_TYPE_REGISTRY:
		$out .= "Registry Path \"" . $PackageCheck['path'] . "\" ";
		switch ($PackageCheck['condition']) {
			case CHECK_CONDITION_REGISTRY_EXISTS: $out .= "Exists"; break;
			case CHECK_CONDITION_REGISTRY_EQUALS: $out .= "Equals \"" . $PackageCheck['value'] . "\""; break;
			default: $out .= "Unknown";
		}
		break;
	case CHECK_TYPE_UNINSTALL:
		$out .= "Uninstall Exists For \"" . $PackageCheck['path'] . "\"";
		break;
	case CHECK_TYPE_EXECUTE:
		$out .= "Execute \"" . $PackageCheck['path'] . "\" and ensure the returned exit code ";
		switch ($PackageCheck['condition']) {
			case CHECK_CONDITION_EXECUTE_EXIT_CODE_SMALLER_THAN: $out .= "< " . $PackageCheck['value']; break;
			case CHECK_CONDITION_EXECUTE_EXIT_CODE_LESS_THAN_OR_EQUAL_TO: $out .= "&le; " . $PackageCheck['value']; break;
			case CHECK_CONDITION_EXECUTE_EXIT_CODE_EQUAL_TO: $out .= "= " . $PackageCheck['value']; break;
			case CHECK_CONDITION_EXECUTE_EXIT_CODE_GREATER_THAN: $out .= "> " . $PackageCheck['value']; break;
			case CHECK_CONDITION_EXECUTE_EXIT_CODE_GREATER_THAN_OR_EQUAL_TO: $out .= "&ge; " . $PackageCheck['value']; break;
			default: $out .= "Unknown";
		}
		break;
	case CHECK_TYPE_FILE:
		$out .= "File \"" . $PackageCheck['path'] . "\" ";
		switch ($PackageCheck['condition']) {
			case CHECK_CONDITION_FILE_EXISTS: $out .= "Exists"; break;
			case CHECK_CONDITION_FILE_SIZE_EQUALS: $out .= "Has a File Size = " . $number->toReadableSize((float)$PackageCheck['value']);
												   $tree->addItemAttribute('title', $number->format($PackageCheck['value']) . " Bytes");
												   break;
			case CHECK_CONDITION_FILE_VERSION_SMALLER_THAN: $out .= "Has a Version < " . $PackageCheck['value']; break;
			case CHECK_CONDITION_FILE_VERSION_LESS_THAN_OR_EQUAL_TO: $out .= "Has a Version &le; " . $PackageCheck['value']; break;
			case CHECK_CONDITION_FILE_VERSION_EQUAL_TO: $out .= "Has a Version = " . $PackageCheck['value']; break;
			case CHECK_CONDITION_FILE_VERSION_GREATER_THAN: $out .= "Has a Version > " . $PackageCheck['value']; break;
			case CHECK_CONDITION_FILE_VERSION_GREATER_THAN_OR_EQUAL_TO: $out .= "Has a Version &ge; " . $PackageCheck['value']; break;
			default: $out .= "Unknown";
		}
}

$out .= " [ " . $html->link($html->image('go-top.png'), array('action' => 'movetop', 'check', $PackageCheck['id']), null, false, false);
$out .= " " . $html->link($html->image('go-up.png'), array('action' => 'moveup', 'check', $PackageCheck['id']), null, false, false);
$out .= " " . $html->link($html->image('go-down.png'), array('action' => 'movedown', 'check', $PackageCheck['id']), null, false, false);
$out .= " " . $html->link($html->image('go-bottom.png'), array('action' => 'movebottom', 'check', $PackageCheck['id']), null, false, false);

$out .= " " . $html->link($html->image('pencil.png'), array('action' => 'edit', 'check', $PackageCheck['id']), null, false, false);
$out .= " " . $html->link($html->image('delete.png'), array('action' => 'delete', 'check', $PackageCheck['id']), null, false, false) . " ]<br />";
echo $out;
?>