<?php foreach ($GLOBALS['masterof'] as $group) : ?>
	<h2><?= escape(Groups::id2name($group)) ?></h2>
	<?php if (count($colonies[$group])) : ?>
		<ul>
		<?php foreach ($colonies[$group] as $colony) : ?>
			<li><?= escape($colony['name']) ?> - <?= escape(Forces::id2name($colony['force_id'])) ?> - <?= $colony['ip'] ?>IP</li>
		<?php endforeach ?>
		</ul>
	<?php else : ?>
		Bisher keine Kolonien definiert.
	<?php endif ?>
<?php endforeach ?>