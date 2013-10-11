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
<h2>Add New Package</h2><hr class="hbar" />
<?php echo $form->create('Package'); ?>
<div class="inputwrap"><label for="PackageName" title="<?php echo TOOLTIP_PACKAGE_NAME; ?>"><span class="required">*</span>Name:</label><?php echo $form->input('name', array('label' => false, 'div' => false, 'class'=>'input', 'size'=>'40', 'maxlength'=>'100')) ?></div>
<div class="inputwrap"><label for="PackageIdText" title="<?php echo TOOLTIP_PACKAGE_ID; ?>"><span class="required">*</span>ID:</label><?php echo $form->input('id_text', array('label' => false, 'div' => false, 'class'=>'input', 'size'=>'20', 'maxlength'=>'100')) ?></div>
<div class="inputwrap"><label for="PackageRevision" title="<?php echo TOOLTIP_PACKAGE_REVISION; ?>">Revision:</label><?php echo $form->input('revision', array('label' => false, 'div' => false, 'class'=>'input', 'size'=>'8', 'maxlength'=>'10')) ?></div>
<div class="inputwrap"><label for="PackagePriority" title="<?php echo TOOLTIP_PACKAGE_PRIORITY; ?>">Priority:</label><?php echo $form->input('priority', array('label' => false, 'div' => false, 'class'=>'input', 'size'=>'8', 'maxlength'=>'11')) ?></div>
<div class="inputwrap"><label for="PackageExecute" title="<?php echo TOOLTIP_PACKAGE_EXECUTE; ?>">Execute:</label><?php echo $form->input('execute', array('label' => false, 'options' => array(PACKAGE_EXECUTE_NORMAL => 'Normal', PACKAGE_EXECUTE_ALWAYS => 'Always', PACKAGE_EXECUTE_ONCE => 'Once'), 'div' => false)) ?></div>
<div class="inputwrap"><label for="PackageReboot" title="<?php echo TOOLTIP_PACKAGE_REBOOT; ?>">Reboot:</label><?php echo $form->input('reboot', array('label' => false, 'options' => array(PACKAGE_REBOOT_FALSE => 'False', PACKAGE_REBOOT_TRUE => 'True', PACKAGE_REBOOT_POSTPONED => 'Postponed'), 'div' => false)) ?></div>
<div class="inputwrap"><label for="PackageNotify" title="<?php echo TOOLTIP_PACKAGE_NOTIFY; ?>">Notify:</label><?php echo $form->input('notify', array('label' => false, 'div' => false, 'type' => 'checkbox')) ?></div>
<div class="inputwrap"><label for="PackageNotes" title="<?php echo TOOLTIP_PACKAGE_NOTES; ?>">Notes:</label><?php echo $form->input('notes', array('label' => false, 'div' => false, 'cols'=>'30', 'rows'=>'4')) ?></div>
<div class="inputwrap"><label>&nbsp;</label><?php echo $form->end('Submit'); ?></div>