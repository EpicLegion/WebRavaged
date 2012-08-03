<script type="text/javascript">
var BLACKOPS_LANGUAGE_VARS = {
	try_again: '<?php echo addslashes(__('Please try again')) ?>',
    msg_sent: '<?php echo addslashes(__('Message successfully sent')) ?>',
    kicked: '<?php echo addslashes(__('User kicked. Refresh player list')) ?>',
    banned: '<?php echo addslashes(__('User banned. Refresh player list')) ?>'
}
</script>
<div id="leftblock">
    <?php echo $navigation        /* views/dashboard/navigation.php */ ?>
</div>
<div id="rightblock">
<h2><?php echo __('Server info') ?></h2>
<div id="refreshcontainer">
<?php if($server_info['error']): ?>
<div class="message error">
    <?php echo HTML::chars($server_info['error']) ?>
</div>
<?php endif; ?>
<?php if(!empty($server_info['colored_hostname']) AND !empty($server_info['sv_maxclients'])): ?>
<div class="badge">
<span><?php echo $server_info['colored_hostname'] ?></span><br />
<span><?php echo $server_info['ip'], ':', $server_info['port'] ?></span><br />
<span><?php echo $server_info['playlist_name'], " ({$server_info['playlist']})" ?></span><br />
<span><?php /*echo $server_info['gametype_name']*/ echo $server_info['gametype_abbrev'], '  @ ', $server_info['map_name'], " ({$server_info['map']})" ?></span><br />
<span><?php echo $server_info['count_players'], '/', $server_info['sv_maxclients'] ?></span>
</div>
<?php endif; ?>
<?php if(($permissions & SERVER_KICK) OR ($permissions & SERVER_BAN) OR ($permissions & SERVER_TEMP_BAN)): ?>
<div class="group">
    <div class="content">
        <label for="kick-reason">
            <?php echo __('Kick/ban reason') ?>:
            <?php if (Config::get('general.kick_reason_required', FALSE)): ?>
            <br /><small><?php echo __('Required') ?></small>
            <?php endif; ?>
        </label>
        <?php if(!count(Config::get('kick_reasons', array()))): ?>
        <input type="text" name="kick-reason" id="kick-reason" />
        <?php else: ?>
        <select name="kick-reason" id="kick-reason">
            <?php foreach(Config::get('kick_reasons', array()) as $v): ?>
            <option value="<?php echo HTML::chars($v) ?>"><?php echo HTML::chars($v) ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<h3><?php echo __('First team'), ' (', count($first_team), ')' ?></h3>
<table cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <td style="width: 5%"><?php echo __('ID') ?></td>
            <td style="width: 25%"><?php echo __('Name') ?></td>
            <td style="width: 5%"><?php echo __('Ping') ?></td>
            <td style="width: 15%"><?php echo __('IP') ?></td>
            <td style="width: 5%"><?php echo __('Score') ?></td>
            <td style="width: 15%"><?php echo __('GUID') ?></td>
            <td><?php echo __('Actions') ?></td>
        </tr>
    </thead>
    <tbody>
        <?php foreach($first_team as $player): ?>
        <tr>
            <td><?php echo (int) $player['id']?></td>
            <td><?php echo HTML::chars($player['name']) ?></td>
            <td><?php echo (int) $player['ping'] ?></td>
            <td><?php echo HTML::chars($player['ip']) ?></td>
            <td><?php echo (int) $player['score'] ?></td>
            <td><?php echo (int) $player['guid'] ?></td>
            <td>
                <?php if(($permissions & SERVER_KICK)): ?>
                <a class="button" style="background-image: url(images/kick.png)" onclick="rconKick(<?php echo (int) $player['id'] ?>)"><?php echo __('Kick') ?></a>
                <?php endif; ?>
                <?php if(($permissions & SERVER_BAN)): ?>
                <a class="button" style="background-image: url(images/banhammer.png)" onclick="rconBan(<?php echo (int) $player['id'] ?>)"><?php echo __('Ban') ?></a>
                <?php endif; ?>
                <?php if(($permissions & SERVER_TEMP_BAN)): ?>
                <a class="button" style="background-image: url(images/tempban.png)" onclick="rconTempBan(<?php echo (int) $player['id'] ?>)"><?php echo __('Temp ban') ?></a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<h3><?php echo __('Second team'), ' (', count($second_team), ')' ?></h3>
<table cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <td style="width: 5%"><?php echo __('ID') ?></td>
            <td style="width: 25%"><?php echo __('Name') ?></td>
            <td style="width: 5%"><?php echo __('Ping') ?></td>
            <td style="width: 15%"><?php echo __('IP') ?></td>
            <td style="width: 5%"><?php echo __('Score') ?></td>
            <td style="width: 15%"><?php echo __('GUID') ?></td>
            <td><?php echo __('Actions') ?></td>
        </tr>
    </thead>
    <tbody>
        <?php foreach($second_team as $player): ?>
        <tr>
            <td><?php echo (int) $player['id']?></td>
            <td><?php echo HTML::chars($player['name']) ?></td>
            <td><?php echo (int) $player['ping'] ?></td>
            <td><?php echo HTML::chars($player['ip']) ?></td>
            <td><?php echo (int) $player['score'] ?></td>
            <td><?php echo (int) $player['guid'] ?></td>
            <td>
                <?php if(($permissions & SERVER_KICK)): ?>
                <a class="button" style="background-image: url(images/kick.png)" onclick="rconKick(<?php echo (int) $player['id'] ?>)"><?php echo __('Kick') ?></a>
                <?php endif; ?>
                <?php if(($permissions & SERVER_BAN)): ?>
                <a class="button" style="background-image: url(images/banhammer.png)" onclick="rconBan(<?php echo (int) $player['id'] ?>)"><?php echo __('Ban') ?></a>
                <?php endif; ?>
                <?php if(($permissions & SERVER_TEMP_BAN)): ?>
                <a class="button" style="background-image: url(images/tempban.png)" onclick="rconTempBan(<?php echo (int) $player['id'] ?>)"><?php echo __('Temp ban') ?></a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<h3><?php echo __('Spectators'), ' (', count($spectators), ')'  ?></h3>
<table cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <td style="width: 5%"><?php echo __('ID') ?></td>
            <td style="width: 25%"><?php echo __('Name') ?></td>
            <td style="width: 5%"><?php echo __('Ping') ?></td>
            <td style="width: 15%"><?php echo __('IP') ?></td>
            <td style="width: 5%"><?php echo __('Score') ?></td>
            <td style="width: 15%"><?php echo __('GUID') ?></td>
            <td><?php echo __('Actions') ?></td>
        </tr>
    </thead>
    <tbody>
        <?php foreach($spectators as $player): ?>
        <tr>
            <td><?php echo (int) $player['id']?></td>
            <td><?php echo HTML::chars($player['name']) ?></td>
            <td><?php echo (int) $player['ping'] ?></td>
            <td><?php echo HTML::chars($player['ip']) ?></td>
            <td><?php echo (int) $player['score'] ?></td>
            <td><?php echo (int) $player['guid'] ?></td>
            <td>
                <?php if(($permissions & SERVER_KICK)): ?>
                <a class="button" style="background-image: url(images/kick.png)" onclick="rconKick(<?php echo (int) $player['id'] ?>)"><?php echo __('Kick') ?></a>
                <?php endif; ?>
                <?php if(($permissions & SERVER_BAN)): ?>
                <a class="button" style="background-image: url(images/banhammer.png)" onclick="rconBan(<?php echo (int) $player['id'] ?>)"><?php echo __('Ban') ?></a>
                <?php endif; ?>
                <?php if(($permissions & SERVER_TEMP_BAN)): ?>
                <a class="button" style="background-image: url(images/tempban.png)" onclick="rconTempBan(<?php echo (int) $player['id'] ?>)"><?php echo __('Temp ban') ?></a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php if($permissions & SERVER_MESSAGE): ?>
<h2><?php echo __('Messages') ?></h2>
<div class="group">
    <div class="content">
        <div>
            <label for="msg-message"><?php echo __('Message') ?>:<br /><small><?php echo __('Use [linebreak] to send multi-line messages') ?></small></label>
            <?php if (!count(Config::get('messages', array()))): ?>
            <input type="text" name="msg-message" id="msg-message" />
            <?php else: ?>
            <select name="msg-message" id="msg-message">
                <?php foreach(Config::get('messages', array()) as $v): ?>
                <option value="<?php echo HTML::chars($v) ?>"><?php echo HTML::chars($v) ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
        </div>
        <div>
            <label for="msg-target"><?php echo __('Target') ?></label>
            <select name="msg-target" id="msg-target">
                <option value="all"><?php echo __('Global') ?></option>
                <?php foreach($server_info['players'] as $player): ?>
                <option value="<?php echo $player['id'] ?>"><?php echo Security::xss_clean($player['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <input type="button" onclick="rconMessage()" id="msg-submit" style="width: auto" name="msg-submit" value="<?php echo __('Send') ?>" />
        </div>
    </div>
</div>
<?php endif; ?>
</div>