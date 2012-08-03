<div id="leftblock">
<h2><?php echo __('Navigation') ?></h2>
<ul class="leftmenu">
    <li class="active" style="background-image: url(images/edit.png)"><a><?php echo __('Settings') ?></a></li>
</ul>
</div>
<div id="rightblock">
<h2><?php echo __('Personal settings') ?></h2>
<h2><?php echo __('Change password') ?></h2>
<div class="group">
    <div class="content">
        <form action="<?php echo URL::site('personal/update_password') ?>" method="post">
            <div>
                <label><?php echo __('Current password') ?>:</label>
                <input type="password" name="current_password" value="" />
            </div>
            <div>
                <label><?php echo __('New password') ?>:</label>
                <input type="password" name="password" value="" />
            </div>
            <div>
                <label><?php echo __('Confirm new password') ?>:</label>
                <input type="password" name="confirm_password" value="" />
            </div>
            <div>
                <input style="width: auto" type="submit" name="submit" value="<?php echo __('Submit') ?>" />
            </div>
        </form>
    </div>
</div>
</div>