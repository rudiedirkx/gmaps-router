<?php

require 'env.php';

?>
<!doctype html>
<html>

<head>
<title>Gmaps router</title>
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
#stats dl {
	margin: 0;
}
</style>
</head>

<body>
<div id="map"></div>

<div id="stats">
	<button id="undo">Undo</button>
	<button id="export">Export</button>
	<dl>
		<dt>Points</dt>
		<dd><span id="stat-points">0</span></dd>
		<dt>Distance</dt>
		<dd><span id="stat-distance">0</span> km</dd>
	</dl>
	<textarea id="output"></textarea>
</div>

<script>
var $map = document.querySelector('#map');
var $output = document.querySelector('#output');
var $undo = document.querySelector('#undo');
var $export = document.querySelector('#export');
var $points = document.querySelector('#stat-points');
var $distance = document.querySelector('#stat-distance');

var map;
var points = JSON.parse(sessionStorage.points || '[]');
var line;

function undoPoint() {
	points.pop();
	drawLines();
}

function addPoint( point ) {
	points.push(point);
	drawLines();
}

function calcStats() {
	var distance = google.maps.geometry.spherical.computeLength(line.getPath().getArray());
	distance = Math.round(distance/100)/10;

	$points.textContent = points.length;
	$distance.textContent = distance;
}

function drawLines() {
	if (line) {
		line.setPath(points);
	}
	else {
		line = new google.maps.Polyline({
			path: points,
			geodesic: true,
			strokeColor: '#FF0000',
			strokeOpacity: 1.0,
			strokeWeight: 2,
		});
		line.setMap(map);
	}

	calcStats();

	sessionStorage.points = JSON.stringify(points);
}

function init() {
	map = new google.maps.Map($map, {
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

	var untilesloaded = google.maps.event.addListener(map, 'tilesloaded', function(e) {
		line || drawLines();
		untilesloaded.remove();
	});

	google.maps.event.addListener(map, 'click', function(e) {
		addPoint({lng: e.latLng.lng(), lat: e.latLng.lat()});
	});

	google.maps.event.addListener(map, 'rightclick', function(e) {
		undoPoint();
	});

	// var drawingManager = new google.maps.drawing.DrawingManager({
	// 	drawingMode: google.maps.drawing.OverlayType.MARKER,
	// 	drawingControl: true,
	// 	drawingControlOptions: {
	// 		position: google.maps.ControlPosition.TOP_CENTER,
	// 		drawingModes: ['polyline']
	// 	},
	// });
	// drawingManager.setMap(map);

	// google.maps.event.addListener(drawingManager, 'polylinecomplete', function(e) {
	// 	console.log(e);
	// });
	// google.maps.event.addListener(drawingManager, 'overlaycomplete', function(e) {
	// 	console.log(e);
	// });

	([
		// 'bounds_changed',
		// 'center_changed',
		// 'click',
		// 'dblclick',
		// 'drag',
		// 'dragend',
		// 'dragstart',
		// 'heading_changed',
		// 'idle',
		// 'maptypeid_changed',
		// // 'mousemove',
		// // 'mouseout',
		// // 'mouseover',
		// 'projection_changed',
		// 'rightclick',
		// 'tilesloaded',
		// 'tilt_changed',
		// 'zoom_changed',
	]).forEach(type => google.maps.event.addListener(map, type, function(e) {
		console.log(type, e);
	}));
}

$undo.onclick = function(e) {
	undoPoint();
};

$export.onclick = function(e) {
	$output.value = sessionStorage.points;
};
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?= ROUTER_GMAPS_API_KEY ?>&libraries=drawing,geometry&callback=init" async defer></script>
</body>

</html>
