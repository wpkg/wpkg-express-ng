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
if (!isset($dbconf))
	exit();

function boolString($bValue = false) {
	return ($bValue ? 'true' : 'false');
}

echo "<?php" . PHP_EOL;
?>
class DATABASE_CONFIG {

	var $default = array(
<?php
foreach ($dbconf as $k => $v)
	echo "		'$k' => " . (is_bool($v) ? boolString($v) : "'$v'") . "," . PHP_EOL;
?>
	);

}
<?php echo "?>" ?>