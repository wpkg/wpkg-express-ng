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
if (!$isAjax) {
    echo $html->script(array('jquery.js', 'jquery_json.js', 'pretty.js', 'dateformat.js', 'wpkgexpress_ng.js'));
}

?>
<h2>Packages - [ <?php echo $html->image('add.png', array('alt' => 'Add', 'url' => array('action' => 'add'))) ?> ]</h2><hr class="hbar" />

<?php if (count($packages) == 0): ?>

<div class="message">No Packages found</div>

<?php else: ?>

<?php
$sortDir = $paginator->sortDir();
$sortKey = $paginator->sortKey();
?>

<table cellpadding="0" cellspacing="0">
	<tr>
	<th width="68"><?php echo ($sortKey == 'Package.enabled') ? $html->image("/img/$sortDir.gif", array("alt"=>$sortDir)) : ""; ?><?php echo $paginator->sort('Enabled', 'enabled', array('url' => array('action' => 'index'))); ?></th>
	<th><?php echo ($sortKey == 'Package.name') ? $html->image("/img/$sortDir.gif", array("alt"=>$sortDir)) : ""; ?><?php echo $paginator->sort('Name', 'name', array('url' => array('action' => 'index'))); ?></th>
	<th><?php echo ($sortKey == 'Package.id_text') ? $html->image("/img/$sortDir.gif", array("alt"=>$sortDir)) : ""; ?><?php echo $paginator->sort('Id', 'id_text', array('url' => array('action' => 'index'))); ?></th>
	<th><?php echo ($sortKey == 'Package.revision') ? $html->image("/img/$sortDir.gif", array("alt"=>$sortDir)) : ""; ?><?php echo $paginator->sort('Revision', 'revision', array('url' => array('action' => 'index'))); ?></th>
	<th width="63"><?php echo ($sortKey == 'Package.priority') ? $html->image("/img/$sortDir.gif", array("alt"=>$sortDir)) : ""; ?><?php echo $paginator->sort('Priority', 'priority', array('url' => array('action' => 'index'))); ?></th>
	<th width="61"><?php echo ($sortKey == 'Package.reboot') ? $html->image("/img/$sortDir.gif", array("alt"=>$sortDir)) : ""; ?><?php echo $paginator->sort('Reboot', 'reboot', array('url' => array('action' => 'index'))); ?></th>
	<th width="67"><?php echo ($sortKey == 'Package.execute') ? $html->image("/img/$sortDir.gif", array("alt"=>$sortDir)) : ""; ?><?php echo $paginator->sort('Execute', 'execute', array('url' => array('action' => 'index'))); ?></th>
	<th width="52"><?php echo ($sortKey == 'Package.notify') ? $html->image("/img/$sortDir.gif", array("alt"=>$sortDir)) : ""; ?><?php echo $paginator->sort('Notify', 'notify', array('url' => array('action' => 'index'))); ?></th>
	<th width="150"><?php echo ($sortKey == 'Package.modified') ? $html->image("/img/$sortDir.gif", array("alt"=>$sortDir)) : ""; ?><?php echo $paginator->sort('Last Modified', 'modified', array('url' => array('action' => 'index'))); ?></th>
	<th width="55">Actions</th>
	</tr>

<?php foreach($packages as $package): ?>
	<tr<?php echo ($package['Package']['enabled'] == 0) ? " class=\"disabled\"" : "" ?>>
		<td><?php echo $html->link(($package['Package']['enabled'] == 0 ? "No" : "Yes"), array('action'=>($package['Package']['enabled'] == 0 ? 'enable' : 'disable'), $package['Package']['id'])); ?></td>
		<td><?php echo $html->link($package['Package']['name'], array('action'=>'view', $package['Package']['id'])); ?></td>
		<td><?php echo $package['Package']['id_text']; ?></td>
		<td><?php echo $package['Package']['revision']; ?></td>
		<td><?php echo $package['Package']['priority']; ?></td>
		<td><?php 
				switch ($package['Package']['reboot']) {
					case PACKAGE_REBOOT_FALSE: echo "No"; break;
					case PACKAGE_REBOOT_TRUE: echo "Yes"; break;
					case PACKAGE_REBOOT_POSTPONED: echo "Postponed"; break;
					default: echo "Unknown";
				}				
		?></td>
		<td><?php 
				switch ($package['Package']['execute']) {
					case PACKAGE_EXECUTE_NORMAL: echo "Normal"; break;
					case PACKAGE_EXECUTE_ALWAYS: echo "Always"; break;
					case PACKAGE_EXECUTE_ONCE: echo "Once"; break;
					default: echo "Unknown";
				}				
		?></td>
		<td><?php echo ($package['Package']['notify'] == 0) ? "No" : "Yes"; ?></td>
		<td><div class="date"><?php echo date("Y-m-d h:i:s A", strtotime($package['Package']['modified'])); ?></div></td>
		<td><?php echo $html->image('pencil.png', array('alt' => 'Edit', 'url' => array('action' => 'edit', $package['Package']['id']))) . "&nbsp;";
			  //echo $html->link($html->image('delete.png'), array('action'=>'delete', $package['Package']['id']), array('alt' => 'Delete'), sprintf("Are you sure you want to delete Package '%s'?", addcslashes($package['Package']['name'], '"')), false);
			  echo $html->link($html->image('delete.png'), array('action'=>'delete', $package['Package']['id']), array('alt' => 'Delete', 'escape' => false));
		?></td>
	</tr>
<?php endforeach; ?>

</table>

<?php echo $paginator->counter(array('format' => 'Showing page %page% of %pages%, %current% record(s) out of %count% total')); ?>

<div class="paging">
	<?php echo $prev = $paginator->prev('<< previous', array(), null, array('class'=>'disabled')); ?>
        <?php echo (strpos($prev, "disabled") === false ? "|&nbsp;" : "") ?><?php echo $paginator->numbers(); ?>
	<?php $next = $paginator->next('next >>', array(), null, array('class'=>'disabled')); ?>
	<?php echo (strpos($next, "disabled") === false ? "|&nbsp;" : "") ?><?php echo $next; ?>
</div>

<?php endif; ?>
