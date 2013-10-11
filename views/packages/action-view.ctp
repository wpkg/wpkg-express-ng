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
$javascript->link('jquery.js', false);
$js = ' 
		function updateExitCodes() {
			$("#exitcodes a[href*=\"delete\"]").click(function() {
				$("#exitcodes").load(this.href, function() {updateExitCodes();});
				return false;
			});
		}
		$(document).ready(function(){
			updateExitCodes();
	    });
';
$javascript->codeBlock($js, array('allowCache' => false, 'safe' => false, 'inline' => false));
?>
<style type="text/css">label {width: 80px;}</style>
<h2>Package Action Details for '<?php echo $html->link($packageAction['Package']['name'], array('controller'=>'packages', 'action'=>'view', $packageAction['Package']['id'])); ?>' - [ <?php echo $html->image('pencil.png', array('alt' => 'Edit', 'url' => array('action' => 'edit', 'action', $packageAction['PackageAction']['id']))) . "&nbsp;" . $html->link($html->image('delete.png'), array('action'=>'delete', 'action', $packageAction['PackageAction']['id']), array('alt' => 'Delete'), "Are you sure you wish to delete this package action and all associated exit codes?", false); ?> ]</h2><hr class="hbar" />
<div class="inputwrap"><label title="<?php echo TOOLTIP_PACKAGEACTION_TYPE; ?>">Type:</label><?php 
	switch ($packageAction['PackageAction']['type']) {
		case ACTION_TYPE_INSTALL: echo 'Install'; break;
		case ACTION_TYPE_UPGRADE: echo 'Upgrade'; break;
		case ACTION_TYPE_DOWNGRADE: echo 'Downgrade'; break;
		case ACTION_TYPE_REMOVE: echo 'Remove'; break;
		default: echo 'Unknown';
	}
?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PACKAGEACTION_COMMAND; ?>">Command:</label><?php echo $packageAction['PackageAction']['command'] ?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PACKAGEACTION_TIMEOUT; ?>">Timeout:</label><?php echo $packageAction['PackageAction']['timeout'] ?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PACKAGEACTION_WORKDIR; ?>">Work Dir.:</label><?php echo $packageAction['PackageAction']['workdir'] ?></div>

<h2>Exit Codes - [ <?php echo $html->image('add.png', array('alt' => 'Add', 'url' => array('action' => 'add', 'exitcode', $packageAction['PackageAction']['id']))) ?> ]</h2><hr class="hbar" />
<div id="exitcodes" class="clear">
	<?php echo $this->element('exitcodes', array('exitcodes' => $packageAction['ExitCode'])); ?>
</div>