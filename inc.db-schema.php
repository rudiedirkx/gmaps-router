<?php

return [
	'version' => 3,
	'tables' => [
		'routes' => [
			'id' => ['pk' => true],
			'secret',
			'created_by_ip',
			'created_on' => ['unsigned' => true],
			'name',
			'routes' => ['type' => 'text'],
		],
	],
];
