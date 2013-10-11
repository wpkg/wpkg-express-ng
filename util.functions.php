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
function constsToWords($prefix, $skipWords = false) {
	$constants = array();
	foreach (get_defined_constants() as $k => $v) {
		if (strpos($k, stristr($k, $prefix)) === 0) {
			$keylower = strtolower(substr($k, strlen($prefix)));
			if ($skipWords !== false)
				$keylower = implode("_", array_slice(explode("_", $keylower), $skipWords+1));
			if (substr($keylower, 0, 1) == "_")
				$keylower = substr($keylower, 1);
			$constants[constant($k)] = ucwords(str_replace("_", " ", $keylower));
		}
	}
	return $constants;
}

function constsVals($prefix) {
	$constants = array();
	foreach (get_defined_constants() as $k => $v) {
		if (strpos($k, stristr($k, $prefix)) === 0)
			$constants[] = constant($k);
	}
	return $constants;
}

function constValToLCSingle($prefix, $val, $keepUnderscore = false, $skipWords = false, $useUnknown = true) {
	$name = ($useUnknown ? "Unknown" : null);
	foreach (get_defined_constants() as $k => $v) {
		if (strpos($k, stristr($k, $prefix)) === 0 && $v == $val) {
			$k = strtolower(substr($k, strlen($prefix)));
			if ($skipWords !== false)
				$k = implode("_", array_slice(explode("_", $k), $skipWords+1));
			$name = $k;
			if (substr($name, 0, 1) == "_")
				$name = substr($name, 1);
			if ($keepUnderscore !== true) {
				$replace = ($keepUnderscore === false ? "" : $keepUnderscore);
				$name = str_replace("_", $replace, $name);
			}
			break;
		}
	}
	return $name;
}
?>