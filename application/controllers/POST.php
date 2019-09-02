<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class POST extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		if ( ! $this->input->is_post_request()) {
			redirect($this->input->referrer());
		}
	}

	public function free_name_card_20190709()
	{
		$success_redirect = 'free-name-card-20190709/thank-you';
		$err_redirect = 'free-name-card-20190709/try-again';

		$name = $this->input->post('name');
		$email = $this->input->post('email');
		$phone = $this->input->post('phone');
		$preferences = $this->input->post('preferences');

		$is_valid = true;

		$empty_tests = [$name, $email, $phone];
		foreach ($empty_tests as $test) {
			if (strlen(trim($test)) === 0) {
				$is_valid = false;
			}
		}

		$this->load->helper('email');
		valid_email($email) OR $is_valid = false;

		$this->load->helper('phone');
		valid_phone($phone) OR $is_valid = false;

		$files = $_FILES['files'];
		empty($files['name']) && $is_valid = false;

		if ($is_valid) {
			$member_db = $this->load->database('member', true);

			if ( ! empty($lead = $this->free_name_card_20190709__exist_phone($phone))) {
				$lead_id = $lead->lead_id;
			}

			if ( ! empty($lead = $this->free_name_card_20190709__exist_email($email))) {
				$lead_id = $lead->lead_id;
			}

			if (empty($lead_id)) {
				$saved = $member_db->insert('plab_leads', [
					'full_name' => $name,
					'email' => $email,
					'phone' => $phone,
					'source' => 'free name card 20190709',
				]);

				if ( ! $saved) {
					$this->session->set_flashdata('free_name_card_20190709__error', [
						'email' => $email,
						'error_code' => 'ERR_LEAD_NOT_SAVED'
					]);
					redirect($err_redirect);
				}

				$lead_id = $member_db->insert_id();

				$saved = $member_db->insert('plab_lead_meta', [
					'lead_id' => $lead_id,
					'data_key' => 'contact_preferences',
					'value' => $preferences,
				]);

				if ( ! $saved) {
					$this->session->set_flashdata('free_name_card_20190709__error', [
						'email' => $email,
						'error_code' => 'ERR_LEAD_META_NOT_SAVED'
					]);
					redirect($err_redirect);
				}
			}

			$upload_path = APPPATH.'uploads/free-name-card-20190709/leadID-'.$lead_id;

			is_dir(APPPATH.'uploads/free-name-card-20190709') OR mkdir(APPPATH.'uploads/free-name-card-20190709');
			is_dir($upload_path) OR mkdir($upload_path);

			$this->load->helper('directory');
			$existing_files = directory_map($upload_path);

			$num_files = count($files['name']);
			$uploaded_files = [];
			$err_files = [];

			for ($i = 0; $i < $num_files; $i++) {
				$_FILES['file']['name']     = $file_name = $_FILES['files']['name'][$i];
				$_FILES['file']['type']     = $_FILES['files']['type'][$i];
				$_FILES['file']['tmp_name'] = $_FILES['files']['tmp_name'][$i];
				$_FILES['file']['error']    = $_FILES['files']['error'][$i];
				$_FILES['file']['size']     = $_FILES['files']['size'][$i];

				$config['upload_path']          = $upload_path;
				$config['allowed_types']        = 'pdf|psd|ai|jpg|jpeg|png|ps|eps';
				$config['max_size']             = 30*1024; // mb to kb

				$this->load->library('upload', $config);

				if ($this->upload->do_upload('file')) {
					$uploaded_files[] = $file_name;
				} else {
					$err_files[] = $file_name;
				}
			}

			if (count($err_files) > 0) {
				$this->session->set_flashdata('free_name_card_20190709__error', [
					'email' => $email,
					'error_code' => 'ERR_FILE_UPLOAD_ERR'
				]);
				redirect($err_redirect);
			}

			redirect($success_redirect);
		} else {
			redirect($this->input->referrer());
		}
	}

	private function free_name_card_20190709__exist_phone($phone = '') {
		$raw_phone = $phone;

		// Remove all '+', dashes & whitespaces
		$raw_phone = preg_replace('[\+|\-|\s]', '', $raw_phone);
		// Remove prefixes (+60, 0060)
		$raw_phone = preg_replace('/^(\+60|\+6|60|0060)/', '', $raw_phone);
		// Remove leading zero
		$min_length = 9;
		if (strlen($raw_phone) > $min_length) {
			$raw_phone = preg_replace('[^0]', '', $raw_phone);
		}

		// Valid raw phone length should be 9 to 10 digits for Malaysian phone number format
		// e.g. 123456789 (9 digits), 1123456789 (10 digits)
		$max_length = 10;
		if (
			preg_match('/^\d+$/', $raw_phone) // Ensure all digits
			&& (strlen($raw_phone) >= $min_length && strlen($raw_phone) <= $max_length)
		) {
			$phone_match_1 = $raw_phone;
			$phone_match_1 = substr_replace($phone_match_1, '%', 2, 0);
			$phone_match_1 = substr_replace($phone_match_1, '%', 6, 0);
			$phone_match_1 = '%'.$phone_match_1.'%'; // Should produce %12%345%6789% (9 digits) OR %11%234%56789% (10 digits)

			$phone_match_2 = $raw_phone;
			$phone_match_2 = substr_replace($phone_match_2, '%', 2, 0);
			$phone_match_2 = substr_replace($phone_match_2, '%', 7, 0);
			$phone_match_2 = '%'.$phone_match_2.'%'; // Should produce %12%3456%789% (9 digits) OR %11%2345%6789% (10 digits)

			$member_db = $this->load->database('member', true);
			$exist_phone = $member_db->query("SELECT * FROM plab_leads WHERE source = 'free name card 20190709' AND (phone LIKE '".$phone_match_1."' OR phone LIKE '".$phone_match_2."')")
															->row();
		} else {
			$exist_phone = false;
		}

		return $exist_phone;
	}

	private function free_name_card_20190709__exist_email($email = '') {
		$member_db = $this->load->database('member', true);
		$exist_email = $member_db->where('email', $email)
														 ->where('source', 'free name card 20190709')
														 ->get('plab_leads')
														 ->row();

    return $exist_email;
	}
}
