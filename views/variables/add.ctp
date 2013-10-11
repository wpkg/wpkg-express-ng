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
<style type="text/css">label {80px;}</style>
<h2>Adding Variable to <?php echo $type; ?> '<?php echo $html->link($name, array('controller' => Inflector::pluralize(strtolower($type)), 'action' => 'view', $recordId), array('title' => $name)); ?>'</h2><hr class="hbar" />
<?php echo $form->create('Variable', array('url' => array(strtolower($type), $recordId))); ?>
<div class="inputwrap"><label for="VariableName"><span class="required">*</span>Name:</label><?php echo $form->input('name', array('label' => false, 'class' => 'input', 'div' => false, 'size' => 15)) ?></div>
<div class="inputwrap"><label for="VariableValue"><span class="required">*</span>Value:</label><?php echo $form->input('value', array('label' => false, 'class' => 'input', 'div' => false, 'size' => 45)) ?></div>
<div class="inputwrap"><label>&nbsp;</label><?php echo $form->end('Submit'); ?></div>