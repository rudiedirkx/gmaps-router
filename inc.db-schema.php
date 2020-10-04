<?php

return [
	'version' => 4,
	'tables' => [
		'routes' => [
			'id' => ['pk' => true],
			'secret' => ['ci' => false],
			'created_by_ip',
			'created_on' => ['unsigned' => true],
			'name',
			'routes' => ['type' => 'text'],
		],
	],
];
