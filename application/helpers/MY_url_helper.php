<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * URL Parser
 * @var [type]
 */
if ( ! function_exists('parse_link') )
{
	function parse_link($url = '') {
		if ( ! empty($url) && strpos($url, 'http') === false ) {
			return base_url($url);
		}
		return $url;
	}
}

if ( ! function_exists('parse_src') )
{
	function parse_src($url = '') {
		if ( ! empty($url) && strpos($url, 'http') === false ) {
			return asset_url($url);
		}
		return $url;
	}
}

// -----------------------------------------------------------------------------

if ( ! function_exists('asset_url') )
{
	function asset_url($uri = '') {
		$CI =& get_instance();
		return $CI->config->slash_item('asset_url').$uri;
	}
}

if ( ! function_exists('current_url'))
{
	/**
	 * Current URL
	 *
	 * Returns the full URL (including segments) of the page where this
	 * function is placed
	 *
	 * @param 	bool 	$query_string 	Whether to add QUERY STRING.
	 * @return	string
	 */
	function current_url($query_string = true)
	{
		$CI =& get_instance();
		$url = $CI->config->site_url($CI->uri->uri_string());

		if ($query_string && ! empty($_SERVER['QUERY_STRING']))
		{
			$url .= '?'.$_SERVER['QUERY_STRING'];
		}

		return $url;
	}
}

if ( ! function_exists('previous_url'))
{
	/**
	 * Returns the last page the user visited.
	 *
	 * @param 	string 	$default 	Default value if no lat page exists.
	 * @param 	bool 	$uri_only 	Whether to return only the URI.
	 * @return 	bool
	 */
	function previous_url($default = null, $uri_only = false)
	{
		$prev_url = $default;

		if (isset($_SERVER['HTTP_REFERER'])
			&& $_SERVER['HTTP_REFERER'] != current_url()
			// We make sure the previous URL from the same site.
			&& false !== preg_match('#^'.site_url().'#', $prev_url))
		{
			$prev_url = $_SERVER['HTTP_REFERER'];
		}

		if ($prev_url)
		{
			$prev_url = (true === $uri_only)
				? str_replace(site_url(), '', $prev_url)
				: site_url($prev_url);
		}

		return $prev_url;
	}
}
