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
	$js = ' var interval;
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
								$(this.domobj).parent().parent().children().eq(2).children().eq(0).attr("title", now.format("Y-m-d h:i:s A"));
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
			  echo $html->link($html->image('delete.png'), array('action'=>'delete', $profile['Profile']['id']), array('alt' => 'Delete'), false, false);
			  //echo $html->link($html->image('delete.png'), array('action'=>'delete', $profile['Profile']['id']), array('alt' => 'Delete'), sprintf("Are you sure you want to delete Profile '%s'?", addcslashes($profile['Profile']['id_text'], '"')), false);
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