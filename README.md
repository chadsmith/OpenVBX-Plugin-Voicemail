# Voicemail Retrieval for OpenVBX

This plugin allows you to call into your voicemail. Try it with my [Password][1] or [Routing][2] plugins for additional security.

[1]: https://github.com/chadsmith/OpenVBX-Plugin-Password
[2]: https://github.com/chadsmith/OpenVBX-Plugin-Routes

## Installation

[Download][3] the plugin and extract to /plugins

[3]: https://github.com/chadsmith/OpenVBX-Plugin-Voicemail/archives/master

## Usage

### Authenticated Retrieval

1. Add the Retrieve Voicemail applet to a Call flow
2. Select the mailbox type (user or group)
3. (Optional) Drop an applet for when the mailbox is exited
4. (Optional) Drop an applet for when the caller is not an OpenVBX user or a member of the selected group

### Unauthenticated Retrieval

1. Add the Retrieve Voicemail (unlocked) applet to a Call flow
2. Select the mailbox you want to call into
3. (Optional) Drop an applet for when the mailbox is exited

## Voicemail Menu

Use the following menu to manage your voicemail during or immediately after a message

1 - Replay

5 - Time Stamp

7 - Delete

8 - Call Return

9 - Save

\* - Exit

\# - Skip