<?php
  $type = AppletInstance::getValue('type', 'user');
  $groups = OpenVBX::getGroups();
  $group_id = AppletInstance::getValue('group', '0');
?>
<div class="vbx-applet vbx-applet-voicemail">
  <h2>Retrieve Voicemail</h2>
  <h3>Connect to voicemail if the caller is a:</h3>
  <br />
	<div class="radio-table">
		<table>
			<tr class="radio-table-row first <?php echo ($type == 'user') ? 'on' : 'off' ?>">
				<td class="radio-cell">
					<input type="radio" name="type" value="user" <?php echo $type == 'user' ? 'checked="checked"' : '' ?> />
				</td>
				<td class="content-cell">
					<h4>User</h4>
				</td>
			</tr>
<?php if(!empty($groups)): ?>
			<tr class="radio-table-row last <?php echo ($type == 'group') ? 'on' : 'off' ?>">
				<td class="radio-cell">
					<input type="radio" name="type" value="group" <?php echo $type == 'group' ? 'checked="checked"' : '' ?> />
				</td>
				<td class="content-cell">
					<h4>Group Member</h4>
					<div class="vbx-input-container">
  				  <select class="medium" name="group">
<?php foreach($groups as $group): ?>
              <option value="<?php echo $group->id; ?>"<?php echo $group->id == $group_id ? ' selected="selected" ' : ''; ?>><?php echo $group->name; ?></option>
<?php endforeach; ?>
            </select>
					</div>
				</td>
			</tr>
<?php endif; ?>
		</table>
	</div>
	<br />
	<div class="vbx-full-pane">
		<h2>On Exit</h2>
<?php echo AppletUI::DropZone('next'); ?>
	</div>
	<div class="vbx-full-pane">
		<h2>Otherwise</h2>
<?php echo AppletUI::DropZone('fail'); ?>
	</div>
</div>