<h2><?php echo __('Navigation') ?></h2>
<ul class="leftmenu">
    <?php if($action == 'index'): ?>
    <li class="active"><a><?php echo __('Players list') ?></a><img title="<?php echo __('Refresh player list') ?>" alt="Refrsh" src="images/refresh.png" onclick="rconRefresh()" /></li>
    <?php else: ?>
    <li><a href="<?php echo URL::site('dashboard/index') ?>"><?php echo __('Players list') ?></a></li>
    <?php endif; ?>
    <?php if($permissions & SERVER_USER_LOG): ?>
    <li<?php if($action == 'logs'): ?> class="active"<?php endif; ?> style="background-image: url(images/log.png)"><a href="<?php echo URL::site('dashboard/logs') ?>"><?php echo __('Player log') ?></a></li>
    <?php endif; ?>
    <?php if($permissions & SERVER_MESSAGE_ROTATION): ?>
    <li<?php if($action == 'msgrotation'): ?> class="active"<?php endif; ?> style="background-image: url(images/msg.png)"><a href="<?php echo URL::site('dashboard/msgrotation') ?>"><?php echo __('Message rotation') ?></a></li>
    <?php endif; ?>
    <?php if($permissions & SERVER_PLAYLIST): ?>
    <li<?php if($action == 'playlists'): ?> class="active"<?php endif; ?> style="background-image: url(images/lists.png)"><a href="<?php echo URL::site('dashboard/playlists') ?>"><?php echo __('Playlists') ?></a></li>
    <?php endif; ?>
    <?php if($permissions & SERVER_MAPS): ?>
    <li<?php if($action == 'maps'): ?> class="active"<?php endif; ?> style="background-image: url(images/map.png)"><a href="<?php echo URL::site('dashboard/maps') ?>"><?php echo __('Map management') ?></a></li>
    <?php endif; ?>
</ul>
<br />
<h2><?php echo __('Unranked only') ?></h2>
<ul class="leftmenu">
    <?php if($permissions & SERVER_FAST_RESTART): ?>
    <li style="background-image: url(images/refresh.png)"><a href="<?php echo URL::site('dashboard/fast_restart') ?>"><?php echo __('Fast restart') ?></a></li>
    <?php endif; ?>
    <?php if($permissions & SERVER_FAST_RESTART): ?>
    <li style="background-image: url(images/map.png)"><a href="<?php echo URL::site('dashboard/next_map') ?>"><?php echo __('Next map in rotation') ?></a></li>
    <?php endif; ?>
</ul>
<br />
<h2><?php echo __('Select server') ?></h2>
<ul class="leftmenu servers">
<?php foreach($owned as $serv): ?>
    <li<?php if($current_server_id == $serv['id']): ?> class="active"<?php endif; ?>><a href="<?php echo URL::site('dashboard/set_server/'.$serv['id'])?>"><?php echo HTML::chars($serv['name']) ?></a></li>
<?php endforeach; ?>
</ul>
