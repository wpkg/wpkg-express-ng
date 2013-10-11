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
<?php if (!$packageActions || count($packageActions) == 0): ?>
	&lt;None&gt;
<?php else: ?>
	<table cellpadding="0" cellspacing="0">
		<tr>
			<th width="75">Type</th>
			<th>Command</th>
			<th width="105">Actions</th>
		</tr>
	<?php foreach($packageActions as $packageAction): ?>
		<tr>
			<td>
				<?php echo ucwords(constValToLCSingle('action_type', $packageAction['PackageAction']['type'])); ?>
			</td>
			<td><?php echo $html->link($packageAction['PackageAction']['command'], array('action'=>'view', 'action', $packageAction['PackageAction']['id'])) ?></td>
			<td>
				<?php
					$out = $html->image('go-top.png', array('url' => array('action' => 'movetop', 'action', $packageAction['PackageAction']['id'])));
					$out .= " " . $html->image('go-up.png', array('url' => array('action' => 'moveup', 'action', $packageAction['PackageAction']['id'])));
					$out .= " " . $html->image('go-down.png', array('url' => array('action' => 'movedown', 'action', $packageAction['PackageAction']['id'])));
					$out .= " " . $html->image('go-bottom.png', array('url' => array('action' => 'movebottom', 'action', $packageAction['PackageAction']['id'])));
					$out .= " " . $html->image('pencil.png', array('url' => array('action' => 'edit', 'action', $packageAction['PackageAction']['id'])));
					$out .= " " . $html->image('delete.png', array('url' => array('action' => 'delete', 'action', $packageAction['PackageAction']['id'])));
					echo $out;
				?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>