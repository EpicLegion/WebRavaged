<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Server daemon
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

class StatsPlugin implements Plugin
{
    /**
     * @var array
     */
    protected $msgHeadshots = array();

    /**
     * @var array
     */
    protected $msgKills = array();

    /**
     * @var array
     */
    protected $playersWelcomed = array();

    /**
     * @var array
     */
    protected $session = array();

    /**
     * @var array
     */
    protected $store = array();

    /**
     * @var array
     */
    protected $timers = array();

    /**
     * @var array
     */
    protected $top = array();

    /**
     * @var array
     */
    protected $welcomeMessages = array();

    /**
     * @var array
     */
    protected $welcomeBackMessages = array();

    /**
     * !rank
     *
     * @param Player $player
     * @param string $text
     */
    public function commandRank(Player $player, $text)
    {
        // ID
        $id = Server::get('id');

        // Other player
        if (isset($text[1]) AND $text[1] == 'reset')
        {
            // On top?
            if (!isset($this->top[$id][$player->guid]))
            {
                $this->loadPlayer($player);
            }

            // Do reset
            $this->top[$id][$player->guid] = array(
                'position'  => 0,
                'kills'     => 0,
                'deaths'    => 0,
                'headshots' => 0,
                'kd'        => 0,
                'changed'   => TRUE
            );

            // Message
            Server::get()->message(__('Your rank has been reset successfully'), $player);
        }
        elseif (isset($text[1]) AND is_int($text[1]))
        {
            // Data
            $data = $this->loadPlayerPosition($text[1]);

            // Not found
            if (!$data)
            {
                Server::get()->message(__('Invalid position'), $player, 'rank');
                return;
            }

            // Send
            Server::get()->message($data['position'].'. '.$data['name'].'^7 | '.__('Kills').': '.$data['kills'].'; '.__('Deaths').': '.$data['deaths'].'; '.__('Headshots').': '.$data['headshots'].'; '.__('KD').': '.$data['kd'],
                                   $player, 'rank');
        }
        else
        {
            // Preload
            if (!isset($this->top[$id][$player->guid]))
            {
                $this->loadPlayer($player);
            }
            else
            {
                $position = Database::instance()->getSingle('SELECT COUNT(*) AS position FROM [prefix]leaderboards WHERE sid = '.$id.' AND kills >= '. $this->top[$id][$player->guid]['kills']);

                if ($position)
                {
                    $this->top[$id][$player->guid]['position'] = $position['position'];
                }
            }

            // Data
            $data = &$this->top[$id][$player->guid];

            // Send
            Server::get()->message($data['position'].'. '.$player->name.'^7 | '.__('Kills').': '.$data['kills'].'; '.__('Deaths').': '.$data['deaths'].'; '.__('Headshots').': '.$data['headshots'].'; '.__('KD').': '.$data['kd'],
                                   $player, 'rank');
        }
    }

    /**
     * !session
     *
     * @param Player $player
     * @param string $text
     */
    public function commandSession(Player $player, $text)
    {
        // ID
        $id = Server::get('id');

        // Few arrays
        if (!isset($this->top[$id])) $this->top[$id] = array();
        if (!isset($this->session[$id])) $this->session[$id] = array();

        // Preload
        if (!isset($this->session[$id][$player->guid]))
        {
            $this->session[$id][$player->guid] = array('kills' => 0, 'deaths' => 0, 'kd' => 0.0, 'headshots' => 0);
        }

        // Data
        $data = $this->session[$id][$player->guid];

        // Send
        Server::get()->message('Session stats: '.$player->name.'^7 | '.__('Kills').': '.$data['kills'].'; '.__('Deaths').': '.$data['deaths'].'; '.__('Headshots').': '.$data['headshots'].'; '.__('KD').': '.$data['kd'],
                               $player, 'session');
    }

    /**
     * Top stats
     *
     * @param Player $player
     * @param string $text
     */
    public function commandTopStats(Player $player, $text)
    {
        // Message
        $msg = '';

        // Iterate
        $i = 1; foreach (Database::instance()->getAll('SELECT * FROM [prefix]leaderboards WHERE sid = '.Server::get('id').' ORDER BY kills DESC LIMIT 3') as $data)
        {
            // Fix data
            $data['position'] = $i;
            $data['kills'] = (int) $data['kills'];
            $data['deaths'] = (int) $data['deaths'];
            $data['headshots'] = (int) $data['headshots'];
            $data['name'] = $data['name'];

            if ($data['deaths'] > 0)
            {
                $data['kd'] = round($data['kills'] / $data['deaths'], 2);
            }
            else
            {
                $data['kd'] = $data['kills'];
            }

            // Send
            $msg .= $data['position'].'. '.$data['name'].'^7 ('.$data['kills'].'), ';

            // Next
            $i++;
        }

        // Send
        Server::get()->message(rtrim($msg, ', '), $player, 'topstats');
    }

    /**
     * Load plugin
     *
     * @return bool
     */
    public function load()
    {
        // Commands
        Commands::add('rank'    , array($this, 'commandRank'));
        Commands::add('session' , array($this, 'commandSession'));
        Commands::add('topstats', array($this, 'commandTopStats'));

        // Event
        Event::add('onKill'           , array($this, 'onKill'));
        Event::add('onJoin'           , array($this, 'welcomeMessage'));
        Event::add('onExitLevel'      , array($this, 'onExitLevel'));
        Event::add('onServerLogFinish', array($this, 'onServerLogFinish'));
        Event::add('onConfigReload'   , array($this, 'loadConfig'));

        // Done
        return TRUE;
    }

    /**
     * Load config
     */
    public function loadConfig()
    {
        // ID
        $id = Server::get('id');

        // Welcome messages
        $this->welcomeMessages[$id] = Config::get('stats.welcome', NULL);
        $this->welcomeBackMessages[$id] = Config::get('stats.welcome_back', NULL);

        // Messages
        $this->msgHeadshots[$id] = Config::get('stats.headshots', array());
        $this->msgKills[$id] = Config::get('stats.kills', array());
        if (!isset($this->store[$id])) $this->store[$id] = array();
    }

    /**
     * Load player
     *
     * @param  Player $playerObj
     * @param  bool   $needsUpdate
     * @return bool
     */
    private function loadPlayer(Player $playerObj, $needsUpdate = FALSE)
    {
        // Few arrays
        if (!isset($this->top[Server::get('id')])) $this->top[Server::get('id')] = array();
        if (!isset($this->session[Server::get('id')])) $this->session[Server::get('id')] = array();

        // Get GUID
        $guid = $playerObj->guid;

        // Find
        $player = Database::instance()->getSingle('SELECT * FROM [prefix]leaderboards WHERE sid = '.Server::get('id').' AND guid = :guid LIMIT 1', array(':guid' => $guid));

        // Create new record
        if (!$player)
        {
            $position = Database::instance()->getSingle('SELECT COUNT(*) AS position FROM [prefix]leaderboards WHERE sid = '.Server::get('id'));

            if (!$position)
            {
                $position = 0;
            }
            else
            {
                $position = ((int) $position['position'] + 1);
            }

            $this->top[Server::get('id')][$guid] = array(
                'position'  => $position,
                'kills'     => 0,
                'deaths'    => 0,
                'headshots' => 0,
                'kd'        => 0,
                'changed'   => $needsUpdate
            );

            Database::instance()->exec('INSERT INTO [prefix]leaderboards (sid, guid, kills, deaths, headshots, name) VALUES(:sid, :guid, :kills, :deaths, :headshots, :name)', array(
                ':guid' => $guid,
                ':kills' => 0,
                ':deaths' => 0,
                ':headshots' => 0,
                ':name' => $playerObj->name,
                ':sid' => Server::get('id')
            ));

            return TRUE;
        }
        else
        {
            $position = Database::instance()->getSingle('SELECT COUNT(*) AS position FROM [prefix]leaderboards WHERE sid = '.Server::get('id').' AND kills >= '.(int) $player['kills']);

            if (!$position)
            {
                $position = 0;
            }
            else
            {
                $position = (int) $position['position'];
            }

            $this->top[Server::get('id')][$guid] = array(
                'position'  => $position,
                'kills'     => (int) $player['kills'],
                'deaths'    => (int) $player['deaths'],
                'headshots' => (int) $player['headshots'],
                'kd'        => 0,
                'changed'   => $needsUpdate
            );

            if ($player['deaths'] > 0)
            {
                $this->top[Server::get('id')][$guid]['kd'] = round($this->top[Server::get('id')][$guid]['kills'] / $this->top[Server::get('id')][$guid]['deaths'], 2);
            }
            else
            {
                $this->top[Server::get('id')][$guid]['kd'] = $this->top[Server::get('id')][$guid]['kills'];
            }

            return FALSE;
        }
    }

    /**
     * Load player position
     *
     * @param int $position
     */
    private function loadPlayerPosition($position)
    {
        // Few arrays
        if (!isset($this->top[Server::get('id')])) $this->top[Server::get('id')] = array();
        if (!isset($this->session[Server::get('id')])) $this->session[Server::get('id')] = array();

        // Find
        $player = Database::instance()->getSingle('SELECT * FROM [prefix]leaderboards WHERE sid = '.Server::get('id').' ORDER BY kills DESC LIMIT 1 OFFSET '.($position - 1));

        // Found
        if (!$player)
        {
            return NULL;
        }

        // Fix data
        $player['position'] = $position;
        $player['kills'] = (int) $player['kills'];
        $player['deaths'] = (int) $player['deaths'];
        $player['headshots'] = (int) $player['headshots'];
        $player['name'] = $player['name'];

        if ($player['deaths'] > 0)
        {
            $player['kd'] = round($player['kills'] / $player['deaths'], 2);
        }
        else
        {
            $player['kd'] = $player['kills'];
        }

        // Return
        return $player;
    }

    /**
     * On kill
     *
     * @param Player $victim
     * @param Player $attacker
     * @param Weapon $weapon
     * @param Damage $damage
     */
    public function onKill(Player $victim, Player $attacker, Weapon $weapon, Damage $damage)
    {
        // Ignore
        if ($victim instanceof WorldPlayer OR $victim instanceof DemoclientPlayer)
        {
            return;
        }

        // ID
        $id = Server::get('id');

        // Config
        if (!isset($this->store[$id])) $this->loadConfig();

        // Victim clear
        $this->store[$id][$victim->guid] = array('kills' => 0, 'hs' => 0, 'name' => NULL, 'dokills' => FALSE, 'dohs' => FALSE);

        // Init attacker if needed
        if (!isset($this->store[$id][$attacker->guid])) $this->store[$id][$attacker->guid] = array('kills' => 0, 'hs' => 0, 'name' => NULL, 'dokills' => FALSE, 'dohs' => FALSE);

        // Add stats
        if (!$attacker->isSameTeam($victim))
        {
            $this->store[$id][$attacker->guid]['kills']++;
            $this->store[$id][$attacker->guid]['dokills'] = !($attacker instanceof WorldPlayer);

            if ($damage->location == Damage::HIT_HEAD)
            {
                $this->store[$id][$attacker->guid]['hs']++;
                $this->store[$id][$attacker->guid]['dohs'] = !($attacker instanceof WorldPlayer);
            }

            $this->store[$id][$attacker->guid]['name'] = $attacker->name;
        }

        // Few arrays
        if (!isset($this->top[$id])) $this->top[$id] = array();
        if (!isset($this->session[$id])) $this->session[$id] = array();

        // Load tops
        if (!isset($this->top[$id][$victim->guid])) $this->loadPlayer($victim, TRUE);
        if (!($attacker instanceof WorldPlayer) AND !isset($this->top[$id][$attacker->guid])) $this->loadPlayer($attacker, TRUE);

        // Session
        if (!($attacker instanceof WorldPlayer) AND !$attacker->isSameTeam($victim))
        {
            if (!isset($this->session[$id][$attacker->guid]))
            {
                $this->session[$id][$attacker->guid] = array('kills' => 0, 'deaths' => 0, 'kd' => 0.0, 'headshots' => 0);
            }

            $this->session[$id][$attacker->guid]['kills']++;
            $this->top[$id][$attacker->guid]['kills']++;

            if ($damage->location == Damage::HIT_HEAD)
            {
                $this->session[$id][$attacker->guid]['headshots']++;
                $this->top[$id][$attacker->guid]['headshots']++;
            }

            if ($this->session[$id][$attacker->guid]['deaths'] > 0)
            {
                $this->session[$id][$attacker->guid]['kd'] = round($this->session[$id][$attacker->guid]['kills'] / $this->session[$id][$attacker->guid]['deaths'], 2);
            }
            else
            {
                $this->session[$id][$attacker->guid]['kd'] = $this->session[$id][$attacker->guid]['kills'];
            }

            if ($this->top[$id][$attacker->guid]['deaths'] > 0)
            {
                $this->top[$id][$attacker->guid]['kd'] = round($this->top[$id][$attacker->guid]['kills'] / $this->top[$id][$attacker->guid]['deaths'], 2);
            }
            else
            {
                $this->top[$id][$attacker->guid]['kd'] = $this->top[$id][$attacker->guid]['kills'];
            }

            $this->top[$id][$attacker->guid]['changed'] = TRUE;
        }

        if (!$attacker->isSameTeam($victim))
        {
            // Update needed
            $this->top[$id][$victim->guid]['changed'] = TRUE;

            if (!isset($this->session[$id][$victim->guid]))
            {
                $this->session[$id][$victim->guid] = array('kills' => 0, 'deaths' => 0, 'kd' => 0.0, 'headshots' => 0);
            }

            $this->session[$id][$victim->guid]['deaths']++;
            $this->top[$id][$victim->guid]['deaths']++;

            $this->session[$id][$victim->guid]['kd'] = round($this->session[$id][$victim->guid]['kills'] / $this->session[$id][$victim->guid]['deaths'], 2);
            $this->top[$id][$victim->guid]['kd'] = round($this->top[$id][$victim->guid]['kills'] / $this->top[$id][$victim->guid]['deaths'], 2);
        }
    }

    /**
     * On exit level
     */
    public function onExitLevel()
    {
        $id = Server::get('id');

        $this->session[$id] = array();
        $this->sync($id);
        $this->top[$id] = array();
        $this->store[$id] = array();
        $this->playersWelcomed[$id] = array();
    }

    /**
     * On server log finish
     *
     * @param float $deltaTime
     */
    public function onServerLogFinish($deltaTime)
    {
        // ID
        $id = Server::get('id');

        // Config
        if (!isset($this->store[$id])) $this->loadConfig();

        // Send text
        foreach ($this->store[$id] as $k => $v)
        {
            // Ignore
            if (!$v['dokills'] AND !$v['dohs']) continue;

            // Send
            if ($v['dokills'] AND isset($this->msgKills[$id][$v['kills']]))
            {
                Server::get()->message($v['name'].'^7 '.$this->msgKills[$id][$v['kills']].' ('.$v['kills'].')');
            }

            if ($v['dohs'] AND isset($this->msgHeadshots[$id][$v['hs']]))
            {
                Server::get()->message($v['name'].'^7 '.$this->msgHeadshots[$id][$v['hs']].' ('.$v['hs'].')');
            }

            // Clean up
            $this->store[$id][$k]['dohs'] = FALSE;
            $this->store[$id][$k]['dokills'] = FALSE;
        }

        // Timer
        if (!isset($this->timers[$id]))
        {
            $this->timers[$id] = 0;
        }

        // Add time
        $this->timers[$id] += $deltaTime;

        // Sync
        if ($this->timers[$id] >= 60)
        {
            $this->sync($id);
            $this->timers[$id] = 0;
        }
    }

    /**
     * Reload plugin
     */
    public function reload()
    {
        // Commands
        Commands::add('rank'    , array($this, 'commandRank'));
        Commands::add('session' , array($this, 'commandSession'));
        Commands::add('topstats', array($this, 'commandTopStats'));

        // Event
        Event::add('onKill'           , array($this, 'onKill'));
        Event::add('onExitLevel'      , array($this, 'onExitLevel'));
        Event::add('onServerLogFinish', array($this, 'onServerLogFinish'));
    }

    /**
     * Leaderboard sync
     *
     * @param int $id
     */
    private function sync($id)
    {
        // Valid?
        if (!isset($this->top[$id])) return;

        // List
        $players = PlayerManager::getPlayers();

        // Iterate
        foreach ($this->top[$id] as $guid => $data)
        {
            // Update?
            if (!$data['changed']) continue;

            // Name
            if (isset($players[$guid]))
            {
                Database::instance()->exec('UPDATE [prefix]leaderboards SET name = :name, kills = :kills, deaths = :deaths, headshots = :headshots WHERE guid = :guid AND sid = :sid', array(
                    ':kills' => $data['kills'],
                    ':deaths' => $data['deaths'],
                    ':headshots' => $data['headshots'],
                    ':guid' => $guid,
                    ':name' => $players[$guid]->name,
                    ':sid' => $id
                ));
            }
            else
            {
                Database::instance()->exec('UPDATE [prefix]leaderboards SET kills = :kills, deaths = :deaths, headshots = :headshots WHERE guid = :guid AND sid = :sid', array(
                    ':kills' => $data['kills'],
                    ':deaths' => $data['deaths'],
                    ':headshots' => $data['headshots'],
                    ':guid' => $guid,
                    ':sid' => $id
                ));
            }

            $this->top[$id][$guid]['changed'] = FALSE;
        }
    }

    /**
     * Unload plugin
     */
    public function unload()
    {

    }

    /**
     * Welcome message
     *
     * @param Player $player
     */
    public function welcomeMessage(Player $player)
    {
        // ID
        $id = Server::get('id');

        // Already welcomed
        if (isset($this->playersWelcomed[$id]) AND isset($this->playersWelcomed[$id][$player->guid])) return;

        // Add welcomed player
        if (!isset($this->playersWelcomed[$id])) $this->playersWelcomed[$id] = array();
        $this->playersWelcomed[$id][$player->guid] = TRUE;

        // Config
        if (!isset($this->welcomeMessages[$id])) $this->loadConfig();

        // Get message
        if ($this->loadPlayer($player))
        {
            $message = $this->welcomeMessages[$id];
        }
        else
        {
            $message = $this->welcomeBackMessages[$id];

            if ($message)
            {
                $message = str_replace('$KILLS', $this->top[$id][$player->guid]['kills'], $message);
                $message = str_replace('$DEATHS', $this->top[$id][$player->guid]['deaths'], $message);
                $message = str_replace('$KD', $this->top[$id][$player->guid]['kd'], $message);
            }
        }

        // Disabled
        if (!$message)
        {
            return;
        }

        // Send message
        $message = str_replace('$PLAYERNAME', $player->name, $message);
        Server::get()->message($message, $player, 'welcome');
    }
}