<h2><?php echo __('Edit permission template') ?></h2>
<div class="group">
    <div class="content">
        <form action="<?php echo URL::site('servers/templates_edit/'.$template->id) ?>" method="post">
            <div>
                <label><?php echo __('Template name') ?>:</label>
                <input type="text" name="name" value="<?php echo HTML::chars($template->name) ?>" />
            </div>
            <div>
                <label><?php echo __('Game') ?>:</label>
                <?php echo $game ?>
            </div>
            <?php echo $fields ?>
            <div>
                <input style="width: auto" type="submit" name="submit" value="<?php echo __('Submit') ?>" />
            </div>
        </form>
    </div>
</div>