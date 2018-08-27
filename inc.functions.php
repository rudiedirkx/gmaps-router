<?php

function rand_string($length = 12) {
	$source = implode(range('A', 'Z')) . implode(range(0, 9)) . implode(range('a', 'z'));
	$string = '';
	while ( strlen($string) < $length ) {
		$string .= $source[rand(0, strlen($source) - 1)];
	}
	return $string;
}
