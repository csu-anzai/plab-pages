<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * AJAX_Controller Class
 *
 * @package 	CodeIgniter
 * @subpackage 	Skeleton
 * @category 	Core Extension
 * @author 		Kader Bouyakoub <bkader[at]mail[dot]com>
 * @link 		https://goo.gl/wGXHO9
 * @copyright 	Copyright (c) 2018, Kader Bouyakoub (https://goo.gl/wGXHO9)
 * @since 		1.3.3
 * @version 	1.3.3
 */
class AJAX_Controller extends MY_Controller
{
	/**
	 * Array of methods that require a logged-in user and
	 * a safe URL with check.
	 * @var array
	 */
	protected $safe_methods = array();

	/**
	 * Array of methods that require a logged-in user of rank admin.
	 * @var array
	 */
	protected $admin_methods = array();

	/**
	 * Array of methods that require a logged-in user of rank admin and
	 * a safe URL check.
	 * @var array
	 */
	protected $safe_admin_methods = array();

	/**
	 * Used by AJAX methods to hold response details.
	 * @var object
	 */
	protected $response;

	/**
	 * List of HTTP status codes.
	 */
	const HTTP_OK                    = 200;
	const HTTP_CREATED               = 201;
	const HTTP_NO_CONTENT            = 204;
	const HTTP_NOT_MODIFIED          = 304;
	const HTTP_BAD_REQUEST           = 400;
	const HTTP_UNAUTHORIZED          = 401;
	const HTTP_FORBIDDEN             = 403;
	const HTTP_NOT_FOUND             = 404;
	const HTTP_METHOD_NOT_ALLOWED    = 405;
	const HTTP_NOT_ACCEPTABLE        = 406;
	const HTTP_CONFLICT              = 409;
	const HTTP_INTERNAL_SERVER_ERROR = 500;
	const HTTP_NOT_IMPLEMENTED       = 501;

	/**
	 * Array of most used HTTP status codes and their message.
	 * @var array
	 */
    protected $http_status_codes = array(
		self::HTTP_OK                    => 'OK',
		self::HTTP_CREATED               => 'CREATED',
		self::HTTP_NO_CONTENT            => 'NO CONTENT',
		self::HTTP_NOT_MODIFIED          => 'NOT MODIFIED',
		self::HTTP_BAD_REQUEST           => 'BAD REQUEST',
		self::HTTP_UNAUTHORIZED          => 'UNAUTHORIZED',
		self::HTTP_FORBIDDEN             => 'FORBIDDEN',
		self::HTTP_NOT_FOUND             => 'NOT FOUND',
		self::HTTP_METHOD_NOT_ALLOWED    => 'METHOD NOT ALLOWED',
		self::HTTP_NOT_ACCEPTABLE        => 'NOT ACCEPTABLE',
		self::HTTP_CONFLICT              => 'CONFLICT',
		self::HTTP_INTERNAL_SERVER_ERROR => 'INTERNAL SERVER ERROR',
		self::HTTP_NOT_IMPLEMENTED       => 'NOT IMPLEMENTED'
    );

	// ------------------------------------------------------------------------

	/**
	 * Class constructor.
	 * @access 	public
	 * @return 	void
	 */
	public function __construct()
	{
		parent::__construct();

		// We make sure the request is AJAX.
		if ( ! $this->input->is_ajax_request()) {
			show_404();
		}

		// Prepare $response property object.
		$this->response          = new stdClass();
		$this->response->header  = self::HTTP_BAD_REQUEST;
		$this->response->message = '';
		$this->response->scripts = array();
		$this->response->results = array();
	}

	// ------------------------------------------------------------------------

	/**
	 * Method for catching called methods to check safety and integrity.
	 * @access 	public
	 * @param 	string 	$method 	The requested method.
	 * @param 	array 	$params 	Arguments to pass to the method.
	 * @return 	AJAX_Controller::response().
	 */
	public function _remap($method, $params = array())
	{
		// The method does not exist?
		if ( ! method_exists($this, $method))
		{
			$this->response->header  = self::HTTP_UNAUTHORIZED;
			return $this->response();
		}

		/**
		 * If the method is present in both $safe_methods and
		 * $admin_methods array we make sure to automatically
		 * add it to $safe_admin_methods array.
		 */
		if (in_array($method, $this->safe_methods)
			&& in_array($method, $this->admin_methods))
		{
			$this->safe_admin_methods[] = $method;
		}

		/**
		 * The reason behind this is that sometime we don't need to create
		 * the referrer field. So we see if one is provided. If it is not,
		 * we simply check the nonce without referrer.
		 */
		$referrer = $this->input->request('_plab_http_referrer');
		$nonce_status = (null !== $referrer)
			? $this->check_nonce()
			: $this->check_nonce(null, false);

		// Does the requested methods require a safety check?
		if (in_array($method, $this->safe_methods)
			&& (true !== $nonce_status )) //OR true !== $this->auth->online()))
		{
			$this->response->header  = self::HTTP_UNAUTHORIZED;
			$this->response->message = ''; //__('PLAB_ERROR_NONCE_URL');
		}

		// Does the method require an admin user?
		elseif (in_array($method, $this->admin_methods))
			//&& true !== $this->auth->is_admin())
		{
			$this->response->header  = self::HTTP_UNAUTHORIZED;
      $this->response->message = ''; //__('PLAB_ERROR_NONCE_URL');
		}

		// Does the method require an admin user AND a safety check?
		elseif (in_array($method, $this->safe_admin_methods)
			&& (true !== $nonce_status)) // OR true !== $this->auth->is_admin()))
		{
			$this->response->header  = self::HTTP_UNAUTHORIZED;
      $this->response->message = ''; //__('PLAB_ERROR_NONCE_URL');
		}
		// Otherwise, call the method.
		else
		{
			call_user_func_array(array($this, $method), $params);
		}

		$this->response->results['_CSRF'] = [
			'name' => $this->security->get_csrf_token_name(),
			'token' => $this->security->get_csrf_hash(),
		];

		// Always return the final response.
		return $this->response();
	}

	// ------------------------------------------------------------------------

	/**
	 * This method handles the rest of AJAX requests.
	 *
	 * @access 	public
	 * @param 	none
	 * @return 	string
	 */
	protected function response()
	{
		/**
		 * Disable parsing of the {elapsed_time} and {memory_usage}
		 * pseudo-variables because we don't need them.
		 */
		$this->output->parse_exec_vars = false;

		$response['code'] = $this->response->header;

		$response['status'] = (isset($this->response->status))
			? $this->response->status
			: $this->http_status_codes[$this->response->header];

		if (isset($this->response->message))
		{
			$response['message'] = $this->response->message;
		}

		if ( ! empty($this->response->scripts))
		{
			$response['scripts'] = $this->response->scripts;
		}
		if ( ! empty($this->response->results))
		{
			$response['results'] = $this->response->results;
		}

		// Return the final output.
		return $this->output
			->set_content_type('application/json', 'UTF-8')
			->set_status_header($this->response->header)
			->set_output(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	}

}
