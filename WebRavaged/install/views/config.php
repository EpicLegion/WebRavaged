<h2>System configuration</h2>
<?php if($message): ?>
<div class="notice"><?php echo $message ?></div>
<?php endif; ?>
<form action="index.php" method="post">
<div class="group">
    <div class="content">
        <div>
            <label for="path">Cookie path:<br /><small>Default is usually correct</small></label>
            <input type="text" name="path" id="path" value="<?php echo $path ?>" />
        </div>
        <div>
            <label for="driver">Database driver:</label>
            <select name="driver" id="driver">
                <option selected="selected" value="mysql">MySQL</option>
                <option value="pgsql">PostgreSQL</option>
            </select>
        </div>
        <div>
            <label for="host">Database host:<br /><small>Usually localhost</small></label>
            <input type="text" name="host" id="host" value="localhost" />
        </div>
        <div>
            <label for="user">Database username:</label>
            <input type="text" name="user" id="user" value="" />
        </div>
        <div>
            <label for="password">Database password:</label>
            <input type="text" name="password" id="password" value="" />
        </div>
        <div>
            <label for="database">Database name:</label>
            <input type="text" name="database" id="database" value="" />
        </div>
        <div>
            <label for="prefix">Table prefix:</label>
            <input type="text" name="prefix" id="prefix" value="blackops_" />
        </div>
        <div>
            <input type="submit" name="submit" id="submit" style="width: auto" value="Submit" />
        </div>
    </div>
</div>
</form>
