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
<?php if (!$profiles || count($profiles) == 0): ?>
	&lt;None&gt;
<?php else: ?>
	<ul>
		<?php
			foreach ($profiles as $profile)
				echo "<li>" . $html->link($profile['id_text'], array('controller' => 'profiles', 'action'=>'view', $profile['id'])) . " [ " . $html->link($html->image('delete.png'), array('action' => "delete", $host['Host']['id'], $profile['id']), array('title' => 'Delete this association'), null, false, false) . " ]</li>";
		?>
	</ul>
<?php endif; ?>