<?php
class TwimlVoicemail {
	protected $cookie_name;
	protected $digits;
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

  public function parse_menu() {
    $ci =& get_instance();
    $this->digits = clean_digits($ci->input->get_post('Digits'));
    if($this->digits === false)
      return false;
    switch($this->digits):
      case '5':
        $this->menu_timestamp();
        break;
      case '7':
        $this->menu_archive_message();
        break;
      case '8':
        $this->menu_return_call();
        break;
      case '9':
        $this->menu_save_message();
        break;
      case '*':
        $this->menu_exit();
        break;
      case '':
        $this->menu_skip_message();
        break;
    endswitch;
    $this->save_state();
    $this->response->redirect();
    return true;
  }

	protected function menu_timestamp() {
	  $this->response->say(date('l, F jS, Y \a\t g:ia', strtotime($this->messages[0]->created) + date('Z')));
	}

	protected function menu_archive_message() {
  	$ci =& get_instance();
    $ci->load->model('vbx_message');
    $ci->vbx_message->archive($this->messages[0]->id, $ci->tenant->id, true);
    array_shift($this->messages);
    $this->response->say('Message deleted.');
	}

	protected function menu_return_call() {
  	$ci =& get_instance();
    $ci->load->model('vbx_message');
    $ci->vbx_message->mark_read($this->messages[0]->id, $ci->tenant->id);
    $this->response->dial($this->messages[0]->caller, array(
      'callerId' => $this->messages[0]->called
    ));
    array_shift($this->messages);
	}

	protected function menu_save_message() {
  	$ci =& get_instance();
    $ci->load->model('vbx_message');
    $ci->vbx_message->mark_read($this->messages[0]->id, $ci->tenant->id);
    array_shift($this->messages);
    $this->response->say('Message saved.');
	}

	protected function menu_exit() {
    $this->response->say('Goodbye.');
    $this->set_state('exit');
	}

	protected function menu_skip_message() {
    array_shift($this->messages);
	}

  public function parse_state() {
    switch($this->state):
      case 'status':
        $this->voicemail_status();
        break;
      case 'exit':
        $this->voicemail_exit();
        break;
      case 'queue':
        $this->voicemail_queue();
        break;
      default:
        return false;
    endswitch;
    return true;
  }

  protected function voicemail_status() {
    $new_messages = 0;
    $saved_messages = 0;
    foreach($this->messages as $message)
      if($message->status == 'new')
        $new_messages++;
      else
        $saved_messages++;
    if($new_messages || $saved_messages):
      if($new_messages && $saved_messages)
        $this->response->say(sprintf('You have %s new message%s and %d saved message%s.', $new_messages, $new_messages != 1 ? 's' : '', $saved_messages, $saved_messages != 1 ? 's' : ''));
      elseif($new_messages) 
        $this->response->say(sprintf('You have %d new message%s.', $new_messages, $new_messages != 1 ? 's' : ''));
      else
        $this->response->say(sprintf('You have %d saved message%s.', $saved_messages, $saved_messages != 1 ? 's' : ''));
      $this->set_state('queue');
    else:
      $this->response->say('You have no messages.');
      $this->set_state('exit');
    endif;
    $this->save_state();
    $this->response->redirect();
  }

  protected function voicemail_exit() {
    $this->set_state(null);
    $this->save_state();
    $next = AppletInstance::getDropZoneUrl('next');
    if(!empty($next))
      $this->response->redirect($next);
    else
      $this->response->hangup();
  }

  protected function voicemail_queue() {
    if(count($this->messages)):
      $message = $this->messages[0];
      $gather = $this->response->gather(array('numDigits' => 1));
      $gather->say(sprintf('%s message.', $message->status == 'new' ? 'New' : 'Saved'));
      $gather->play($message->content_url);
      $gather->say('To delete, press 7. To save, press 9.');
    else:
      $this->response->say('You have no more messages.');
      $this->set_state('exit');
      $this->save_state();
      $this->response->redirect();
    endif;
  }
}