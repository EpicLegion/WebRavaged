<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Basic votes
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
 * @subpackage plugins
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

class BasicVotesPlugin implements Plugin
{
    /**
     * Load plugin
     *
     * @return bool
     */
    public function load()
    {
        // Votes
        VoteManager::add('kick'   , array($this, 'onKickStart')   , array($this, 'onKickEnd'));
        VoteManager::add('ban'    , array($this, 'onBanStart')    , array($this, 'onBanEnd'));
        VoteManager::add('tempban', array($this, 'onTempBanStart'), array($this, 'onTempBanEnd'));

        // Success
        return TRUE;
    }

    /**
     * Ban vote end
     *
     * @param string       $result
     * @param array|string $option
     * @param mixed        $params
     */
    public function onBanEnd($result, $option, $params)
    {
        // Only win
        if ($result != 'win' OR $option != __('yes'))
        {
            return;
        }

        // Find user
        $player = PlayerManager::findGUID($params);

        // Not found?
        if ($player === NULL OR $player->hasFlag('immunity'))
        {
            return;
        }

        // Ban
        Server::get()->ban($player);

        // Message
        Server::get()->message(__('Player :name has been banned by players', array(':name' => $player->name.'^7')));
    }

    /**
     * Ban vote start
     *
     * @param  Player $player
     * @param  array  $text
     * @return mixed
     */
    public function onBanStart(Player $player, $text)
    {
        // Argument
        if (!isset($text[2]) OR !is_string($text[2]))
        {
            return FALSE;
        }

        // Find player
        $target = PlayerManager::find($text[2]);

        // Not found
        if ($target === NULL OR $target->hasFlag('immunity'))
        {
            return FALSE;
        }

        // Success
        return array(
            'message' => __('Ban player :name?', array(':name' => $target->name.'^7')),
            'options' => array(__('yes') => 0, __('no') => 0),
            'params'  => $target->guid
        );
    }

    /**
     * Kick vote end
     *
     * @param string       $result
     * @param array|string $option
     * @param mixed        $params
     */
    public function onKickEnd($result, $option, $params)
    {
        // Only win
        if ($result != 'win' OR $option != __('yes'))
        {
            return;
        }

        // Find user
        $player = PlayerManager::findGUID($params);

        // Not found?
        if ($player === NULL OR $player->hasFlag('immunity'))
        {
            return;
        }

        // Kick
        Server::get()->kick($player, 'Votekick');

        // Message
        Server::get()->message(__('Player :name has been kicked by players', array(':name' => $player->name.'^7')));
    }

    /**
     * Kick vote start
     *
     * @param  Player $player
     * @param  array  $text
     * @return mixed
     */
    public function onKickStart(Player $player, $text)
    {
        // Argument
        if (!isset($text[2]) OR !is_string($text[2]))
        {
            return FALSE;
        }

        // Find player
        $target = PlayerManager::find($text[2]);

        // Not found
        if ($target === NULL OR $target->hasFlag('immunity'))
        {
            return FALSE;
        }

        // Success
        return array(
            'message' => __('Kick player :name?', array(':name' => $target->name.'^7')),
            'options' => array(__('yes') => 0, __('no') => 0),
            'params'  => $target->guid
        );
    }

    /**
     * Tempban vote end
     *
     * @param string       $result
     * @param array|string $option
     * @param mixed        $params
     */
    public function onTempBanEnd($result, $option, $params)
    {
        // Only win
        if ($result != 'win' OR $option != __('yes'))
        {
            return;
        }

        // Find user
        $player = PlayerManager::findGUID($params);

        // Not found?
        if ($player === NULL OR $player->hasFlag('immunity'))
        {
            return;
        }

        // Temp ban
        Server::get()->tempBan($player);

        // Message
        Server::get()->message(__('Player :name has been temp-banned by players', array(':name' => $player->name.'^7')));
    }

    /**
     * Tempban vote start
     *
     * @param  Player $player
     * @param  array  $text
     * @return mixed
     */
    public function onTempBanStart(Player $player, $text)
    {
        // Argument
        if (!isset($text[2]) OR !is_string($text[2]))
        {
            return FALSE;
        }

        // Find player
        $target = PlayerManager::find($text[2]);

        // Not found
        if ($target === NULL OR $target->hasFlag('immunity'))
        {
            return FALSE;
        }

        // Success
        return array(
            'message' => __('Temp-ban player :name?', array(':name' => $target->name.'^7')),
            'options' => array(__('yes') => 0, __('no') => 0),
            'params'  => $target->guid
        );
    }

    /**
     * Reload plugin
     */
    public function reload()
    {
        // Votes
        VoteManager::add('kick'   , array($this, 'onKickStart')   , array($this, 'onKickEnd'));
        VoteManager::add('ban'    , array($this, 'onBanStart')    , array($this, 'onBanEnd'));
        VoteManager::add('tempban', array($this, 'onTempBanStart'), array($this, 'onTempBanEnd'));
    }

    /**
     * Unload plugin
     */
    public function unload()
    {

    }
}
