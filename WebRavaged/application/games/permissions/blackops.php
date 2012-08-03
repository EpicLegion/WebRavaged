<?php
// Consts
if(!defined('SERVER_KICK'))
{
    define('SERVER_KICK', 1);
    define('SERVER_BAN', 2);
    define('SERVER_TEMP_BAN', 4);
    define('SERVER_MESSAGE', 8);
    define('SERVER_USER_LOG', 16);
    define('SERVER_PLAYLIST', 32);
    define('SERVER_MESSAGE_ROTATION', 64);
    define('SERVER_MAPS', 128);
    define('SERVER_FAST_RESTART', 256);
}

// Return permissions
return array(
    'can_kick'             => array('title' => 'Can kick',                     'bit' => SERVER_KICK),
    'can_ban'              => array('title' => 'Can ban',                      'bit' => SERVER_BAN),
    'can_temp_ban'         => array('title' => 'Can temp ban',                 'bit' => SERVER_TEMP_BAN),
    'can_messages'         => array('title' => 'Can send messages',            'bit' => SERVER_MESSAGE),
    'can_message_rotation' => array('title' => 'Can manage message rotations', 'bit' => SERVER_MESSAGE_ROTATION),
    'can_logs'             => array('title' => 'Can view user logs',           'bit' => SERVER_USER_LOG),
    'can_playlists'        => array('title' => 'Can set playlists',            'bit' => SERVER_PLAYLIST),
    'can_maps'             => array('title' => 'Can manage maps',              'bit' => SERVER_MAPS),
    'can_fast_restart'     => array('title' => 'Can use fast restart',         'bit' => SERVER_FAST_RESTART),
);