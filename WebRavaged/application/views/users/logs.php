<div id="leftblock">
     <h2><?php echo __('Navigation') ?></h2>
    <ul class="leftmenu">
        <li><a href="<?php echo URL::site('users/index') ?>"><?php echo __('Manage users') ?></a></li>
        <li class="active" style="background-image: url(images/log.png)"><a><?php echo __('Actions log') ?></a></li>
    </ul>
</div>
<div id="rightblock">
    <h2><?php echo __('Logs') ?></h2>
    <table cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <td><?php echo __('ID') ?></td>
                <td><?php echo __('Username') ?></td>
                <td><?php echo __('Date') ?></td>
                <td><?php echo __('IP') ?></td>
                <td><?php echo __('Actions') ?></td>
            </tr>
        </thead>
        <tbody>
            <?php foreach($logs as $l): ?>
            <tr>
                <td><?php echo $l['id'] ?></td>
                <td><?php echo strip_tags($l['username']) ?></td>
                <td><?php echo $l['date'] ?></td>
                <td><?php echo $l['ip'] ?></td>
                <td><?php echo strip_tags($l['content']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php echo $pagination ?>
    <h2><?php echo __('Filters') ?></h2>
    <div class="group">
        <form action="index.php/users/logs" method="post">
            <div class="content">
                <div>
                    <label for="user"><?php echo __('Username') ?>:</label>
                    <input type="text" name="user" id="user" value="<?php echo HTML::chars($conditions['user']) ?>" />
                </div>
                <div>
                    <label for="action"><?php echo __('Action') ?>:<br /><small><?php echo __('Contains') ?></small></label>
                    <input type="text" name="content" id="action" value="<?php echo HTML::chars($conditions['content']) ?>" />
                </div>
                <div>
                    <label for="ip"><?php echo __('IP') ?>:</label>
                    <input type="text" name="ip" id="ip" value="<?php echo HTML::chars($conditions['ip']) ?>" />
                </div>
                <div>
                    <label for="date_from"><?php echo __('Date') ?>:<br /><small><?php echo __('From - to') ?></small></label>
                    <input type="text" style="width: 200px" maxlength="19" name="date_from" id="date_from" value="<?php echo ($conditions['date_from'] ? date('Y-m-d H:i:s', $conditions['date_from']) : '') ?>" />
                    <input type="text" style="width: 200px" maxlength="19" name="date_to" id="date_to" value="<?php echo ($conditions['date_to'] ? date('Y-m-d H:i:s', $conditions['date_to']) : '') ?>" />
                </div>
                <div>
                    <input type="submit" name="submit" style="width: auto" value="<?php echo __('Submit') ?>" />
                </div>
            </div>
        </form>
    </div>
</div>