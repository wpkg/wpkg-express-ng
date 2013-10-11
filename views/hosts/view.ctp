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
		function updateProfiles() {
			$("#profiles a[href*=\"delete\"]").click(function() {
				$("#profiles").load(this.href, function() {updateProfiles();});
				return false;
			});
		}
		$(document).ready(function(){
			prettyDates();
			updateProfiles();
		});
';
$javascript->codeBlock($js, array('allowCache' => false, 'safe' => false, 'inline' => false));
?>
<style type="text/css">label {width: 114px;}</style>
<h2>Host Details for '<?php echo $host['Host']['name']; ?>' - [ <?php echo $html->image('pencil.png', array('alt' => 'Edit', 'url' => array('action' => 'edit', $host['Host']['id']))) . "&nbsp;" . $html->link($html->image('delete.png'), array('action'=>'delete', $host['Host']['id']), array('alt' => 'Delete'), sprintf("Are you sure you want to delete Host '%s'?", addcslashes($host['Host']['name'], '"')), false) . "&nbsp;" . $html->image('var.png', array('alt' => 'Variables', 'url' => array('controller' => 'variables', 'action' => 'view', 'host', $host['Host']['id']))) ?> ]</h2><hr class="hbar" />
<div class="inputwrap"><label title="<?php echo TOOLTIP_HOST_ENABLED; ?>">Enabled:</label><?php echo $host['Host']['enabled'] == 0 ? "No" : "Yes" ?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_HOST_NAME; ?>">Name:</label><?php echo $host['Host']['name'] ?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_HOST_NOTES; ?>">Notes:</label><?php echo (!empty($host['Host']['notes']) ? "<div class=\"notes\">" . nl2br(Sanitize::html($host['Host']['notes'])) . "</div>" : "&lt;None&gt;") ?></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_HOST_LASTMODIFIED; ?>">Last Modified:</label><div class="date"><?php echo (!empty($host['Host']['modified']) ? date("Y-m-d h:i:s A", strtotime($host['Host']['modified'])) : "&nbsp;") ?></div></div>
<div class="inputwrap"><label title="<?php echo TOOLTIP_HOST_MAINPROFILE; ?>">Main Profile:</label><?php echo $html->link($host['MainProfile']['id_text'], array('controller'=>'profiles', 'action'=>'view', $host['MainProfile']['id'])) ?></div>

<h2>Additional associated Profiles - [ <?php echo $html->image('pencil.png', array('alt' => 'Edit', 'url' => array('action' => 'add', 'profile', $host['Host']['id']))) ?> ]</h2><hr class="hbar" />
<div id="profiles" class="clear">
	<?php echo $this->element('hostprofiles', array('profiles' => $host['Profile'])); ?>
</div>