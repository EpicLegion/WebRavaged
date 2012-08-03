<script type="text/javascript">
var BLACKOPS_LANGUAGE_VARS = {
    try_again: '<?php echo addslashes(__('Please try again')) ?>',
    msg_sent: '<?php echo addslashes(__('Message successfully sent')) ?>',
    kicked: '<?php echo addslashes(__('User kicked. Refresh player list')) ?>',
    banned: '<?php echo addslashes(__('User banned. Refresh player list')) ?>'
}
</script>
<div id="leftblock">
    <?php echo $navigation        /* views/dashboard/navigation.php */ ?>
</div>
<div class="detail-window">
    <h2><?php echo __('Details') ?> <a style="cursor: pointer" onclick="$(this).parent().parent().hide('slow')">[<?php echo __('close window') ?>]</a></h2>
    <h3><?php echo __('IP addresses') ?></h3>
    <ul id="detail-ip-addresses">
    </ul>
    <h3><?php echo __('Player names') ?></h3>
    <ul id="detail-names">
    </ul>
</div>
<div id="rightblock">
    <h2><?php echo __('Player log') ?></h2>
    <script type="text/javascript" src="<?php echo URL::base() ?>jquery.uitablefilter.js"></script>
    <script type="text/javascript">
    	$(window).scroll(function(){
			$('.detail-window').css('top', (100+$(window).scrollTop()));
        });
        $(function() {
            var $t = $('table');

            $t.find('th').each(function(index){
                var column = $(this).text();
                if ( index == 0 || index == 2 || index == 3 ) {
                    var $search = $('<img src="images/magnifier-small.png" />')
                        .css({'vertical-align':'middle', 'cursor':'pointer'})
                        .click(function() {
                            $filter.toggle();
                        });
                    var $filter = $('<input type="text" size="10"/>')
                        .css({'display':'none', 'font-size':'10px'})
                        .keyup(function() {
                            $.uiTableFilter( $t, this.value, column );
                            $('#nvisible').text($t.find('tbody > tr:visible').size());
                        });
                    $(this).append($search).append('<br>').append($filter);
                }
            });
        });
    </script>
    <table cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th><?php echo __('GUID') ?></th>
                <th><?php echo __('Last scan') ?></th>
                <th><?php echo __('Last name') ?></th>
                <th><?php echo __('Last IP') ?></th>
                <th><?php echo __('Details') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($logs as $log): ?>
            <?php
                $log['names'] = empty($log['names']) ? array() : unserialize($log['names']);
                $log['ip_addresses'] = empty($log['ip_addresses']) ? array() : unserialize($log['ip_addresses']);
            ?>
            <tr>
                <td><?php echo $log['id'] ?></td>
                <td><?php echo date('d.m.Y H:i', (int) $log['last_update']) ?>
                </td>
                <td>
                    <?php echo (is_array($log['names']) AND count($log['names'])) ? strip_tags(end($log['names'])) : ''; ?>
                </td>
                <td>
                    <?php echo (is_array($log['ip_addresses']) AND count($log['ip_addresses'])) ? strip_tags(end($log['ip_addresses'])) : ''; ?>
                </td>
                <td>
                    <a class="button" onclick="rconPlayerDetails(<?php echo $log['id'] ?>)"><?php echo __('Details') ?></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php echo $pagination ?>
    <h2><?php echo __('Filters') ?></h2>
    <div class="group">
        <form action="index.php/dashboard/logs" method="post">
            <div class="content">
                <div>
                    <label for="guid"><?php echo __('GUID') ?>:</label>
                    <input type="text" name="guid" id="guid" value="<?php echo $conditions['guid'] ? (int) $conditions['guid'] : '' ?>" />
                </div>
                <div>
                    <input type="submit" name="submit" style="width: auto" value="<?php echo __('Submit') ?>" />
                </div>
            </div>
        </form>
    </div>
</div>