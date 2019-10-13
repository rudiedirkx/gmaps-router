<?php

use rdx\grouter\Map;

require __DIR__ . '/inc.bootstrap.php';

$map = Map::first(['secret' => $_GET['id']]);

header('Content-disposition: attachment; filename=route.gpx');

echo '<' . '?xml version="1.0" encoding="UTF-8" standalone="no"?' . ">\n";

?>
<gpx xmlns="http://www.topografix.com/GPX/1/1" creator="GRouter" version="1.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd">
	<? foreach ($map->routes_array as $i => $route):
		$n = count($map->routes_array) == 1 ? '' : ' ' . ($i + 1);
		?>
		<trk>
			<name><?= html($map->name) . $n ?></name>
			<desc><?= count($route) ?> points</desc>
			<trkseg>
				<? foreach ($route as $point): ?>
					<trkpt lat="<?= $point['lat'] ?>" lon="<?= $point['lng'] ?>"><ele>0</ele></trkpt>
				<? endforeach ?>
			</trkseg>
		</trk>
	<? endforeach ?>
</gpx>
