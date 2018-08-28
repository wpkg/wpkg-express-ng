<?php if (!isset($total)): ?>
	<center><h1>Unable to complete your search request</h1></center>
<?php else: ?>
	<br />Found <b><?php echo $total ?> result<?php echo ($total == 1 ? "" : "s") ?></b> while searching <?php echo $text->toList($kind); ?> for "<b><?php echo $query ?></b>":
	<br />
	<?php foreach ($results as $model => $records): ?>
	<?php echo "<h1>" . Inflector::pluralize($model) . " (" . count($records) . ")</h1>" ?>
		<ol>
		<?php $resultnum = 1; foreach ($records as $result): ?>
			<li><?php 
					if (isset($result['name']))
						$displayVal = $result['name'];
					else if (isset($result['id_text']))
						$displayVal = $result['id_text'];
					else
						$displayVal = "Result #$resultnum";
					echo $html->link($displayVal, array('controller' => Inflector::pluralize(strtolower($model)), 'action' => 'view', $result['id']));
					unset($result['id']);
			?>
			<br />
			<ul style="list-style-type: none; list-style-image: none">
			<?php foreach ($result as $field => $val):
					if (stripos($val, $query) !== false):
			?>
				<li><b><?php echo ($field == 'id_text' ? 'Id' : ucwords($field)) ?>:</b> <?php echo $text->highlight($val, $query) ?>
				</li>
			<?php	endif;
				  endforeach;
			?>
			</ul>
			</li>
		<?php $resultnum++; endforeach; ?>
		</ol><br />
	<?php endforeach; ?>
<?php endif; ?>