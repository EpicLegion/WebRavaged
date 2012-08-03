<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Admin commands
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

class AdminCommandsPlugin implements Plugin
{
    /**
     * @var array
     */
    protected $reasonShortcuts = array();

    /**
     * @var array
     */
    protected $sayShortcuts = array();

    /**
     * Ban player
     *
     * @param Player $player
     * @param string $text
     */
    public function commandBan(Player $player, $text)
    {
        // Config
        if (!isset($this->reasonShortcuts[Server::get('id')])) $this->loadConfig();

        // Valid syntax
        if (!isset($text[1]))
        {
            return;
        }

        // Permissions
        if (!$player->hasFlag('ban'))
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
            $reason = NULL;
        }
        else
        {
            $reason = (string) $text[2];

            if (isset($this->reasonShortcuts[Server::get('id')][$reason]))
            {
                $reason = $this->reasonShortcuts[Server::get('id')][$reason]['message'];
            }
        }

        // Ban
        Server::get()->ban($target, $reason);

        // Message
        Server::get()->message(__('Player :name has been banned (:reason)', array(
            ':name' => $target->name.'^7',
            ':reason' => $reason
        )));
    }

    /**
     * Say command
     *
     * @param Player $player
     * @param string $text
     */
    public function commandSay(Player $player, $text)
    {
        // Config
        if (!isset($this->sayShortcuts[Server::get('id')])) $this->loadConfig();

        // Valid syntax
        if (!isset($text[1]))
        {
            return;
        }

        // Permissions
        if (!$player->hasFlag('say'))
        {
            Server::get()->message(__('Insufficient permissions'), $player);
            return;
        }

        // Shortcut
        if (isset($this->sayShortcuts[Server::get('id')][$text[1]]))
        {
            $text[1] = $this->sayShortcuts[Server::get('id')][$text[1]];
        }
        elseif (count($text) > 2)
        {
            for ($i = 2, $c = count($text); $i < $c; $i++)
            {
                $text[1] .= ' '.$text[$i];
            }
        }

        // Send message
        Server::get()->message($text[1]);
    }

    /**
     * Kick player
     *
     * @param Player $player
     * @param string $text
     */
    public function commandKick(Player $player, $text)
    {
        // Config
        if (!isset($this->reasonShortcuts[Server::get('id')])) $this->loadConfig();

        // Valid syntax
        if (!isset($text[1]))
        {
            return;
        }

        // Permissions
        if (!$player->hasFlag('kick'))
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
            $reason = NULL;
        }
        else
        {
            $reason = (string) $text[2];

            if (isset($this->reasonShortcuts[Server::get('id')][$reason]))
            {
                $reason = $this->reasonShortcuts[Server::get('id')][$reason]['message'];
            }
        }

        // Kick
        Server::get()->kick($target, $reason);

        // Message
        Server::get()->message(__('Player :name has been kicked (:reason)', array(
            ':name' => $target->name.'^7',
            ':reason' => $reason
        )));
    }

    /**
     * !reserved
     *
     * @param Player $player
     * @param array  $text
     */
    public function commandReserved(Player $player, $text)
    {
        // Permissions
        if (!$player->hasFlag('reserved'))
        {
            Server::get()->message(__('Insufficient permissions'), $player);
            return;
        }

        // Players
        $players = PlayerManager::getPlayers();

        // Kick state
        $state = FALSE;

        // Type
        if (Config::get('admin_cmd.reserved', 'random') == 'ping')
        {
            // Team status
            $status = Server::get()->teamStatus(TRUE);

            // Iterate
            foreach ($status['players'] as $p)
            {
                // Valid player
                if (isset($players[$p['guid']]) AND !$players[$p['guid']]->hasFlag('slot'))
                {
                    Server::get()->kick($players[$p['guid']], __('Slot reservation'));
                    Server::get()->message(__('Player :name has been kicked by server (Slot reservation)', array(
                        ':name' => $players[$p['guid']]->name.'^7'
                    )));
                    $state = TRUE;
                    break;
                }
            }
        }

        // Random
        if (!$state)
        {
            // Randomize
            shuffle($players);

            // Iterate
            foreach ($players as $p)
            {
                if (!$p->hasFlag('slot'))
                {
                    Server::get()->kick($p, __('Slot reservation'));
                    Server::get()->message(__('Player :name has been kicked by server (Slot reservation)', array(
                        ':name' => $p->name.'^7'
                    )));
                    $state = TRUE;
                    break;
                }
            }
        }

        // Not done?
        if (!$state)
        {
            Server::get()->message(__('Unable to find matching players'), $player);
        }
    }

    /**
     * Temp ban player
     *
     * @param Player $player
     * @param string $text
     */
    public function commandTempBan(Player $player, $text)
    {
        // Config
        if (!isset($this->reasonShortcuts[Server::get('id')])) $this->loadConfig();

        // Valid syntax
        if (!isset($text[1]))
        {
            return;
        }

        // Permissions
        if (!$player->hasFlag('tempban'))
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
            $reason = NULL;
            $duration = NULL;
        }
        else
        {
            $reason = (string) $text[2];

            if (isset($this->reasonShortcuts[Server::get('id')][$reason]))
            {
                $duration = $this->reasonShortcuts[Server::get('id')][$reason]['duration'];
                $reason = $this->reasonShortcuts[Server::get('id')][$reason]['message'];
            }
        }

        // Duration
        if (isset($text[3]) AND is_string($text[3]))
        {
            $duration = parse_time($text[3]);
        }

        // Ban
        Server::get()->tempBan($target, $reason, $duration);

        // Message
        Server::get()->message(__('Player :name has been temp-banned (:reason)', array(
            ':name' => $target->name.'^7',
            ':reason' => $reason
        )));
    }

    /**
     * Load plugin
     *
     * @return bool
     */
    public function load()
    {
        // Commands
        Commands::add('kick'    , array($this, 'commandKick'), 'kick');
        Commands::add('ban'     , array($this, 'commandBan'), 'ban');
        Commands::add('tempban' , array($this, 'commandTempBan'), 'tempban');
        Commands::add('say'     , array($this, 'commandSay'), 'say');
        Commands::add('reserved', array($this, 'commandReserved'), 'reserved');

        // Events
        Event::add('onConfigReload', array($this, 'loadConfig'));

        // Done
        return TRUE;
    }

    /**
     * Load configuration
     */
    public function loadConfig()
    {
        // Server ID
        $id = Server::get('id');

        // Empty arrays
        $this->reasonShortcuts[$id] = array();
        $this->sayShortcuts[$id] = array();

        // Reasons
        foreach (Config::get('admin_cmd.reasons', array()) as $k => $v)
        {
            $reason = array('message' => '', 'duration' => NULL);

            if (is_array($v))
            {
                if (!isset($v['message']))
                {
                    continue;
                }

                $reason['message'] = $v['message'];

                if (isset($v['duration']))
                {
                    $reason['duration'] = parse_time($v['duration']);
                }
            }
            else
            {
                $reason['message'] = $v;
            }

            $this->reasonShortcuts[$id][$k] = $reason;
        }

        // Say shortcuts
        foreach (Config::get('admin_cmd.say', array()) as $k => $v)
        {
            $this->sayShortcuts[$id][$k] = $v;
        }
    }

    /**
     * Reload plugin
     */
    public function reload()
    {
        // Commands
        Commands::add('kick'    , array($this, 'commandKick'), 'kick');
        Commands::add('ban'     , array($this, 'commandBan'), 'ban');
        Commands::add('tempban' , array($this, 'commandTempBan'), 'tempban');
        Commands::add('say'     , array($this, 'commandSay'), 'say');
        Commands::add('reserved', array($this, 'commandReserved'), 'reserved');

        // Events
        Event::add('onConfigReload', array($this, 'loadConfig'));

        // Clear config
        $this->reasonShortcuts = array();
        $this->sayShortcuts = array();
    }

    /**
     * Unload plugin
     */
    public function unload()
    {

    }
}
