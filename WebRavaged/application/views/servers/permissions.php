<div id="leftblock">
<h2><?php echo __('Navigation') ?></h2>
<ul class="leftmenu">
    <li style="background-image: url(images/edit.png)"><a href="<?php echo URL::site('servers/index') ?>"><?php echo __('Servers') ?></a></li>

    <li class="active" style="background-image: url(images/gear.png)"><a><?php echo __('Permissions') ?></a></li>

    <li style="background-image: url(images/lists.png)"><a href="<?php echo URL::site('servers/templates') ?>"><?php echo __('Permission templates') ?></a></li>
</ul>
</div>
<div id="rightblock">
<h2><?php echo __('Permissions') ?></h2>
<table cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <td><?php echo __('User') ?></td>
            <td><?php echo __('Server') ?></td>
            <td><?php echo __('Permission bitset') ?></td>
            <td><?php echo __('Actions') ?></td>
        </tr>
    </thead>
    <tbody>
        <?php foreach($list as $s): ?>
        <?php $permissions = (int) $s['permissions'] ?>
        <tr>
            <td><?php echo $users[$s['user_id']] ?></td>
            <td><?php echo $servers[$s['server_id']] ?></td>
            <td>
                <?php echo $permissions ?>
            </td>
            <td>
                <a href="<?php echo URL::site('servers/permissions_edit/'.$s['user_id'].'/'.$s['server_id']) ?>" class="button" style="background-image: url(images/edit.png)"><?php echo __('Edit') ?></a>
                <a href="<?php echo URL::site('servers/permissions_delete/'.$s['user_id'].'/'.$s['server_id']) ?>" class="button" style="background-image: url(images/delete.png)"><?php echo __('Delete') ?></a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
$servers['0'] = '---';
?>
<h2><?php echo __('Assign new permissions') ?></h2>
<div class="group">
    <div class="content">
        <form action="<?php echo URL::site('servers/permissions') ?>" method="post">
            <div>
                <label><?php echo __('User') ?>:</label>
                <?php echo Form::select('user_id', $users) ?>
            </div>
            <div>
                <label><?php echo __('Server') ?>:</label>
                <?php echo Form::select('server_id', $servers, '0', array('onchange' => 'rconPermFields(this);rconTemplates(this)')) ?>
            </div>
            <div>
                <label><?php echo __('Permission template') ?>:<br /><small><?php echo __('Overrides permissions') ?></small></label>
                <select name="template" id="templates-container">
                    <option value="0">---</option>
                </select>
            </div>
            <div id="permission-fields">
            </div>
            <div>
                <input style="width: auto" type="submit" name="submit" value="<?php echo __('Add/Apply') ?>" />
            </div>
        </form>
    </div>
</div>
</div>