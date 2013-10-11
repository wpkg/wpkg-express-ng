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
$javascript->codeBlock('$(document).ready(function(){ $("input:visible:enabled:first").focus(); })', array('allowCache' => false, 'safe' => false, 'inline' => false));

echo $form->create($dest, $url);
echo "<fieldset><legend>wpkgExpress Login</legend>";
if (!empty($criticalerrors)) {
	echo "<ul class=\"criticalerrors\">";
	foreach ($criticalerrors as $err)
		echo "<li>" . $err . "</li>";
	echo "</ul>";
}
echo $form->input('user', array('label' => 'Login: ', 'autocomplete' => 'off'));
echo $form->input('password', array('label' => 'Password: '));
echo "<hr />";
echo $form->end("Login");
echo "</fieldset>";
?>