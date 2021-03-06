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
echo $html->script(array('jquery.js', 'pretty.js', 'wpkgexpress_ng.js'));

?>
<h2>Variables for <?php echo $type; ?> '<?php echo $html->link($name, array('controller' => Inflector::pluralize(strtolower($type)), 'action' => 'view', $recordId), array('title' => $name)); ?>' - [ <?php echo $html->image('add.png', array('alt' => 'Add', 'url' => array('action' => 'add', strtolower($type), $recordId))); ?> ]</h2><hr class="hbar" />

<div id="variables" class="clear">
	<?php echo $this->element('variables', array('variables' => $variables)); ?>
</div>
