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
		function prettyDates() {
			$(".date").each(function(){ this.title = this.innerHTML; }).prettyDate();
			clearInterval(interval);
			interval = setInterval(function(){ $(".date").prettyDate(); }, 5000);
		}
		function updatePackages() {
			$("#packages a[href*=\"delete\"]").click(function() {
				$("#packages").load(this.href, function() {updatePackages();});
				return false;
			});
		}
		$(document).ready(function(){
			prettyDates();
			updatePackages();
		});
';
$javascript->codeBlock($js, array('allowCache' => false, 'safe' => false, 'inline' => false));
?>
<style type="text/css">label {width: 114px;}</style>
<h2>Profile Details for '<?php echo $profile['Profile']['id_text']; ?>' - [ <?php echo $html->image('pencil.png', array('alt' => 'Edit', 'url' => array('action' => 'edit', $profile['Profile']['id']))) . "&nbsp;" . $html->link($html->image('delete.png'), array('action'=>'delete', $profile['Profile']['id']), array('alt' => 'Delete'), sprintf("Are you sure you want to delete Host '%s'?", addcslashes($profile['Profile']['id_text'], '"')), false) . "&nbsp;" . $html->image('var.png', array('alt' => 'Variables', 'url' => array('controller' => 'variables', 'action' => 'view', 'profile', $profile['Profile']['id']))) ?> ]</h2><hr class="hbar" />
<div class="inputwrap"><label title="<?php echo TOOLTIP_PROFILE_ENABLED; ?>">Enabled:</label><?php echo $profile['Profile']['enabled'] == 0 ? "No" : "Yes" ?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PROFILE_ID; ?>">ID:</label><?php echo $profile['Profile']['id_text'] ?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PROFILE_NOTES; ?>">Notes:</label><?php echo (!empty($profile['Profile']['notes']) ? "<div class=\"notes\">" . nl2br(Sanitize::html($profile['Profile']['notes'])) . "</div>" : "&lt;None&gt;") ?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PROFILE_LASTMODIFIED; ?>">Last Modified:</label><div class="date"><?php echo (!empty($profile['Profile']['modified']) ? date("Y-m-d h:i:s A", strtotime($profile['Profile']['modified'])) : "&nbsp;") ?></div></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_PROFILE_DEPENDSON; ?>">Depends On:</label>
	<?php
		if (count($profile['ProfileDependency']) == 0)
			echo "None";
		else {
			echo "<ul>";
			foreach ($profile['ProfileDependency'] as $profileDepends)
				echo "<li>" . $html->link($profileDepends['id_text'], array('action'=>'view', $profileDepends['id']));
			echo "</ul>";
		}
	?>
</div>

<h2>Associated Packages - [ <?php echo $html->image('pencil.png', array('alt' => 'Edit', 'url' => array('action' => 'add', 'package', $profile['Profile']['id']))) ?> ]</h2><hr class="hbar" />
<div id="packages" class="clear">
	<?php echo $this->element('profilepackages', array('packages' => $profile['Package'])); ?>
</div>