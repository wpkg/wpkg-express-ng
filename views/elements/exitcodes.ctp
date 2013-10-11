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
<?php if (!$exitcodes || count($exitcodes) == 0): ?>
	&lt;None&gt;
<?php else: ?>
	<ul>
		<?php
			foreach ($exitcodes as $exitcode) {
				if (isset($exitcode['ExitCode']))
					$exitcode = $exitcode['ExitCode'];
					
				switch ($exitcode['reboot']) {
					case EXITCODE_REBOOT_TRUE: $reboot = "Yes"; break;
					case EXITCODE_REBOOT_DELAYED: $reboot = "Delayed"; break;
					case EXITCODE_REBOOT_POSTPONED: $reboot = "Postponed"; break;
					case EXITCODE_REBOOT_FALSE: $reboot = "None"; break;
					default: $reboot = "Unknown";
				}
				echo "<li>" . $exitcode['code'] . " (Reboot: $reboot) [ " . $html->link($html->image('pencil.png'), array('action' => 'edit', 'exitcode', $exitcode['id']), null, false, false) . " " . $html->link($html->image('delete.png'), array('action'=>'delete', 'exitcode', $exitcode['id']), array('escape' => false), false) . " ]</li>";
			}
		?>
	</ul>
<?php endif; ?>