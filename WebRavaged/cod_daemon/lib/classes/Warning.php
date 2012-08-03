<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Warnings system
 *
 * Copyright (c) 2011, EpicLegion
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
 * @author     EpicLegion
 * @package    cod_daemon
 * @subpackage game
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

final class Warning
{
    /**
     * @var array
     */
    protected static $action = array();

    /**
     * @var array
     */
    protected static $final = array();

    /**
     * @var array
     */
    protected static $limit = array();

    /**
     * @var array
     */
    protected static $shortcuts = array();

    /**
     * @var array
     */
    protected static $warnings = array();

    /**
     * Add warning
     *
     * @param Player $player
     * @param string $reason
     */
    public static function add(Player $player, $reason)
    {
        // ID
        $id = Server::get('id');

        // Config
        if (!isset(self::$warnings[$id]) OR !isset(self::$limit[$id])) self::reloadConfig();

        // Player has no warnings?
        if (!isset(self::$warnings[$id][$player->guid])) self::$warnings[$id][$player->guid] = array();

        // Shortcut
        if (isset(self::$shortcuts[$id][$reason]))
        {
            $reason = self::$shortcuts[$id][$reason];
        }

        // Append
        self::$warnings[$id][$player->guid][] = $reason;

        // Bad boy
        if (count(self::$warnings[$id][$player->guid]) > self::$limit[$id])
        {
            // Clear
            unset(self::$warnings[$id][$player->guid]);

            // Do our job
            switch (self::$action[$id])
            {
                case 'ban':
                    Server::get()->ban($player, $reason);
                    Server::get()->message(__('Player :name has been banned by server (:reason)', array(':name' => $player->name, ':reason' => $reason)));
                    break;

                case 'tempban':
                    Server::get()->tempBan($player, $reason);
                    Server::get()->message(__('Player :name has been temp-banned by server (:reason)', array(':name' => $player->name, ':reason' => $reason)));
                    break;

                default:
                    Server::get()->kick($player, $reason);
                    Server::get()->message(__('Player :name has been kicked by server (:reason)', array(':name' => $player->name, ':reason' => $reason)));
            }

            // We're done here
            return;
        }

        // Message
        Server::get()->message(__('Player :name has been warned [:warnings/:limit] (:reason)', array(
            ':name' => $player->name,
            ':warnings' => count(self::$warnings[$id][$player->guid]),
            ':limit' => self::$limit[$id],
            ':reason' => $reason
        )));

        // Final warning
        if (count(self::$warnings[$id][$player->guid]) == self::$limit[$id])
        {
            Server::get()->message(self::$final[$id], $player);
        }
    }

    /**
     * Init warnings system
     */
    public static function init()
    {
        // Commands
        Commands::add('warn'    , array('Warning', 'warn'), 'warn');
        Commands::add('warnings', array('Warning', 'warnings'));

        // Events
        Event::add('onExitLevel'   , array('Warning', 'onExitLevel'));
        Event::add('onServerConfig', array('Warning', 'reloadConfig'));
    }

    /**
     * Clear warnings
     */
    public static function onExitLevel()
    {
        self::$warnings[Server::get('id')] = array();
    }

    /**
     * Reload config
     */
    public static function reloadConfig()
    {
        // ID
        $id = Server::get('id');

        // Basic
        self::$limit[$id] = Config::get('warnings.limit', 5);
        self::$action[$id] = Config::get('warnings.action', 'kick');
        self::$final[$id] = Config::get('warnings.final_warning', 'This is your final warning. Next rule violation will result in kick');

        // Shortcuts
        self::$shortcuts[$id] = array();

        foreach (Config::get('warnings.shortcuts', array()) as $k => $v)
        {
            self::$shortcuts[$id][$k] = $v;
        }

        // Warnings
        if (!isset(self::$warnings[$id])) self::$warnings[$id] = array();
    }

    /**
     * Warn player
     *
     * @param Player $player
     * @param string $text
     */
    public static function warn(Player $player, $text)
    {
        // ID
        $id = Server::get('id');

        // Config
        if (!isset(self::$warnings[$id])) self::reloadConfig();

        // Valid syntax
        if (!isset($text[1]))
        {
            return;
        }

        // Permissions
        if (!$player->hasFlag('warn'))
        {
            Server::get()->message(__('Insufficient permissions'), $player);
            return;
        }

        // Find player
        $target = PlayerManager::find($text[1]);

        if ($target === NULL OR $target->hasFlag('immunity'))
        {
            Server::get()->message(__('Invalid target (player not found or has immunity)'), $player);
            return;
        }

        // Reason
        if (!isset($text[2]))
        {
            $reason = __('No reason');
        }
        else
        {
            $reason = (string) $text[2];
        }

        // Add warning
        Warning::add($target, $reason);
    }

    /**
     * Show warnings
     *
     * @param Player $player
     * @param string $text
     */
    public static function warnings(Player $player, $text)
    {
        // ID
        $id = Server::get('id');

        // Config
        if (!isset(self::$warnings[$id])) self::reloadConfig();

        // No warnings?
        if (empty(self::$warnings[$id][$player->guid]))
        {
            Server::get()->message(__('Currently you have no warnings'), $player);
            return;
        }

        // Last warning
        $last = end(self::$warnings[$id][$player->guid]);

        // Print
        Server::get()->message(__('You have [:warnings/:limit] warnings. Your last warning: :last', array(
            ':warnings' => count(self::$warnings[$id][$player->guid]),
            ':limit' => self::$limit[$id],
            ':last' => $last
        )));
    }
}
