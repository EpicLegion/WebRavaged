<option value="0">---</option>
<?php foreach($templates as $t): ?>
<option value="<?php echo $t->id ?>"><?php echo HTML::chars($t->name) ?></option>
<?php endforeach; ?>