<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Input Class
 *
 * This class extends CI_Input class in order to add some useful methods.
 */
class MY_Input extends CI_Input
{
	/**
	 * Get Item From Request
	 *
	 * Method for fetching an item from the REQUEST array
	 *
	 * @author 	B. Kader
	 * @package CI Skeleton
	 *
	 * @access 	public
	 * @param 	string 	$index 		Index of the item to be fetched from $_REQUEST.
	 * @param 	bool 	$xss_clean 	Whether to apply XSS filtering.
	 * @return 	mixed
	 */
	public function request($index = null, $xss_clean = null)
	{
		return $this->_fetch_from_array($_REQUEST, $index, $xss_clean);
	}

  // ---------------------------------------------------------------------------

	/**
	 * Return Protocol
	 *
	 * Method for returning the protocol that the request was make with.
	 *
	 * @author 	B. Kader
	 * @package CI Skeleton
	 *
	 * @access 	public
	 * @param 	none
	 * @return 	string
	 */
	public function protocol()
	{
		if (
			$this->server('HTTPS') == 'on' OR
			$this->server('HTTPS') == 1 OR
			$this->server('SERVER_PORT') == 443
		) {
			return 'https';
		}

		return 'http';
	}

  // ---------------------------------------------------------------------------

	/**
	 * Return Referrer
	 *
	 * Method for returning the REFERRER.
	 *
	 * @author 	B. Kader
	 * @package CI Skeleton
	 *
	 * @access 	public
	 * @param 	string 	$default 	What to return if no referrer is found.
	 * @param 	bool 	$xss_clean 	Whether to apply XSS filtering
	 * @return 	string
	 */
	public function referrer($default = '', $xss_clean = NULL)
	{
		$referrer = $this->server('HTTP_REFERER', $xss_clean);
		return ($referrer) ? $referrer : $default;
	}

  // ---------------------------------------------------------------------------

	/**
	 * Return Query String
	 *
	 * Methods for returning the QUERY_STRING from $_SERVER array.
	 *
	 * @author 	B. Kader
	 * @package CI Skeleton
	 *
	 * @access 	public
	 * @param 	string 	$default 	What to return if nothing found.
	 * @param 	bool 	$xss_clean 	Whether to apply XSS filtering
	 * @return 	string
	 */
	public function query_string($default = '', $xss_clean = null)
	{
		$query_string = $this->server('QUERY_STRING', $xss_clean);
		return ($query_string) ? $query_string : $default;
	}

  // ---------------------------------------------------------------------------

	/**
	 * Validate POST Request
	 *
	 * Method for making sure the request is a POST request.
	 *
	 * @author 	B. Kader
	 * @package CI Skeleton
	 *
	 * @access 	public
	 * @param 	bool 	$xss_clean 	Whether to apply XSS filtering.
	 * @return 	bool 	true if it is a POST request, else false.
	 */
	public function is_post_request($xss_clean = NULL)
	{
		return ($this->server('REQUEST_METHOD', $xss_clean) === 'POST');
	}

	// ---------------------------------------------------------------------------

	/**
	 * Validate GET Request
	 *
	 * Method for making sure the request is a GET request.
	 *
	 * @author 	B. Kader
	 * @package CI Skeleton
	 *
	 * @access 	public
	 * @param 	bool 	$xss_clean 	Whether to apply XSS filtering.
	 * @return 	bool 	true if it is a GET request, else false.
	 */
	public function is_get_request($xss_clean = NULL)
	{
		return ($this->server('REQUEST_METHOD', $xss_clean) === 'GET');
	}

	// ---------------------------------------------------------------------------

	/**
	 * Validate HEAD Request
	 *
	 * Method for making sure the request is a HEAD request.
	 *
	 * @author 	B. Kader
	 * @package CI Skeleton
	 *
	 * @access 	public
	 * @param 	bool 	$xss_clean 	Whether to apply XSS filtering.
	 * @return 	bool 	true if it is a HEAD request, else false.
	 */
	public function is_head_request($xss_clean = NULL)
	{
		return ($this->server('REQUEST_METHOD', $xss_clean) === 'HEAD');
	}

	// ---------------------------------------------------------------------------

	/**
	 * Validate PUT Request
	 *
	 * Method for making sure the request is a PUT request.
	 *
	 * @author 	B. Kader
	 * @package CI Skeleton
	 *
	 * @access 	public
	 * @param 	bool 	$xss_clean 	Whether to apply XSS filtering.
	 * @return 	bool 	true if it is a PUT request, else false.
	 */
	public function is_put_request($xss_clean = NULL)
	{
		return ($this->server('REQUEST_METHOD', $xss_clean) === 'PUT');
	}

}
