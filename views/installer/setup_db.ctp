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

echo $form->create('Installer', $url);
echo "<fieldset><legend>Step $step of $steps - " . $this->pageTitle . '</legend>';

if (!empty($criticalerrors)) {
    echo '<ul class="criticalerrors">';
    foreach ($criticalerrors as $err) {
        echo "<li>$err</li>";
    }
    echo '</ul>';
}

echo $form->input('driver', array(
    'label' => 'Driver: ',
    'options' => $drivers,
    'default' => getenv('DB_DRIVER')
));
echo $form->input('persistent', array(
    'label' => 'Persistent connection: ',
    'options' => array(1 => 'Yes', 0 => 'No'),
    'default' => getenv('DB_PERSISTENT')
));
echo $form->input('database', array(
    'label' => 'Database name: ',
    'default' => getenv('DB_NAME')
));
echo $form->input('host', array(
    'label' => 'Host: ',
    'default' => getenv('DB_HOST')
));
echo $form->input('port', array(
    'label' => 'Port: ',
    'default' => getenv('DB_PORT')
));
echo $form->input('login', array(
    'label' => 'Login: ',
    'autocomplete' => 'off',
    'default' => getenv('DB_USER')
));
echo $form->input('password', array(
    'label' => 'Password: ',
    'default' => getenv('DB_PASS')
));

echo '<hr />';
echo $form->end($submit);
echo '</fieldset>';
