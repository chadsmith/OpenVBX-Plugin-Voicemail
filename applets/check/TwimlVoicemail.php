<?php
class TwimlVoicemail {
	protected $cookie_name;
	public $response;
	public $state;
	public $messages;

	public function __construct() {
		$ci =& get_instance();
		$this->response = new TwimlResponse;
		$this->cookie_name = 'state-' . AppletInstance::getInstanceId();
		$this->state = $ci->session->userdata($this->cookie_name);
		$this->messages = $ci->session->userdata($this->cookie_name . '_messages');
	}
	
	public function respond() {
		$this->response->respond();
	}

	public function save_state() {
	  $ci =& get_instance();
		$state = $this->state;
		$messages = $this->messages;
		$ci->session->set_userdata($this->cookie_name, $state);
		$ci->session->set_userdata($this->cookie_name . '_messages', $messages);
	}

	public function set_state($state) {
	  $this->state = $state;
	}

	public function set_messages($messages) {
	  $this->messages = $messages;
	}
}
