 <h2><?php echo __('Edit user') ?></h2>
<div class="group">
    <div class="content">
        <form action="<?php echo URL::site('users/edit/'.$user->id) ?>" method="post">
            <div>
                <label><?php echo __('Username') ?>:</label>
                <?php echo $user->username ?>
            </div>
            <div>
                <label><?php echo __('New password') ?>:<br /><small><?php echo __('Empty = no change') ?></small></label>
                <input type="password" name="password" value="" />
            </div>
            <div>
                <label><?php echo __('Allow log management') ?>:</label>
                <input style="width: auto" type="checkbox" name="can_log" value="1"<?php if($can_log): ?> checked="checked"<?php endif;?> />
            </div>
            <div>
                <label><?php echo __('Allow servers management') ?>:</label>
                <input style="width: auto" type="checkbox" name="can_servers" value="1"<?php if($can_servers): ?> checked="checked"<?php endif;?> />
            </div>
            <div>
                <label><?php echo __('Allow users management') ?>:</label>
                <input style="width: auto" type="checkbox" name="can_users" value="1"<?php if($can_users): ?> checked="checked"<?php endif;?> />
            </div>
            <div>
                <input style="width: auto" type="submit" name="submit" value="<?php echo __('Apply') ?>" />
            </div>
        </form>
    </div>
</div>