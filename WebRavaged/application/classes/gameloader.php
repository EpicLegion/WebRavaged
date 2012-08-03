<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Gameloader
 *
 * Copyright (c) 2010, EpicLegion
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 *   * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA,
 * OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author        EpicLegion, Maximusya
 * @package        rcon
 * @subpackage    controller
 * @license        http://www.opensource.org/licenses/bsd-license.php    New BSD License
 */
final class Gameloader {

    /**
     * Gameloader cache
     *
     * @var    array
     */
    protected static $cache = array();

    /**
     * Current server
     *
     * @var    array|int
     */
    public static $current_server = 0;

    /**
     * Servers owned by user
     *
     * @var    array
     */
    public static $owned_servers = array();

    /**
     * Get games
     */
    public static function get_games()
    {
        return require APPPATH.'games/list.php';
    }

    /**
     * Get permissions for game
     *
     * @param    string    $game
     */
    public static function get_permissions($game = NULL)
    {
        // Current server
        if($game === NULL AND self::$current_server != 0)
        {
            $game = self::$current_server['game'];
        }
        elseif($game === NULL)
        {
            return array();
        }

        // Basename
        $game = basename($game);

        // Cached
        if(isset(self::$cache[$game]))
        {
            return self::$cache[$game];
        }

        // Get permissions
        $perms = require APPPATH.'games/permissions/'.$game.'.php';

        // Cache it
        self::$cache[$game] = $perms;

        // Return
        return $perms;
    }

    /**
     * Load dashboard class
     *
     * @throws Kohana_Exception
     */
    public static function load_dashboard()
    {
        // User
        $user = Auth::instance()->get_user();

        // Guest?
        if(!$user)
        {
            Request::$instance->redirect('login/index');
        }

        // Owned servers
        self::$owned_servers = Model_Server::get_owned($user->id);

        // No servers?
        if(empty(self::$owned_servers))
        {
            // Empty dashboard
            require APPPATH.'games/dashboard/empty.php';

            // Done
            return;
        }

        // Session
        $session = Session::instance();

        // Get ID from session and check if the user ownes the current
        if(is_int($session->get('current_server')) OR ctype_digit($session->get('current_server')))
        {
            foreach(self::$owned_servers as $o)
            {
                if($o['server_id'] == (int) $session->get('current_server'))
                {
                    self::$current_server = $o;
                    break;
                }
            }
        }

        // Get default
        if(!self::$current_server)
        {
            foreach(self::$owned_servers as $o)
            {
                self::$current_server = $o;
                break;
            }
        }

        // Get all the info (id, name, ip, port, password) for current server
        $server = Model_Server::get_details(self::$current_server['server_id']);

        // Something's wrong?
        if(count($server) <= 0)
        {
            throw new Kohana_Exception('This is not supposed to happen :O');
        }

        // Add server data
        self::$current_server += $server;

        // Game
        $game = basename(self::$current_server['game']);

        // Preload permissions
        self::get_permissions($game);

        // Valid game?
        if(is_file(APPPATH.'games/dashboard/'.$game.'.php'))
        {
            // Read configuration file
            Config::read_config($game);

            // Load dashboard file
            require APPPATH.'games/dashboard/'.$game.'.php';
        }
        else
        {
            require APPPATH.'games/dashboard/empty.php';
        }
    }
}