<?php

namespace rdx\grouter;

use db_generic_model;

class Route extends db_generic_model {

	static public $_table = 'routes';

	static public function load( $secret ) {
		if ( $secret ) {
			return static::first(['secret' => $secret]);
		}
	}

	static public function save( array $points ) {
		$secret = rand_string(10);
		static::insert([
			'secret' => $secret,
			'created_by_ip' => $_SERVER['REMOTE_ADDR'],
			'created_on' => time(),
			'points' => json_encode($points),
		]);
		return $secret;
	}

}
