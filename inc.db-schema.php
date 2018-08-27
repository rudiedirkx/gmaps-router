<?php

return [
	'version' => 1,
	'tables' => [
		'routes' => [
			'id' => ['pk' => true],
			'secret',
			'created_by_ip',
			'created_on' => ['unsigned' => true],
			'points' => ['type' => 'text'],
		],
	],
];
