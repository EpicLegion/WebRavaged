<div id="leftblock">
    <h2><?php echo __('Navigation') ?></h2>
    <ul class="leftmenu">
        <li class="active"><a><?php echo __('Manage users') ?></a></li>
        <li style="background-image: url(images/log.png)"><a href="<?php echo URL::site('users/logs') ?>"><?php echo __('Actions log') ?></a></li>
    </ul>
</div>
<div id="rightblock">
<h2><?php echo __('Users') ?></h2>
<table cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <td><?php echo __('ID') ?></td>
            <td><?php echo __('Username') ?></td>
            <td><?php echo __('Email') ?></td>
            <td><?php echo __('Last login') ?></td>
            <td><?php echo __('Actions') ?></td>
        </tr>
    </thead>
    <tbody>
        <?php foreach($users as $u): ?>
        <tr>
            <td><?php echo $u->id ?></td>
            <td><?php echo strip_tags($u->username) ?></td>
            <td><?php echo strip_tags($u->email) ?></td>
            <td><?php echo date('Y-m-d H:i', $u->last_login) ?></td>
            <td>
                <a href="<?php echo URL::site('users/edit/'.$u->id) ?>" class="button" style="background-image: url(images/edit.png)"><?php echo __('Edit') ?></a>
                <a href="<?php echo URL::site('users/delete/'.$u->id) ?>" class="button" style="background-image: url(images/delete.png)"><?php echo __('Delete') ?></a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<h2><?php echo __('Add user') ?></h2>
<div class="group">
    <div class="content">
        <form action="<?php echo URL::site('users/index') ?>" method="post">
            <div>
                <label><?php echo __('Username') ?>:<br /><small><?php echo __('Unique. Minimum 4 chars, max 32 characters.') ?></small></label>
                <input type="text" name="username" value="" />
            </div>
            <div>
                <label><?php echo __('Password') ?>:<br /><small><?php echo __('Minimum 5 chars, max 42 chars.') ?></small></label>
                <input type="password" name="password" value="" />
            </div>
            <div>
                <label><?php echo __('Confirm password') ?>:</label>
                <input type="password" name="password_confirm" value="" />
            </div>
            <div>
                <label>Email:<br /><small><?php echo __('Unique and valid email address') ?></small></label>
                <input type="text" name="email" value="" />
            </div>
            <div>
                <label><?php echo __('Allow log management') ?>:</label>
                <input style="width: auto" type="checkbox" name="can_log" value="1" />
            </div>
            <div>
                <label><?php echo __('Allow servers management') ?>:</label>
                <input style="width: auto" type="checkbox" name="can_servers" value="1" />
            </div>
            <div>
                <label><?php echo __('Allow users management') ?>:</label>
                <input style="width: auto" type="checkbox" name="can_users" value="1" />
            </div>
            <div>
                <input style="width: auto" type="submit" name="submit" value="<?php echo __('Add') ?>" />
            </div>
        </form>
    </div>
</div>
</div>