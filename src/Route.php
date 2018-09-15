<?php

namespace rdx\grouter;

use db_generic_model;

class Route extends db_generic_model {

	static public $_table = 'routes';

	public function init() {
		if ( $this->points && !$this->routes ) {
			$this->update([
				'points' => null,
				'routes' => "[$this->points]",
			]);
		}
	}

	protected function get_routes_array() {
		return json_decode($this->routes, true) ?: [[]];
	}

	/** @return self[] */
	static public function load( array $secrets ) {
		if ( $secrets = array_filter($secrets) ) {
			return static::all(['secret' => $secrets]);
		}

		return [];
	}

	static public function save( $name, array $points ) {
		$secret = rand_string(10);
		static::insert([
			'secret' => $secret,
			'created_by_ip' => $_SERVER['REMOTE_ADDR'],
			'created_on' => time(),
			'name' => $name,
			'routes' => json_encode($points),
		]);
		return $secret;
	}

	public function __toString() {
		return (string) $this->name;
	}

}
