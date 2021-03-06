<?php

function html( $text ) {
	return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8') ?: htmlspecialchars((string)$text, ENT_QUOTES, 'ISO-8859-1');
}

function rand_string($length = 12) {
	$source = implode(range('A', 'Z')) . implode(range(0, 9)) . implode(range('a', 'z'));
	$string = '';
	while ( strlen($string) < $length ) {
		$string .= $source[rand(0, strlen($source) - 1)];
	}
	return $string;
}

function get_url( $path, $query = [] ) {
	$query = $query ? '?' . http_build_query($query) : '';
	$path = $path ? $path . '.php' : basename($_SERVER['SCRIPT_NAME']);
	return $path . $query;
}

function do_redirect( $path = null, $query = [] ) {
	$url = get_url($path, $query);
	header('Location: ' . $url);
	exit;
}
