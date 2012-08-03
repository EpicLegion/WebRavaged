<script type="text/javascript" src="<?php echo URL::base() ?>jquery-ui.js"></script>
<div id="leftblock">
    <?php echo $navigation        /* views/dashboard/navigation.php */ ?>
</div>
<div id="rightblock">
    <h2><?php echo __('Map management') ?></h2>
    <ul class="maplist" id="maplist">
    <?php foreach($rotation as $v): ?>
        <li id="<?php echo $v?>"<?php if(isset($excludes[$v])): ?> class="excluded"<?php endif; ?> style="background-image: url(images/maps/<?php echo $v ?>.jpg)">
            <h4><?php echo $maps[$v] ?><?php if($current_map == $v): ?> (<?php echo __('current') ?>)<?php endif; ?></h4>
            <a class="button force" href="<?php echo URL::site('dashboard/maps_change/'.$v) ?>"><?php echo __('Change to') ?></a>
            <a class="button force1" href="<?php echo URL::site('dashboard/maps_next/'.$v) ?>"><?php echo __('Set next') ?></a>
            <?php if(isset($excludes[$v])): ?>
            <a class="button include" href="<?php echo URL::site('dashboard/maps_status/'.$v) ?>"><?php echo __('Include') ?></a>
            <?php else: ?>
            <a class="button exclude" href="<?php echo URL::site('dashboard/maps_status/'.$v) ?>"><?php echo __('Exclude') ?></a>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
    </ul>
    <div class="group">
        <div class="content">
            <input type="submit" name="submit" style="width: auto" value="<?php echo __('Apply rotation') ?>" onclick="rconApplySorting()" />
        </div>
    </div>
    <h2><?php echo __('Map presets') ?></h2>
    <div class="group">
        <form action="index.php/dashboard/maps" method="post">
            <div class="content">
                <div>
                    <label for="preset"><?php echo __('Preset') ?>:</label>
                    <?php if (count($presets)): ?>
                    <select id="preset" name="preset">
                        <?php foreach($presets as $v): ?>
                        <option value="<?php echo $v['name'] ?>"><?php echo $v['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php else: ?>
                    <?php echo __('No available presets') ?>
                    <?php endif; ?>
                </div>
                <div>
                    <input type="submit" name="submit" value="<?php echo __('Apply') ?>" style="width: auto" />
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
    $('#maplist').sortable();
});
</script>