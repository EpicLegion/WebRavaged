<?php foreach($fields as $k => $v): ?>
<div>
    <label><?php echo __($v['title']) ?>:</label>
    <input type="checkbox" name="<?php echo $k ?>" style="width: auto" value="1"<?php if($v['bit'] & $current): ?> checked="checked"<?php endif; ?> />
</div>
<?php endforeach; ?>