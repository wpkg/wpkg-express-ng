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
<?php if (!$variables || count($variables) == 0): ?>
	&lt;None&gt;
<?php else: ?>
	<ul>
		<?php
			foreach ($variables as $var) {
				if (isset($var['Variable']))
					$var = $var['Variable'];
				echo "<li>" . $var['name'] . "<span class=\"variable-equals\"> = </span>" . $var['value'] . " [ " . $html->image('pencil.png', array('url' => array('action' => 'edit', $var['id']))) . " " . $html->image('delete.png', array('url' => array('action' => 'delete', $var['id']))) . " ]";
			}
		?>
	</ul>
<?php endif; ?>