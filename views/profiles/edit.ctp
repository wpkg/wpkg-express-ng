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
<style type="text/css">label {width: 93px;}</style>
<h2>Editing Profile '<?php echo $html->link($name, array('action'=>'view', $this->data['Profile']['id'])); ?>'</h2><hr class="hbar" />
<?php echo $form->create('Profile'); ?>
<div class="inputwrap"><label for="ProfileEnabled" title="<?php echo TOOLTIP_PROFILE_ENABLED; ?>">Enabled:</label><?php echo $form->input('enabled', array('label' => false, 'div' => false, 'type' => 'checkbox')) ?></div>
<div class="inputwrap"><label for="ProfileIdText" title="<?php echo TOOLTIP_PROFILE_ID; ?>"><span class="required">*</span>ID:</label><?php echo $form->input('id_text', array('label' => false, 'div' => false, 'class'=>'input', 'size'=>'20', 'maxlength'=>'100')) ?></div>
<div class="inputwrap"><label for="ProfileNotes" title="<?php echo TOOLTIP_PROFILE_NOTES; ?>">Notes:</label><?php echo $form->input('notes', array('label' => false, 'div' => false, 'cols'=>'30', 'rows'=>'4')) ?></div>
<div class="inputwrap"><label for="ProfileDependency" title="<?php echo TOOLTIP_PROFILE_DEPENDSON; ?>">Dependencies:</label><?php
	if (count($profileDependencies) > 0)
		echo $form->input('ProfileDependency', array('label' => false, 'multiple' => true, 'div' => false));
	else
		echo "No other Profiles to choose from.";
?></div>
<div class="inputwrap"><label>&nbsp;</label><?php echo $form->end('Submit'); ?></div>