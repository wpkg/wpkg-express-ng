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
if (!$isAjax) {
	$javascript->link('jquery.js', false);
	$javascript->link('jquery_json.js', false);
	$javascript->link('pretty.js', false);
	$javascript->link('dateformat.js', false);
	$js = ' 
			var interval;
			var params = "";
			function prettyDates() {
				$(".date").each(function(){ this.title = this.innerHTML; }).prettyDate();
				clearInterval(interval);
				interval = setInterval(function(){ $(".date").prettyDate(); }, 5000);
			}
			function updatePagingLinks() {
				$(".paging a").click(function(){ params=this.href.substr(this.href.indexOf("index")+5); $("#content").load(this.href, function() {update();}); return false; });
				$("th a").click(function(){ params=this.href.substr(this.href.indexOf("index")+5); $("#content").load(this.href, function() {update();}); return false; });
		    }
			function updateOtherLinks() {
				$("a[href*=\"enable\"], a[href*=\"disable\"]").click(function() {
					$.ajax({
						domobj: this,
						url: this.href,
						cache: false,
						success: function(data, textStatus) {
							data = $.secureEvalJSON(data);
							if (data.success) {
								if ($(this.domobj).html() == "Yes") {
									$(this.domobj).attr("href", this.domobj.href.replace("disable", "enable"));
									$(this.domobj).html("No");
									$(this.domobj).parent().parent().attr("class", "disabled");
								} else {
									this.domobj.href = this.domobj.href.replace("enable", "disable");
									$(this.domobj).html("Yes");
									$(this.domobj).parent().parent().removeAttr("class");
								}
								var now = new Date();
								$(this.domobj).parent().parent().children().eq(8).children().eq(0).attr("title", now.format("Y-m-d h:i:s A"));
								if ($("#flashMessage").length > 0)
									$("#flashMessage").remove();
							} else {
								if ($("#flashMessage").length == 0)
									$("#content h2:first").before("<div id=\"flashMessage\" class=\"message\"></div>");
								$("#flashMessage").html(data.message);
							}
						}
					});
					return false;
				});
				$("a[alt=\"Delete\"]").click(function() {
					var type = $("#content h2").text();
					type = type.substr(0, type.indexOf(" ")-1);
					if (confirm("Are you sure you wish to delete the " + type + " \"" + $(this).parent().parent().children().eq(1).children().eq(0).html() + "\"?")) {
						$("#content").load(this.href + params, function() {update();});
					}
					return false;
				});
			}
			function update() {
				prettyDates();
				updatePagingLinks();
				updateOtherLinks();
			}
			$(document).ready(function(){
				update();
			});
	';
	$javascript->codeBlock($js, array('allowCache' => false, 'safe' => false, 'inline' => false));
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
			  echo $html->link($html->image('delete.png'), array('action'=>'delete', $package['Package']['id']), array('alt' => 'Delete'), false, false);
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