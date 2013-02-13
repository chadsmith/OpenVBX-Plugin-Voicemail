<?php
include_once(dirname(dirname(dirname(__FILE__))) . '/TwimlVoicemail.php');

$ci =& get_instance();
$vm = new TwimlVoicemail();

if(!$vm->parse_menu())
  if(!$vm->parse_state()) {
    $ci->load->model('vbx_message');
    $user_or_group = AppletInstance::getUserGroupPickerValue('user_or_group');
    if(get_class($user_or_group) == 'VBX_User')
      $type = 'user';
    else
      $type = 'group';
    $query = array();
    $query[$type] = array($user_or_group->values['id']);
    $messages = $ci->vbx_message->get_messages($query);
    $vm->set_messages($messages['messages']);
    $vm->set_state('status');
    $vm->save_state();
    $vm->response->redirect();
  }

$vm->respond();