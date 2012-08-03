<div id="leftblock">
    <?php echo $navigation        /* views/dashboard/navigation.php */ ?>
</div>
<div id="rightblock">
    <h2><?php echo __('Playlists') ?></h2>
    <table cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <td><?php echo __('Playlist name') ?></td>
                <td><?php echo __('Gametypes') ?></td>
                <td><?php echo __('Active') ?></td>
                <td><?php echo __('Actions') ?></td>
            </tr>
        </thead>
        <tbody>
            <?php foreach($playlists as $pls): ?>
            <tr>
                <td><?php echo HTML::chars($pls['name']) ?></td>
                <td class="gametypes"><?php
                    $gametypes_htmls = array();
                    foreach ($pls['gametypes'] as $gametype)
                    {
                        /* Keys are name (playlist name), abbrev (SD), mode (hardcore) */
                        $gametype_html = '<span class="'.HTML::chars($gametype['mode']).'" title="'.HTML::chars($gametype['name']).'">'.HTML::chars($gametype['abbrev']).'</span>';
                        $gametypes_htmls[] = $gametype_html;
                    }
                    echo join('/', $gametypes_htmls);
                ?></td>
                <td><?php echo $pls['is_active'] ? __('Yes') : __('No') ?></td>
                <td>
                    <a href="<?php echo URL::site('dashboard/playlist_edit/'.$pls['id']) ?>" class="button" style="background-image: url(images/edit.png)"><?php echo __('Edit') ?></a>
                    <a href="<?php echo URL::site('dashboard/playlist_delete/'.$pls['id']) ?>" class="button" style="background-image: url(images/delete.png)"><?php echo __('Delete') ?></a>
                    <?php if ($pls['is_active']): ?>
                    <a href="<?php echo URL::site('dashboard/playlists/'.$pls['id'].'/0') ?>" class="button" style="background-image: url(images/off.png)"><?php echo __('Turn OFF') ?></a>
                    <?php else: ?>
                    <a href="<?php echo URL::site('dashboard/playlists/'.$pls['id'].'/1') ?>" class="button" style="background-image: url(images/on.png)"><?php echo __('Turn ON') ?></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h2><?php echo __('Create new playlist') ?></h2>
    <div class="group">
        <div class="content">
            <form action="<?php echo URL::site('dashboard/playlists') ?>" method="post" onsubmit="return validate(this);">
                <div class="gametype">
                    <label><span class="gtnumber">1</span>.</label>
                    <?php echo Form::select('playlists_ids[]', $grouped_playlists)?>
                </div>
                <div id="add1more"><a href="javascript:addAnotherPlaylist()"><?php echo __('Add one more...') ?></a></div>
                <div>
                    <label><?php echo __('Playlist name') ?>:</label>
                    <input type="text" name="playlist_name" value="" />
                </div>
                <div>
                    <label><?php echo __('Make active') ?>:</label>
                    <input type="checkbox" name="make_active" style="width: auto" value="1" checked="checked" />
                </div>
                <div>
                    <input style="width: auto" type="submit" name="submit" value="<?php echo __('Create') ?>" />
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

    if ( count == 1 )
    {
        $gametypes.append('<a href="#" onclick="removePlaylist(this); return false;" ><?php echo __('Remove') ?></a>');
    }

    var $new_gametype = $gametypes.first().clone();
    $('.gtnumber',$new_gametype).text(count+1);

    $('#add1more').before($new_gametype);
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
</script>