<?php

use rdx\grouter\Route;

require __DIR__ . '/inc.bootstrap.php';

if ( isset($_POST['points'], $_POST['name']) ) {
	if ( $points = json_decode($_POST['points'], true) ) {
		$secret = Route::save($_POST['name'], $points);

		header('Location: ./?load=' . $secret);
	}
	else {
		header('Location: ./');
	}
	exit;
}

$routes = Route::load(explode(',', $_GET['load'] ?? ''));
$screenshot = isset($_GET['screenshot']);

?>
<!doctype html>
<html>

<head>
<meta charset="utf-8" />
<link href="favicon.ico" rel="shortcut icon" />
<title><? if ($routes): ?><?= html(implode(', ', $routes)) ?> - <? endif ?>Gmaps router</title>
<style>
body {
	margin: 10px;
}
#map {
	width: calc(100vw - 20px);
	height: calc(100vh - 20px);
}
#stats {
	position: fixed;
	bottom: 0;
	left: 0;
	padding: 10px;
	background-color: #000;
	color: #fff;
}
#built-stats {
	margin-top: 10px;
	display: flex;
}
#built-stats > dl {
	margin: 0;
	border: solid 2px var(--color);
}
#built-stats > dl.selected {
	background-color: var(--color);
}
#built-stats > dl:not(.selected) {
	cursor: pointer;
}
#built-stats > dl + dl {
	margin-left: 10px;
}
#output {
	position: absolute;
	width: 300px;
	height: 300px;
	top: 50vh;
	left: 50vw;
	margin: -150px 0 0 -150px;
}
#output:not(:focus) {
	top: -200px;
}
</style>
<?if ($screenshot): ?>
	<style>
	body {
		margin: 0;
		overflow: hidden;
	}
	#map {
		width: calc(100vw);
		height: calc(100vh);
	}
	#stats,
	form,
	.gm-style > * + * {
		display: none;
	}
	</style>
<? endif ?>
</head>

<body>
<div id="map"></div>

<form method="post" action>
	<input type="hidden" name="name" />
	<textarea id="output" name="points"></textarea>
</form>

<div id="stats">
	<button id="add-route">Add route</button>
	<button id="undo">Undo</button>
	<!-- <button id="export">Export</button> -->
	<button id="save" hidden>Save</button>
	<div id="built-stats"></div>
	<template>
		<dl style="--color: COLOR">
			<button class="copy">Copy</button>
			<dt>Points</dt>
			<dd><span class="stat-points">0</span></dd>
			<dt>Distance</dt>
			<dd><span class="stat-distance">0</span> km</dd>
		</dl>
	</template>
</div>

<script>
const COLORS = ['#ff0000', '#00ff00', '#0000ff', '#ff00ff', '#00ffff'];

class UI {
	constructor(el, points) {
		this.el = el;
		this.map = null;
		this.activeRoute = 0;
		this.points = points;
		this.routes = null;
		this.zIndex = 1;

		this.$$stats = [];
		this.$$points = [];
		this.$$distance = [];
	}

	init() {
		this.routes = this.points.map(points => this.makeRoute(points));
		this.routes.forEach(route => route.init());

		this.makeMap();

		this.buildStats();
		this.listen();
	}

	drawRoutes() {
		this.routes.forEach(route => {
			route.line.setMap(this.map);
		});

		this.calcStats();
	}

	calcStats() {
		this.routes.forEach((route, i) => {
			var distance = google.maps.geometry.spherical.computeLength(route.line.getPath().getArray());
			distance = Math.round(distance/100)/10;

			this.$$points[i].textContent = route.points.length;
			this.$$distance[i].textContent = distance;
		});

		var canSave = this.routes[0].points.length > 1;
		this.$save.hidden = !canSave;
	}

	buildStats() {
		var $tpl = document.querySelector('#stats template');
		var html = this.routes.map(route => $tpl.innerHTML.replace(/COLOR/g, route.color)).join('');
		document.querySelector('#built-stats').innerHTML = html;

		this.$$stats = document.querySelectorAll('#built-stats dl');
		this.$$points = document.querySelectorAll('.stat-points');
		this.$$distance = document.querySelectorAll('.stat-distance');

		setTimeout(() => this.selectRoute());
	}

	selectRoute(index) {
		if (index != null) {
			this.activeRoute = index;
		}

		[].forEach.call(document.querySelectorAll('.selected'), el => el.classList.remove('selected'));
		this.$$stats[this.activeRoute].classList.add('selected');

		this.routes[this.activeRoute].line.set('zIndex', ++this.zIndex);
	}

	addPoint(point) {
		this.routes[this.activeRoute].addPoint(point);
		this.drawRoutes();
		this.remember();
	}

	undoPoint() {
		this.routes[this.activeRoute].undoPoint();

		if (this.routes[this.activeRoute].isEmpty() && this.routes.length > 1) {
			this.removeRoute(this.activeRoute);
			this.activeRoute = 0;
			this.buildStats();
		}

		this.drawRoutes();
		this.remember();
	}

	removeRoute(index) {
		this.routes.splice(index, 1);
		this.routes.forEach(route => route.recolor());
	}

	addRoute(route) {
		this.routes.push(route);

		this.activeRoute = this.routes.length - 1;
		this.buildStats();

		route.init();
		this.drawRoutes();
		this.remember();
	}

	addNewRoute() {
		this.addRoute(this.makeRoute([]));
	}

	copyRoute(index) {
		var fromRoute = this.routes[index];
		var points = JSON.parse(JSON.stringify(fromRoute.points));
		this.addRoute(this.makeRoute(points));
	}

	remember() {
		sessionStorage.points = JSON.stringify(this.routes.map(route => route.points));
	}

	listen() {
		this.listenMap();
		this.listenControls();
	}

	listenMap() {
		google.maps.event.addListener(this.map, 'click', e => {
			this.addPoint({lng: e.latLng.lng(), lat: e.latLng.lat()});
		});

		google.maps.event.addListener(this.map, 'rightclick', e => {
			this.undoPoint();
		});

		// ([
		// 	// 'bounds_changed',	// 'maptypeid_changed',
		// 	// 'center_changed',	// 'mousemove',
		// 	// 'click',				// 'mouseout',
		// 	// 'dblclick',			// 'mouseover',
		// 	// 'drag',				// 'projection_changed',
		// 	// 'dragend',			// 'rightclick',
		// 	// 'dragstart',			// 'tilesloaded',
		// 	// 'heading_changed',	// 'tilt_changed',
		// 	// 'idle',				// 'zoom_changed',
		// ]).forEach(type => google.maps.event.addListener(this.map, type, function(e) {
		// 	console.log(type, e);
		// }));
	}

	listenControls() {
		this.$undo = document.querySelector('#undo');
		this.$builtStats = document.querySelector('#built-stats');
		this.$addRoute = document.querySelector('#add-route');
		this.$save = document.querySelector('#save');
		this.$output = document.querySelector('#output');

		console.log(this);

		this.$undo.onclick = e => {
			this.undoPoint();
		};

		this.$builtStats.onclick = e => {
			var copy = e.target.closest('.copy');
			var dl = e.target.closest('dl');
			if (copy) {
				var index = [].indexOf.call(dl.parentNode.children, dl);
				this.copyRoute(index);
			}
			else if (dl) {
				var index = [].indexOf.call(dl.parentNode.children, dl);
				this.selectRoute(index);
			}
		};

		this.$addRoute.onclick = e => {
			e.preventDefault();

			this.addNewRoute();
		};

		this.$save.onclick = e => {
			e.preventDefault();
			var name = prompt('Name:', '');
			if ( name && name.trim() ) {
				this.$output.form.elements.name.value = name;
				this.$output.value = sessionStorage.points;
				this.$output.form.submit();
			}
		};
	}

	makeBounds() {
		var points = this.routes.reduce((points, route) => points.concat(route.points), []);
		if (points.length > 0) {
			var bounds = new google.maps.LatLngBounds();
			points.forEach(point => bounds.extend(point));
			return bounds;
		}
	}

	makeMap() {
		var bounds = this.makeBounds();

		this.map = new google.maps.Map(this.el, {
			center: {lat: 51.44, lng: 5.48},
			zoom: 13,
			streetViewControl: false,
			tilt: 0,
			styles: [
				{featureType: 'poi', stylers: [{visibility: 'off'}]},
				{featureType: 'transit', stylers: [{visibility: 'off'}]},
				// {featureType: 'road', stylers: [{visibility: 'off'}]},
			],
		});
		bounds && this.map.fitBounds(bounds);

		var tilesloaded = google.maps.event.addListener(this.map, 'tilesloaded', e => {
			this.drawRoutes();

			tilesloaded.remove();
		});
	}

	makeRoute(points) {
		return new Route(this, points);
	}
}

class Route {
	constructor(map, points) {
		this.map = map;
		this.points = points;
		this.line = null;
	}

	init() {
		this.line = new google.maps.Polyline({
			path: this.points,
			geodesic: true,
			strokeColor: this.color,
			strokeOpacity: 1.0,
			strokeWeight: 2,
		});
	}

	recolor() {
		this.line.set('strokeColor', this.color);
	}

	isEmpty() {
		return this.points.length == 0;
	}

	addPoint(point) {
		this.points.push(point);
		this.line.setPath(this.points);
	}

	undoPoint() {
		this.points.pop();
		this.line.setPath(this.points);
	}

	get index() {
		return this.map.routes.indexOf(this);
	}

	get color() {
		return COLORS[this.index % COLORS.length];
	}
}

var ui = new UI(
	document.querySelector('#map'),
	<?= $routes ? json_encode(call_user_func_array('array_merge', array_column($routes, 'routes_array'))) : "JSON.parse(sessionStorage.points || '[[]]')" ?>
);
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= ROUTER_GMAPS_API_KEY ?>&libraries=drawing,geometry&callback=ui.init" async defer></script>
</body>

</html>
