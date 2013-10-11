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
<style type="text/css">label {width: 81px;}</style>
<h2>Add New Host</h2><hr class="hbar" />
<?php echo $form->create('Host'); ?>
<div class="inputwrap"><label for="HostName" title="<?php echo TOOLTIP_HOST_NAME; ?>"><span class="required">*</span>Name:</label><?php echo $form->input('name', array('label' => false, 'div' => false, 'class'=>'input', 'size'=>'20', 'maxlength'=>'100')) ?></div>
<div class="inputwrap"><label for="HostNotes" title="<?php echo TOOLTIP_HOST_NOTES; ?>">Notes:</label><?php echo $form->input('notes', array('label' => false, 'div' => false, 'cols'=>'30', 'rows'=>'4')) ?></div>
<div class="inputwrap"><label for="HostMainprofileId" title="<?php echo TOOLTIP_HOST_MAINPROFILE; ?>">Main Profile:</label><?php echo $form->input('Host.mainprofile_id', array('label' => false, 'div' => false, 'options' => $profiles)) ?></div>
<div class="inputwrap"><label>&nbsp;</label><?php echo $form->end('Submit'); ?></div>