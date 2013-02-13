<?php
include_once(dirname(dirname(dirname(__FILE__))) . '/TwimlVoicemail.php');

$ci =& get_instance();
$vm = new TwimlVoicemail();

if(!$vm->parse_menu())
  if(!$vm->parse_state()) {
    $ci->load->model('vbx_message');
    $type = AppletInstance::getValue('type', 'user');
    $id = AppletInstance::getValue('group', '0');
    $is_user = false;
    $is_member = false;
    $from_or_to = 'From';
    if(isset($_REQUEST['Direction']) && !in_array($_REQUEST['Direction'], array('inbound', 'incoming')))
      $from_or_to = 'To';
    if(!empty($_REQUEST[$from_or_to])) {
      $number = normalize_phone_to_E164($_REQUEST[$from_or_to]);
      $users = OpenVBX::getUsers();
      foreach($users as $user)
        foreach($user->devices as $device)
          if($number == $device->value) {
            $is_user = true;
            $caller = $user;
            break 2;
          }
      if($type == 'group' && $is_user) {
        $groups = OpenVBX::getGroups(array('id' => $id));
        if($group = current($groups))
          foreach($group->users as $user)
            if($user->id == $caller->id) {
              $is_member = true;
              break;
            }
      }
      if($is_member || ($type == 'user' && $is_user)) {
        $query = array();
        $query[$type] = $type == 'user' ? array($caller->id) : array($id);
        $messages = $ci->vbx_message->get_messages($query);
        $vm->set_messages($messages['messages']);
        $vm->set_state('status');
        $vm->save_state();
        $vm->response->redirect();
      }
    }
    if(empty($_REQUEST[$from_or_to]) || !$is_user || ($type == 'group' && !$is_member)) {
      $next = AppletInstance::getDropZoneUrl('fail');
      if(!empty($next))
        $vm->response->redirect($next);
      else
        $vm->response->hangup();
    }
  }

$vm->respond();