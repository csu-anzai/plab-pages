<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pages extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function _remap($method, $params = array())
	{
		if (method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), $params);
		} else {
			/*
			$folder = str_replace('_', '-', mb_strtolower($method));

			if (empty($params[0])) {
				$page = 'index';
			} else {
				$page = $params[0];
			}
			*/

			// If user visit pages via short url "p"
			if ($method == 'p') {
				empty($params[0]) && show_404();
				$method = $params[0];
				unset($params[0]);
				sort($params);
			}

			$method = str_replace('_', '-', mb_strtolower($method));

			// Load views file
			$file = $method.'.php';
			if (file_exists(VIEWPATH.$file)) {
				$this->load->view($file);
				return;
			}

			// Load views file from folder
			if (empty($params[0])) {
				$page = 'index';
			} else {
				$page = $params[0];
			}
			$file = $method.'/'.$page.'.php';
			file_exists(VIEWPATH.$file) OR show_404();
			
			$this->load->view($file);
		}
	}
}
