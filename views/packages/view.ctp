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
$javascript->link('pretty.js', false);
$js = ' 
		var interval;
		function updateChecks() {
			$("#checks a[href*=\"move\"]").click(function() {
				$("#checks").load(this.href, function() {updateChecks();});
				return false;
			});
			$("#checks a[href*=\"delete\"]").click(function() {
				if ($(this).parent().clone().children().remove().end().text().indexOf("Logical") == 0) {
					if (!confirm("Are you sure you wish to delete this package check?\nAny children of this logical check will also be removed."))
						return false;
				}
				$("#checks").load(this.href, function() {updateChecks();});
				return false;
			});
		}
		function updateActions() {
			$("#actions a[href*=\"move\"]").click(function() {
				$("#actions").load(this.href, function() {updateActions();});
				return false;
			});
			$("#actions a[href*=\"delete\"]").click(function() {
				$("#actions").load(this.href, function() {updateActions();});
				return false;
			});
		}
		function prettyDates() {
			$(".date").each(function(){ this.title = this.innerHTML; }).prettyDate();
			clearInterval(interval);
			interval = setInterval(function(){ $(".date").prettyDate(); }, 5000);
		}
		$(document).ready(function(){
			prettyDates();
			updateChecks();
			updateActions();
	    });
';
$javascript->codeBlock($js, array('allowCache' => false, 'safe' => false, 'inline' => false));

$execute = ucwords(constValToLCSingle("package_execute", $package['Package']['execute']));
$reboot = ucwords(constValToLCSingle("package_reboot", $package['Package']['reboot']));
$notify = ucwords(constValToLCSingle("package_notify", $package['Package']['notify']));
?>
<style type="text/css">label {width: 114px;}</style>
<h2>Package Details for '<?php echo $package['Package']['name']; ?>' - [ <?php echo $html->image('pencil.png', array('alt' => 'Edit', 'url' => array('action' => 'edit', $package['Package']['id']))) . "&nbsp;" . $html->link($html->image('delete.png'), array('action'=>'delete', $package['Package']['id']), array('alt' => 'Delete'), sprintf("Are you sure you want to delete Package '%s'?", addcslashes($package['Package']['name'], '"')), false) . "&nbsp;" . $html->image('var.png', array('alt' => 'Variables', 'url' => array('controller' => 'variables', 'action' => 'view', 'package', $package['Package']['id']))) ?> ]</h2><hr class="hbar" />
<div class="inputwrap"><label title="<?php echo TOOLTIP_PACKAGE_ENABLED; ?>">Enabled:</label><?php echo $package['Package']['enabled'] == 0 ? "No" : "Yes" ?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PACKAGE_NAME; ?>">Name:</label><?php echo $package['Package']['name'] ?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PACKAGE_ID; ?>">ID:</label><?php echo $package['Package']['id_text'] ?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PACKAGE_REVISION; ?>">Revision:</label><?php echo $package['Package']['revision'] ?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PACKAGE_PRIORITY; ?>">Priority:</label><?php echo $package['Package']['priority'] ?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PACKAGE_REBOOT; ?>">Reboot:</label><?php echo $reboot ?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PACKAGE_EXECUTE; ?>">Execute:</label><?php echo $execute ?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PACKAGE_NOTIFY; ?>">Notify:</label><?php echo $notify ?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PACKAGE_NOTES; ?>">Notes:</label><?php echo (!empty($package['Package']['notes']) ? "<div class=\"notes\">" . nl2br(Sanitize::html($package['Package']['notes'])) . "</div>" : "&lt;None&gt;") ?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PACKAGE_LASTMODIFIED; ?>">Last Modified:</label><div class="date"><?php echo date("Y-m-d h:i:s A", strtotime($package['Package']['modified'])) ?></div></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PACKAGE_DEPENDSON; ?>">Depends On:</label>
	<?php
		if (count($package['PackageDependency']) == 0)
			echo "&lt;None&gt;";
		else {
			echo "<ul>";
			foreach ($package['PackageDependency'] as $packageDepends)
				echo "<li>" . $html->link($packageDepends['name'] . " (" . $packageDepends['id_text'] . ")", array('action'=>'view', $packageDepends['id']));
			echo "</ul>";
		}
	?>
</div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PACKAGE_DEPENDEDONBY; ?>">Depended On By:</label>
	<?php
		if (count($package['DependedOnBy']) == 0)
			echo "&lt;None&gt;";
		else {
			echo "<ul>";
			foreach ($package['DependedOnBy'] as $packageDepends)
				echo "<li>" . $html->link($packageDepends['name'] . " (" . $packageDepends['id_text'] . ")", array('action'=>'view', $packageDepends['id']));
			echo "</ul>";
		}
	?>
</div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PACKAGE_PROFILES; ?>">Exists in Profiles:</label>
	<?php
		if (count($package['Profile']) == 0)
			echo "&lt;None&gt;";
		else {
			echo "<ul>";
			foreach ($package['Profile'] as $packageProfile)
				echo "<li>" . $html->link($packageProfile['id_text'], array('controller'=>'profiles', 'action'=>'view', $packageProfile['id']));
			echo "</ul>";
		}
	?>
</div>

<h2>Package Checks - [ <?php echo $html->image('add.png', array('alt' => 'Add', 'url' => array('action' => 'add', 'check', $package['Package']['id']))) ?> ]</h2><hr class="hbar" />
<div id="checks" class="clear">
	<?php echo $this->element('packagechecks', array('packageChecks' => $packageChecks)); ?>
</div>

<h2>Package Actions - [ <?php echo $html->image('add.png', array('alt' => 'Add', 'url' => array('action' => 'add', 'action', $package['Package']['id']))) ?> ]</h2><hr class="hbar" />
<div id="actions" class="clear">
	<?php echo $this->element('packageactions', array('packageActions' => $packageActions)); ?>
</div>