<?php
include_once('TwimlVoicemail.php');

$ci =& get_instance();
$ci->load->model('vbx_message');
$vm = new TwimlVoicemail();
$digits = clean_digits($ci->input->get_post('Digits'));

if($digits !== false):
  if($digits == '7'):
    $ci->vbx_message->archive($vm->messages[0]->id, $ci->tenant->id, true);
    array_shift($vm->messages);
    $vm->response->say('Message deleted.');
  elseif($digits == '9'):
    $ci->vbx_message->mark_read($vm->messages[0]->id, $ci->tenant->id);
    array_shift($vm->messages);
    $vm->response->say('Message saved.');
  elseif($digits == '4'):
    $vm->response->say('Goodbye.');
    $vm->set_state('exit');
  endif;
  $vm->save_state();
  $vm->response->redirect();
else:
  switch($vm->state):
    case 'status':
      $new_messages = array();
      $saved_messages = array();
      foreach($vm->messages as $message)
        if($message->status == 'new')
          $new_messages[] = $message;
        else
          $saved_messages[] = $message;
      $num_new_messages = count($new_messages);
      $num_saved_messages = count($saved_messages);
      if($num_new_messages || $num_saved_messages):
        if($num_new_messages && $num_saved_messages)
          $vm->response->say(sprintf('You have %s new message%s and %d saved message%s.', $num_new_messages, $num_new_messages != 1 ? 's' : '', $num_saved_messages, $num_saved_messages != 1 ? 's' : ''));
        elseif($num_new_messages) 
          $vm->response->say(sprintf('You have %d new message%s.', $num_new_messages, $num_new_messages != 1 ? 's' : ''));
        else
          $vm->response->say(sprintf('You have %d saved message%s.', $num_saved_messages, $num_saved_messages != 1 ? 's' : ''));
        $vm->set_state('queue');
      else:
        $vm->response->say('You have no messages.');
        $vm->set_state('exit');
      endif;
      $vm->save_state();
      $vm->response->redirect();
      break;
    case 'exit':
      $vm->set_state(null);
      $vm->save_state();
      $next = AppletInstance::getDropZoneUrl('next');
      if(!empty($next))
	      $vm->response->redirect($next);
	    else
	      $vm->response->hangup();
      break;
    case 'queue':
      if(count($vm->messages)):
        $message = $vm->messages[0];
        $gather = $vm->response->gather(array('numDigits' => 1));
        $gather->say(sprintf('%s message.', $message->status == 'new' ? 'New' : 'Saved'));
        $gather->play($message->content_url);
        $gather->say('To delete, press 7. To save, press 9.');
      else:
        $vm->response->say('You have no more messages.');
        $vm->set_state('exit');
        $vm->save_state();
        $vm->response->redirect();
      endif;
      break;
    default:
      $user_or_group = AppletInstance::getUserGroupPickerValue('user_or_group');
      if(get_class($user_or_group) == 'VBX_User')
        $type = 'user';
      else
        $type = 'group';
      $query = array();
      $query[$type] = $user_or_group->values->id;
      $messages = $ci->vbx_message->get_messages($query);
      $vm->set_messages($messages['messages']);
      $vm->set_state('status');
      $vm->save_state();
      $vm->response->redirect();
  endswitch;
endif;

$vm->respond();
