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
      $width = 75;
	$pathLabel = "Path: ";
	$valueLabel = "Value: ";
	$showPath = true;
	$showValue = true;
	switch ($checkType) {
		case CHECK_TYPE_LOGICAL:
			$showPath = false;
			$showValue = false;
			break;
		case CHECK_TYPE_UNINSTALL:
			$width = 136;
			$showValue = false;
			$pathLabel = "Add/Remove Name:";
			break;
		case CHECK_TYPE_REGISTRY:
			if ($checkTypeCond == CHECK_CONDITION_REGISTRY_EXISTS)
				$showValue = false;
			break;
		case CHECK_TYPE_FILE:
			$pathLabel = "File Path:";
			$value_width = 10;
			if ($checkTypeCond >= CHECK_CONDITION_FILE_VERSION_SMALLER_THAN
			    && $checkTypeCond <= CHECK_CONDITION_FILE_VERSION_GREATER_THAN_OR_EQUAL_TO)
				$valueLabel = "Version:";
			else if ($checkTypeCond == CHECK_CONDITION_FILE_EXISTS)
				$showValue = false;
			else if ($checkTypeCond == CHECK_CONDITION_FILE_SIZE_EQUALS) {
				$width = 103;
				$valueLabel = "Size (in bytes):";
			}
			break;
		case CHECK_TYPE_EXECUTE:
			$width = 118;
			$value_width = 10;
			$pathLabel = "Executable Path:";
			$valueLabel = "Exit Code:";
			break;
	}
?>
<style type="text/css">label {width: <?php echo $width; ?>px;}</style>
<h2>Adding Package Check for '<?php echo $html->link($pkgName, array('controller'=>'packages', 'action'=>'view', $pkgId)); ?>'</h2><hr class="hbar" />
<?php echo $form->create("PackageCheck", array("url" => "/packages/add/check/$pkgId")); ?>

<div class="inputwrap"><label for="PackageCheckType" title="<?php echo TOOLTIP_PACKAGECHECK_TYPE; ?>"><span class="required">*</span>Type:</label>
	<?php if (isset($logicalChecks)): ?>
		<?php echo $form->input('type', array('label' => false, 'selected' => $checkType, 'options' => array(CHECK_TYPE_REGISTRY => 'Registry', CHECK_TYPE_FILE => 'File', CHECK_TYPE_UNINSTALL => 'Uninstall', CHECK_TYPE_LOGICAL => 'Logical', CHECK_TYPE_EXECUTE => 'Execute'), 'div' => false, 'onchange' => 'window.location.href = "' . HtmlHelper::url("add/check/$pkgId") . '/type:" + document.getElementById("PackageCheckType").value;')) ?>
	<?php else: ?>
		Logical<?php echo $form->hidden('type', array('label' => false, 'selected' => $checkType, 'options' => array(CHECK_TYPE_LOGICAL => 'Logical'), 'div' => false)) ?>
	<?php endif; ?>
</div>
<div class="inputwrap"><label for="PackageCheckCondition" title="<?php echo TOOLTIP_PACKAGECHECK_CONDITION; ?>"><span class="required">*</span>Condition:</label><?php echo $form->input('condition', array('label' => false, 'selected' => $checkTypeCond, 'div' => false, 'options' => $checkCond, 'onchange' => 'window.location.href = "' . HtmlHelper::url("add/check/$pkgId") . '/type:" + document.getElementById("PackageCheckType").value + "/cond:" + document.getElementById("PackageCheckCondition").value;')) ?></div>
<?php if ($showPath): ?>
	<div class="inputwrap"><label for="PackageCheckPath" title="<?php echo TOOLTIP_PACKAGECHECK_PATH; ?>"><span class="required">*</span><?php echo $pathLabel; ?></label><?php echo $form->input('path', array('label' => false, 'class'=>'input', 'div' => false, 'size' => 80)) ?></div>
<?php endif; ?>
<?php if ($showValue): ?>
	<div class="inputwrap"><label for="PackageCheckValue" title="<?php echo TOOLTIP_PACKAGECHECK_VALUE; ?>"><span class="required">*</span><?php echo $valueLabel; ?></label><?php echo $form->input('value', array('label' => false, 'class'=>'input', 'div' => false, 'size' => $value_width)) ?></div>
<?php endif; ?>
<?php if (isset($logicalChecks)): ?>
	<div class="inputwrap"><label for="PackageCheckParentId" title="<?php echo TOOLTIP_PACKAGECHECK_PARENT; ?>"><span class="required">*</span>Parent:</label><?php echo $form->input('parent_id', array('label' => false, 'options' => $logicalChecks, 'div' => false)) ?></div>
<?php endif; ?>
<div class="inputwrap"><label>&nbsp;</label><?php echo $form->end('Submit'); ?></div>