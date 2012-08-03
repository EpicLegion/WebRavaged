<h2><?php echo __('Edit server') ?></h2>
<div class="group">
    <div class="content">
        <form action="<?php echo URL::site('servers/edit/'.$server->id) ?>" method="post">
            <div>
                <label><?php echo __('Server name') ?>:</label>
                <input type="text" name="name" value="<?php echo $server->name ?>" />
            </div>
            <div>
                <label><?php echo __('IP') ?>:</label>
                <input type="text" name="ip" value="<?php echo $server->ip ?>" />
            </div>
            <div>
                <label><?php echo __('Port') ?>:</label>
                <input type="text" name="port" value="<?php echo $server->port ?>" />
            </div>
            <div>
                <label><?php echo __('RCon password') ?>:</label>
                <input type="text" name="password" value="<?php echo $server->password ?>" />
            </div>
            <div>
                <label><?php echo __('Game') ?>:</label>
                <?php echo Form::select('game', $games, $server->game) ?>
            </div>
            <div>
                <label><?php echo __('Log URL') ?>:</label>
                <input type="text" name="log_url" value="<?php echo $server->log_url ?>" />
            </div>
            <div>
                <input style="width: auto" type="submit" name="submit" value="<?php echo __('Apply') ?>" />
            </div>
        </form>
    </div>
</div>