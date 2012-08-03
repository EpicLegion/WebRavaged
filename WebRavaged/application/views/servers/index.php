<div id="leftblock">
<h2><?php echo __('Navigation') ?></h2>
<ul class="leftmenu">
    <li class="active" style="background-image: url(images/edit.png)"><a><?php echo __('Servers') ?></a></li>
    <li style="background-image: url(images/gear.png)"><a href="<?php echo URL::site('servers/permissions') ?>"><?php echo __('Permissions') ?></a></li>
    <li style="background-image: url(images/lists.png)"><a href="<?php echo URL::site('servers/templates') ?>"><?php echo __('Permission templates') ?></a></li>
</ul>
</div>
<div id="rightblock">
<h2><?php echo __('Servers') ?></h2>
<table cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <td style="width: 16px"></td>
            <td><?php echo __('ID') ?></td>
            <td><?php echo __('Server name') ?></td>
            <td><?php echo __('Host') ?></td>
            <td><?php echo __('Port') ?></td>
            <td><?php echo __('Game') ?></td>
            <td><?php echo __('Actions') ?></td>
        </tr>
    </thead>
    <tbody>
        <?php foreach($servers as $s): ?>
        <tr>
            <td><img src="images/games/<?php echo $s->game ?>.png" alt="" /></td>
            <td><?php echo $s->id ?></td>
            <td><?php echo $s->name ?></td>
            <td><?php echo $s->ip ?></td>
            <td><?php echo $s->port ?></td>
            <td><?php echo isset($games[$s->game]) ? $games[$s->game] : 'Unknown' ?></td>
            <td>
                <a href="<?php echo URL::site('servers/edit/'.$s->id) ?>" class="button" style="background-image: url(images/edit.png)"><?php echo __('Edit') ?></a>
                <a href="<?php echo URL::site('servers/delete/'.$s->id) ?>" class="button" style="background-image: url(images/delete.png)"><?php echo __('Delete') ?></a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<h2><?php echo __('Add server') ?></h2>
<div class="group">
    <div class="content">
        <form action="<?php echo URL::site('servers/index') ?>" method="post">
            <div>
                <label><?php echo __('Server name') ?>:</label>
                <input type="text" name="name" value="" />
            </div>
            <div>
                <label><?php echo __('Server IP') ?>:</label>
                <input type="text" name="ip" value="" />
            </div>
            <div>
                <label><?php echo __('Port') ?>:</label>
                <input type="text" name="port" value="" />
            </div>
            <div>
                <label><?php echo __('RCon password') ?>:</label>
                <input type="text" name="password" value="" />
            </div>
            <div>
                <label><?php echo __('Log URL') ?>:</label>
                <input type="text" name="log_url" value="" />
            </div>
            <div>
                <label><?php echo __('Game') ?>:</label>
                <?php echo Form::select('game', $games) ?>
            </div>
            <div>
                <input style="width: auto" type="submit" name="submit" value="<?php echo __('Add') ?>" />
            </div>
        </form>
    </div>
</div>
</div>