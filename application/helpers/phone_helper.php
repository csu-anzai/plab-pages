<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('valid_phone')) {
	function valid_phone (
		$value = '',
		$min_length = 9,
		$max_length = 0,
		$pattern = '/^((0|\(?\+60\)?\s?|0060\s?|60\s?)?(\d\d)([\-\s]?\d\d\d?\d){2})$/'
	) {
		return vh_validateFieldByRules($value, [
				'min_length' => $min_length,
				'max_length' => $max_length,
				'pattern'    => $pattern,
		]);
	}
}

function vh_validateFieldByRules($value = '', $rules = []) {
	$is_valid = true;

	// Check if empty value
	empty($value) && $is_valid = false;

	// Validate by pattern
	if ( ! empty($pattern)) {
		preg_match($pattern, $value, $match);
		empty($match) && $is_valid = false;
	}

	// Validate by min_length
	if ( ! empty($min_length = $rules['min_length']) && $min_length > 0) {
		(strlen($value) < $min_length) && $is_valid = false;
	}

	// Validate by max_length
	if ( ! empty($max_length = $rules['max_length']) && $max_length > 0) {
		(strlen($value) > $max_length) && $is_valid = false;
	}

	return $is_valid;
}
