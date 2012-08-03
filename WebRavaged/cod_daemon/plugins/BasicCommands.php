<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Basic server commands
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

class BasicCommandsPlugin implements Plugin
{
    /**
     * @var array
     */
    protected $autoRules = array();

    /**
     * @var array
     */
    protected $rules = array();

    /**
     * @var array
     */
    protected $timeout = array();

    /**
     * Some basic info about player
     *
     * @param Player $player
     * @param string $text
     */
    public function commandInfo(Player $player, $text)
    {
        // Test target
        if (!isset($text[1]))
        {
            $target = $player;
        }
        else
        {
            $target = PlayerManager::find($text[1]);

            if ($target === NULL)
            {
                Server::get()->message(__('Invalid target (player not found)'), $player);
                return;
            }
        }

        // Group
        $group = PlayerManager::getGroup($target->guid);

        if (!$group) $group = __('none');

        // Print some data
        Server::get()->message($target->name.'^7 ID: '.$target->id.'; GUID: '.$target->guid.'; '.__('Group').': '.$group, $player, 'info');
    }

    /**
     * Players list
     *
     * @param Player $player
     * @param string $text
     */
    public function commandPlayers(Player $player, $text)
    {
        // String
        $players = '';

        // Iterate
        foreach (PlayerManager::getPlayers() as $p)
        {
            $players .= $p->name.', ';
        }

        // Cleanup string
        $players = trim($players, ', ');

        // Send
        Server::get()->message(__('Players: ').$players, $player, 'players');
    }

    /**
     * Time command
     *
     * @param Player $player
     * @param string $text
     */
    public function commandTime(Player $player, $text)
    {
        // Send
        Server::get()->message(__('Current server time is: :time', array(':time' => date('H:i'))), $player, 'time');
    }

    /**
     * Load plugin
     *
     * @return bool
     */
    public function load()
    {
        // Events
        Event::add('onServerLogFinish', array($this, 'onServerLogFinish'));

        // Commands
        Commands::add('time'   , array($this, 'commandTime'));
        Commands::add('players', array($this, 'commandPlayers'));
        Commands::add('info'   , array($this, 'commandInfo'));

        // Events
        Event::add('onConfigReload', array($this, 'loadConfig'));

        // Done
        return TRUE;
    }

    /**
     * Load config for current server
     */
    public function loadConfig()
    {
        // ID
        $id = Server::get('id');

        // Timeout
        $this->timeout[$id] = array('max' => Config::get('basic_cmd.rule_timeout', 30), 'time' => 0);

        // Auto rules
        $this->autoRules[$id] = Config::get('basic_cmd.auto_rules', TRUE);

        // Rules
        $this->rules[$id] = array();

        foreach (Config::get('basic_cmd.server_rules', array()) as $r)
        {
            $this->rules[$id][] = (string) $r;
        }
    }

    /**
     * Send auto rules
     *
     * @param float $deltaTime
     */
    public function onServerLogFinish($deltaTime)
    {
        // ID
        $id = Server::get('id');

        // Preload config
        if (!isset($this->autoRules[$id])) $this->loadConfig();

        // Disabled?
        if (!$this->autoRules[$id])
        {
            return;
        }

        // No rules?
        if (empty($this->rules[$id]))
        {
            return;
        }

        // Add time
        $this->timeout[$id]['time'] += $deltaTime;

        // Should we send it?
        if ($this->timeout[$id]['max'] <= $this->timeout[$id]['time'])
        {
            Server::get()->message($this->rules[$id][rand(0, (count($this->rules[$id]) - 1))]);
            $this->timeout[$id]['time'] = 0;
        }
    }

    /**
     * Reload plugin
     */
    public function reload()
    {
        // Events
        Event::add('onServerLogFinish', array($this, 'onServerLogFinish'));

        // Commands
        Commands::add('time'   , array($this, 'commandTime'));
        Commands::add('players', array($this, 'commandPlayers'));
        Commands::add('info'   , array($this, 'commandInfo'));

        // Events
        Event::add('onConfigReload', array($this, 'loadConfig'));

        // Clear config
        $this->autoRules = array();
        $this->rules = array();
        $this->timeout = array();
    }

    /**
     * Unload plugin
     */
    public function unload()
    {

    }
}