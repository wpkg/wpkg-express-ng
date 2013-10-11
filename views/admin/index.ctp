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
<h2>Tasks</h2><hr class="hbar" />

<fieldset style="margin-left: 30px; width: 500px">
<legend>Import</legend>
<?php
echo $form->create('Import', array('url' => array('controller' => 'admin', 'action' => 'index'), 'enctype' => 'multipart/form-data'));
echo $form->input('Import.packages', array('label' => 'Packages: ', 'div' => false, 'after' => '<br />', 'type' => 'file'));
echo $form->input('Import.profiles', array('label' => 'Profiles: ', 'div' => false, 'after' => '<br />', 'type' => 'file'));
echo $form->input('Import.hosts', array('label' => 'Hosts: ', 'div' => false, 'type' => 'file'));
echo $form->end('Import');
?>
</fieldset>
<br />
<fieldset style="margin-left: 30px; width: 500px">
<legend>Export</legend>
<?php echo $html->link('Packages', array('controller' => 'packages', 'action' => 'index.xml')); ?><br />
<?php echo $html->link('Profiles', array('controller' => 'profiles', 'action' => 'index.xml')); ?><br />
<?php echo $html->link('Hosts', array('controller' => 'hosts', 'action' => 'index.xml')); ?><br />
</fieldset>

<h2>Settings</h2><hr class="hbar" />

<fieldset class="systemsettings" style="margin-left: 30px; width: 500px">
<legend>Web Authentication</legend>
<?php
echo $form->create('Auth', array('url' => array('controller' => 'admin', 'action' => 'index')));
echo $form->input('Auth.user', array('label' => 'Username: ', 'class' => 'input' . (in_array('user', array_keys($AuthErrs)) ? ' form-error' : ''), 'div' => false, 'after' => (in_array('user', array_keys($AuthErrs)) ? '<div class="error-message">' . $AuthErrs['user'] . '</div>' : '') . '<br />', 'autocomplete' => "off"));
echo $form->input('Auth.password', array('label' => 'Password: ', 'class' => 'input' . (in_array('password', array_keys($AuthErrs)) ? ' form-error' : ''), 'div' => false, 'after' => (in_array('password', array_keys($AuthErrs)) ? '<div class="error-message">' . $AuthErrs['password'] . '</div>' : '') . '<br />'));
echo $form->end('Save');
?>
</fieldset>
<br />
<fieldset class="systemsettings" style="margin-left: 30px; width: 500px">
<legend>System</legend>
<?php
echo $form->create('System', array('url' => array('controller' => 'admin', 'action' => 'index')));
echo $form->input('System.secure', array('label' => 'Force SSL: ', 'class' => 'input' . (isset($SystemErrs) && in_array('secure', array_keys($SystemErrs)) ? ' form-error' : ''), 'div' => false, 'after' => (isset($SystemErrs) && in_array('secure', array_keys($SystemErrs)) ? '<div class="error-message">' . $SystemErrs['secure'] . '</div>' : '') . '<br />', 'type' => 'checkbox'));
echo $form->end('Save');
?>
</fieldset>
<br />
<fieldset class="xmlsettings" style="margin-left: 30px; width: 500px">
<legend>XML Feed</legend>
<?php
echo $form->create('XMLFeed', array('url' => array('controller' => 'admin', 'action' => 'index')));
echo $form->input('XMLFeed.exportdisabled', array('label' => 'Export disabled items [<span style="border-bottom: 1px dotted #000000" title="Disabled items will only appear as XML comments">?</span>] : ', 'class' => 'input' . (in_array('exportdisabled', array_keys($XMLFeedErrs)) ? ' form-error' : ''), 'div' => false, 'after' => (in_array('exportdisabled', array_keys($XMLFeedErrs)) ? '<div class="error-message">' . $XMLFeedErrs['exportdisabled'] . '</div>' : '') . '<br />', 'type' => 'checkbox'));
echo $form->input('XMLFeed.formatxml', array('label' => 'Format XML output [<span style="border-bottom: 1px dotted #000000" title="Only useful for debugging purposes">?</span>] : ', 'class' => 'input' . (in_array('formatxml', array_keys($XMLFeedErrs)) ? ' form-error' : ''), 'div' => false, 'after' => (in_array('formatxml', array_keys($XMLFeedErrs)) ? '<div class="error-message">' . $XMLFeedErrs['formatxml'] . '</div>' : '') . '<br />', 'type' => 'checkbox'));
echo $form->input('XMLFeed.protectxml', array('label' => 'Protect XML output: ', 'class' => 'input' . (in_array('protectxml', array_keys($XMLFeedErrs)) ? ' form-error' : ''), 'div' => false, 'after' => (in_array('protectxml', array_keys($XMLFeedErrs)) ? '<div class="error-message">' . $XMLFeedErrs['protectxml'] . '</div>' : '') . '<br />', 'type' => 'checkbox'));
echo $form->input('XMLFeed.xmluser', array('label' => 'Username: ', 'class' => 'input' . (in_array('xmluser', array_keys($XMLFeedErrs)) ? ' form-error' : ''), 'div' => false, 'after' => (in_array('xmluser', array_keys($XMLFeedErrs)) ? '<div class="error-message">' . $XMLFeedErrs['xmluser'] . '</div>' : '') . '<br />', 'autocomplete' => "off"));
echo $form->input('XMLFeed.xmlpassword', array('label' => 'Password: ', 'class' => 'input' . (in_array('xmlpassword', array_keys($XMLFeedErrs)) ? ' form-error' : ''), 'div' => false, 'after' => (in_array('xmlpassword', array_keys($XMLFeedErrs)) ? '<div class="error-message">' . $XMLFeedErrs['xmlpassword'] . '</div>' : '')));
echo $form->end('Save');
?>
</fieldset>