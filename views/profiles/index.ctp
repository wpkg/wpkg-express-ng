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
<h2>Profiles - [ <?php echo $html->image('add.png', array('alt' => 'Add', 'url' => array('action' => 'add'))) ?> ]</h2><hr class="hbar" />

<?php if (count($profiles) == 0): ?>

<div class="message">No Profiles found</div>

<?php else: ?>

<?php
$sortDir = $paginator->sortDir();
$sortKey = $paginator->sortKey();
?>

<table cellpadding="0" cellspacing="0">
	<tr>
	<th width="68"><?php echo ($sortKey == 'Profile.enabled') ? $html->image("/img/$sortDir.gif", array("alt"=>$sortDir)) : ""; ?><?php echo $paginator->sort('Enabled', 'enabled', array('url' => array('action' => 'index'))); ?></th>
	<th><?php echo ($sortKey == 'Profile.id_text') ? $html->image("/img/$sortDir.gif", array("alt"=>$sortDir)) : ""; ?><?php echo $paginator->sort('Id', 'id_text', array('url' => array('action' => 'index'))); ?></th>
	<th><?php echo ($sortKey == 'Profile.modified') ? $html->image("/img/$sortDir.gif", array("alt"=>$sortDir)) : ""; ?><?php echo $paginator->sort('Last Modified', 'modified', array('url' => array('action' => 'index'))); ?></th>
	<th width="55">Actions</th>
	</tr>

<?php foreach($profiles as $profile): ?>
	<tr<?php echo ($profile['Profile']['enabled'] == 0) ? " class=\"disabled\"" : "" ?>>
		<td><?php echo $html->link(($profile['Profile']['enabled'] == 0 ? "No" : "Yes"), array('action'=>($profile['Profile']['enabled'] == 0 ? 'enable' : 'disable'), $profile['Profile']['id'])); ?></td>
		<td><?php echo $html->link($profile['Profile']['id_text'], array('action'=>'view', $profile['Profile']['id'])); ?></td>
		<td><div class="date"><?php echo date("Y-m-d h:i:s A", strtotime($profile['Profile']['modified'])); ?></div></td>
		<td><?php echo $html->image('pencil.png', array('alt' => 'Edit', 'url' => array('action' => 'edit', $profile['Profile']['id']))) . "&nbsp;";
			  echo $html->link($html->image('delete.png'), array('action'=>'delete', $profile['Profile']['id']), array('alt' => 'Delete', 'escape' => false), false, false);
		?></td>
	</tr>
<?php endforeach; ?>

</table>

<?php
echo $paginator->counter(array('format' => __('Showing page %page% of %pages%, %current% record(s) out of %count% total', true)));
?>

<div class="paging">
	<?php echo $prev = $paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
        <?php echo (strpos($prev, "disabled") === false ? "|" : "") ?><?php echo $paginator->numbers();?>
	<?php echo $paginator->next(__('next', true).' >>', array(), null, array('class'=>'disabled'));?>
</div>

<?php endif; ?>
