<div id="leftblock">
    <?php echo $navigation        /* views/dashboard/navigation.php */ ?>
</div>
<div id="rightblock">
    <h2><?php echo __('Playlist edit') ?></h2>
<div class="group">
        <div class="content">
            <form action="<?php echo URL::site('dashboard/playlist_edit/'.$playlist['id']) ?>" method="post" onsubmit="return validate(this);">
                <?php $count = 1; ?>
                <?php foreach ( $playlist['playlists'] as $pls ): ?>
                <div class="gametype">
                    <label><span class="gtnumber"><?php echo $count++ ?></span>.</label>
                    <?php echo Form::select('playlists_ids[]', $grouped_playlists, $pls['playlist_id'])?>
                </div>
                <?php endforeach; ?>
                <?php if ( empty($playlist['playlists']) ): ?>
                <div id="gt_selector" style="display: none;">
                    <label><span class="gtnumber">1</span>.</label>
                    <?php echo Form::select('playlists_ids[]', $grouped_playlists)?>
                </div>
                <?php endif;?>
                <div id="add1more"><a href="javascript:addAnotherPlaylist()"><?php echo __('Add one more...') ?></a></div>
                <div>
                    <label><?php echo __('Playlist name') ?>:</label>
                    <input type="text" name="playlist_name" value="<?php echo HTML::chars($playlist['name']) ?>" />
                </div>
                <div>
                    <input style="width: auto" type="submit" name="submit" value="<?php echo __('Save') ?>" />
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
function addAnotherPlaylist()
{
    var $gametypes = $('div.gametype');
    var count = $gametypes.size();

    if ( count == 0 )
    {
        $('#gt_selector').addClass('gametype');
        $('#gt_selector').show();
    }
    else
    {
        if ( count == 1 )
        {
            $gametypes.append('<a href="#" onclick="removePlaylist(this); return false;" ><?php echo __('Remove') ?></a>');
        }

        var $new_gametype = $gametypes.first().clone();
        $('.gtnumber',$new_gametype).text(count+1);

        $('#add1more').before($new_gametype);
    }
}
function removePlaylist(a)
{
    $(a).parent().remove();

    var $gametypes = $('div.gametype');
    var count = $gametypes.size();

    // reindex gametypes
    $gametypes.each(function(index) {
        $('.gtnumber', this).text(index+1);
      });

    if ( count == 1 )
    {
        $gametypes.find('a').remove();
    }
}
function validate(form)
{
    var $selects = $('div.gametype select');

    // TODO remind a user if he chose same gametypes

    return true;
}
$(function(){
    if ( $('div.gametype').size() > 1 )
    {
        $('div.gametype').append('<a href="#" onclick="removePlaylist(this); return false;" ><?php echo __('Remove') ?></a>');
    }
});
</script>