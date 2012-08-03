<div id="leftblock">
<h2><?php echo __('Navigation') ?></h2>
<ul class="leftmenu">
    <li style="background-image: url(images/edit.png)"><a href="<?php echo URL::site('servers/index') ?>"><?php echo __('Servers') ?></a></li>
    <li style="background-image: url(images/gear.png)"><a href="<?php echo URL::site('servers/permissions') ?>"><?php echo __('Permissions') ?></a></li>
    <li class="active" style="background-image: url(images/lists.png)"><a><?php echo __('Permission templates') ?></a></li>
</ul>
</div>
<div id="rightblock">
<h2><?php echo __('Permission templates') ?></h2>
<table cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <td><?php echo __('ID') ?></td>
            <td><?php echo __('Name') ?></td>
            <td><?php echo __('Game') ?></td>
            <td><?php echo __('Actions') ?></td>
        </tr>
    </thead>
    <tbody>
        <?php foreach($templates as $t): ?>
        <tr>
            <td><?php echo $t->id ?></td>
            <td><?php echo $t->name ?></td>
            <td><?php echo isset($games[$t->game]) ? $games[$t->game] : 'Unknown' ?></td>
            <td>
                <a href="<?php echo URL::site('servers/templates_edit/'.$t->id) ?>" class="button" style="background-image: url(images/edit.png)"><?php echo __('Edit') ?></a>
                <a href="<?php echo URL::site('servers/templates_delete/'.$t->id) ?>" class="button" style="background-image: url(images/delete.png)"><?php echo __('Delete') ?></a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php $games['0'] = '---'; ?>
<h2><?php echo __('Add template') ?></h2>
<div class="group">
    <div class="content">
        <form action="<?php echo URL::site('servers/templates') ?>" method="post">
            <div>
                <label><?php echo __('Template name') ?>:</label>
                <input type="text" name="name" value="" />
            </div>
            <div>
                <label><?php echo __('Game') ?>:</label>
                <?php echo Form::select('game', $games, '0', array('onchange' => 'rconPermFieldsGame(this)')) ?>
            </div>
            <div id="permission-fields">
            </div>
            <div>
                <input style="width: auto" type="submit" name="submit" value="<?php echo __('Add') ?>" />
            </div>
        </form>
    </div>
</div>
</div>