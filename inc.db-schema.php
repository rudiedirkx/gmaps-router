<?php

return [
	'version' => 2,
	'tables' => [
		'routes' => [
			'id' => ['pk' => true],
			'secret',
			'created_by_ip',
			'created_on' => ['unsigned' => true],
			'name',
			'points' => ['type' => 'text'],
		],
	],
];
