<? foreach ($GLOBALS['masterof'] as $group) : ?>
	<h2><?= escape(Groups::id2name($group)) ?></h2>
	<? if (count($colonies[$group])) : ?>
		<ul>
		<? foreach ($colonies[$group] as $colony) : ?>
			<li><?= escape($colony['name']) ?> - <?= escape(Forces::id2name($colony['force_id'])) ?> - <?= $colony['ip'] ?>IP</li>
		<? endforeach ?>
		</ul>
	<? else : ?>
		Bisher keine Kolonien definiert.
	<? endif ?>
<? endforeach ?>