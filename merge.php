<?php

use rdx\grouter\Map;

require __DIR__ . '/inc.bootstrap.php';

$map = Map::first(['secret' => $_GET['id'] ?? '']);
if ( !$map ) exit('Invalid map id');

if ( isset($_POST['maps']) ) {
	$merges = Map::all('id <> ? AND secret IN (?)', [$map->id, $_POST['maps']]);

	$routes = $map->routes_array == [[]] ? [] : $map->routes_array;
	foreach ($merges as $merge) {
		$routes = array_merge($routes, $merge->routes_array);
	}

	$map->update([
		'routes' => json_encode($routes),
	]);

	return do_redirect('list');
}

$maps = Map::all('id <> ? ORDER BY created_on DESC', [$map->id]);

?>
<h1>Merge selected maps into <em><?= html($map->name) ?></em></h1>

<form method="post" action>
	<table cellpadding="6" cellspacing="0">
		<? foreach ($maps as $map): ?>
			<tr>
				<td>
					<label>
						<input type="checkbox" name="maps[]" value="<?= html($map->secret) ?>" />
						<?= html($map->name ?: '??') ?>
					</label>
				</td>
				<td><?= $map->created_on_label ?></td>
				<td><?= count($map->routes_array) ?> routes</td>
			</tr>
		<? endforeach ?>
	</table>
	<p><button>Merge</button></p>
</form>
