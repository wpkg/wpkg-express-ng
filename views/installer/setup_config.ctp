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
echo $form->create('Installer', $url);
echo "<fieldset><legend>Step $step of $steps - " . $this->pageTitle . "</legend>";
if (isset($criticalerror)) {
	echo "<ul class=\"criticalerrors\">";
	echo "<li>" . $criticalerror . "</li>";
	echo "</ul>";
}
echo "<div class=\"installerHeader\">Web Authentication</div>";
echo $form->input('Auth.user', array('label' => 'Username: ', 'autocomplete' => "off"));
echo $form->input('Auth.password', array('label' => 'Password: '));
echo "<div class=\"installerHeader\">System</div>";
echo $form->input('System.secure', array('label' => 'Force SSL: ', 'type' => 'checkbox'));
echo "<div class=\"installerHeader\">XML Feed</div>";
echo $form->input('XMLFeed.exportdisabled', array('label' => 'Export disabled items [<span style="border-bottom: 1px dotted #000000" title="Disabled items will only appear as XML comments">?</span>] : ', 'type' => 'checkbox'));
echo $form->input('XMLFeed.formatxml', array('label' => 'Format XML output [<span style="border-bottom: 1px dotted #000000" title="Only useful for debugging purposes">?</span>] :', 'type' => 'checkbox'));
echo "<fieldset style=\"margin-left: 10px; width: 340px\"><legend style=\"font-weight: normal; font-size: 8pt; border: 0\">Security</legend>";
echo $form->input('XMLFeed.protectxml', array('label' => 'Protect XML output: ', 'type' => 'checkbox'));
echo $form->input('XMLFeed.xmluser', array('label' => 'XML Username: ', 'autocomplete' => "off"));
echo $form->input('XMLFeed.xmlpassword', array('label' => 'XML Password: ', 'type' => 'password'));
echo "</fieldset><hr />";
echo $form->end($submit);
echo "</fieldset>";
?>