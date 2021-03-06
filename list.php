<?php

use rdx\grouter\Map;

require __DIR__ . '/inc.bootstrap.php';

$maps = Map::all('1 ORDER BY created_on DESC');

?>
<table cellpadding="6" cellspacing="0">
	<? foreach ($maps as $map): ?>
		<tr>
			<td>
				<a href="index.php?load=<?= $map->secret ?>">
					<?= html($map->name ?: '??') ?>
				</a>
			</td>
			<td><?= $map->created_on_label ?></td>
			<td><?= count($map->routes_array) ?> routes, <?= array_sum(array_map('count', $map->routes_array)) ?> points</td>
			<td>
				<a href="merge.php?id=<?= $map->secret ?>">merge</a>
				<a href="gpx.php?id=<?= $map->secret ?>">gpx</a>
			</td>
		</tr>
	<? endforeach ?>
</table>
