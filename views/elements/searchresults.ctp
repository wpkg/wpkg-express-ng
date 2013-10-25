<ul style="list-style-type: none">
<?php $resultnum = 1; foreach (current($results) as $result): ?>
	<li><?php echo $html->link('', array('controller' => '')) ?>
	<ul style="list-style-type: none">
	<?php foreach ($result as $field => $val): ?>
		<li><?php echo ($field == 'id_text' ? 'Id' : $field) ?>: <?php echo $text->highlight("..." . $text->excerpt($val, $query), $query) ?></li>
	<?php endforeach; ?>
	</li>
<?php $resultnum++; endforeach; ?>
</ul>