<div id="loginbox">
    <h2><?php echo __('Login to panel') ?></h2>
    <form action="<?php echo URL::site('login/index') ?>" method="post">
        <div>
            <label for="username"><?php echo __('Username') ?>:</label>
            <input type="text" id="username" name="username" />
        </div>
        <div>
            <label for="password"><?php echo __('Password') ?>:</label>
            <input type="password" id="password" name="password" />
        </div>
        <div>
            <input type="submit" id="submit" style="width: auto" name="submit" value="<?php echo __('Submit') ?>" />
        </div>
    </form>
</div>