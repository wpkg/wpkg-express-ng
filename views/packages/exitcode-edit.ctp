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
<h2>Editing Exit Code for '<?php echo $html->link($pkgActType, array('controller'=>'packages', 'action'=>'view', 'action', $pkgActId), array('title'=>$pkgActCmd)); ?>' Package Action</h2><hr class="hbar" />
<?php echo $form->create("ExitCode", array("url" => "/packages/edit/exitcode/" . $this->data['ExitCode']['id'])); ?>
<div class="inputwrap"><label for="ExitCodeCode" title="<?php echo TOOLTIP_EXITCODE_CODE; ?>"><span class="required">*</span>Code:</label><?php echo $form->input('code', array('label' => false, 'class'=>'input', 'div' => false, 'size' => 8)) ?></div>
<div class="inputwrap"><label for="ExitCodeReboot" title="<?php echo TOOLTIP_EXITCODE_REBOOT; ?>">Reboot:</label><?php echo $form->input('reboot', array('label' => false, 'div' => false, 'options' => array(EXITCODE_REBOOT_FALSE => 'None', EXITCODE_REBOOT_TRUE => 'Yes', EXITCODE_REBOOT_DELAYED => 'Delayed', EXITCODE_REBOOT_POSTPONED => 'Postponed'))) ?></div>
<div class="inputwrap"><label>&nbsp;</label><?php echo $form->end('Submit'); ?></div>