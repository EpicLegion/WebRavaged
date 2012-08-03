<?php defined('ROOT_PATH') or die('No direct script access.');

/**
 * Maps system
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

class MapsPlugin implements Plugin
{
    /**
     * @var array
     */
    protected $cacheMap = array();

    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var array
     */
    protected $mapList = array();

    /**
     * @var array
     */
    protected $nextMap = array();

    /**
     * @var array
     */
    protected $nextType = array();

    /**
     * @var array
     */
    protected $nominations = array();

    /**
     * @var array
     */
    protected $playCurrent = array();

    /**
     * @var array
     */
    protected $playlists = array();

    /**
     * @var array
     */
    protected $skipEnabled = array();

    /**
     * @var array
     */
    protected $skips = array();

    /**
     * @var array
     */
    protected $timers = array();

    /**
     * @var array
     */
    protected $timers2 = array();

    /**
     * Current map name
     *
     * @param Player $player
     * @param string $text
     */
    public function commandMap(Player $player, $text)
    {
        // Cached?
        if (isset($this->cacheMap[Server::get('id')]))
        {
            $map = $this->cacheMap[Server::get('id')];
        }
        else
        {
            $map = Server::get()->getCvar('mapname');
            $this->cacheMap[Server::get('id')] = $map;
        }

        // Send
        Server::get()->message(__('Current map is: ').$map, $player, 'map');
    }

    /**
     * !nextmap
     *
     * @param Player $player
     * @param string $text
     */
    public function commandNextMap(Player $player, $text)
    {
        // Already choosen
        if (!empty($this->nextMap[Server::get('id')]))
        {
            Server::get()->message(__('Next map: ').$this->nextMap[Server::get('id')], $player, 'nextmap');
        }
        else
        {
            Server::get()->message(__('Next map is not selected yet'), $player, 'nextmap');
        }
    }

    /**
     * !nominate mp_map
     *
     * @param Player $player
     * @param string $text
     */
    public function commandNominate(Player $player, $text)
    {
        // Server ID
        $id = Server::get('id');

        // Config
        if (!isset($this->config[$id])) $this->loadConfig();

        // Invalid call
        if ($this->config[$id]['type'] != 'vote' OR (isset($this->timers[$id]) AND $this->timers[$id]['called']))
        {
            Server::get()->message(__('This command is currently unavailable'), $player, 'error');
            return;
        }

        // Nominations
        if (!isset($this->nominations[$id])) $this->nominations[$id] = array('players' => array(), 'maps' => array());

        // Nominations enabled?
        if (!$this->config[$id]['nominations'])
        {
            Server::get()->message(__('Nominations system is disabled'), $player, 'error');
            return;
        }

        // Valid call
        if (!isset($text[1]) OR !is_string($text[1]))
        {
            Server::get()->message(__('Usage: !nominate mapname'), $player, 'usage');
            return;
        }

        // Valid map?
        if (!isset($this->mapList[$id][$text[1]]))
        {
            if (($text[1] = $this->findMap($text[1])) === NULL)
            {
                Server::get()->message(__('Invalid map'), $player, 'error');
                return;
            }
        }

        // Load current map
        if (!isset($this->cacheMap[$id]))
        {
            $this->cacheMap[$id] = Server::get()->getCvar('mapname');
        }

        // Can't nominate current map
        if ($this->cacheMap[$id] AND $this->cacheMap[$id] == $text[1])
        {
            return;
        }

        // Already nominated?
        if (isset($this->nominations[$id]['maps'][$text[1]]))
        {
            Server::get()->message(__('This map has been already nominated'), $player, 'error');
            return;
        }

        if (isset($this->nominations[$id]['players'][$player->guid]))
        {
            Server::get()->message(__('You can only nominate one map'), $player, 'error');
            return;
        }

        // Add nomination
        $this->nominations[$id]['players'][$player->guid] = TRUE;
        $this->nominations[$id]['maps'][$text[1]] = $text[1];
    }

    /**
     * !setmap mp_map
     *
     * @param Player $player
     * @param string $text
     */
    public function commandSetNextMap(Player $player, $text)
    {
        // Server ID
        $id = Server::get('id');

        // Config
        if (!isset($this->config[$id])) $this->loadConfig();

        // Flags
        if (!$player->hasFlag('setnextmap'))
        {
            Server::get()->message(__('Insufficient permissions'), $player, 'error');
            return;
        }

        // Valid call
        if (!isset($text[1]) OR !is_string($text[1]))
        {
            Server::get()->message(__('Usage: !setmap mapname'), $player, 'usage');
            return;
        }

        // Valid map?
        if (!isset($this->mapList[$id][$text[1]]))
        {
            if (($text[1] = $this->findMap($text[1])) === NULL)
            {
                Server::get()->message(__('Invalid map'), $player, 'error');
                return;
            }
        }

        // Reset timers
        $this->timers[$id] = array('time' => 0, 'called' => TRUE);

        // Disable skip
        $this->skipEnabled[$id] = FALSE;

        // Set next map
        $this->setNextMap($text[1]);

        // Set
        Server::get()->message(__('Next map has been set to: ').$text[1]);
    }

    /**
     * !setplaylist tdm normal
     *
     * @param Player $player
     * @param string $text
     */
    public function commandSetPlaylist(Player $player, $text)
    {
        // Server ID
        $id = Server::get('id');

        // Config
        if (!isset($this->config[$id])) $this->loadConfig();

        // Flags
        if (!$player->hasFlag('setplaylist'))
        {
            Server::get()->message(__('Insufficient permissions'), $player, 'error');
            return;
        }

        // Valid call
        if (!isset($text[1]) OR !is_string($text[1]))
        {
            Server::get()->message(__('Usage: !setplaylist tdm|sd... barebones|hardcore|normal'), $player, 'usage');
            return;
        }

        // Playlist
        $playlist = array('type' => $text[1]);

        // Valid playlist
        if (!isset(RconConstants::$gametypes[$text[1]]))
        {
            Server::get()->message(__('Invalid game type'), $player, 'error');
            return;
        }

        // Mode
        if (isset($text[2]) AND is_string($text[2]))
        {
            if ($text[2] == 'barebones') $playlist['mode'] = 'barebones';
            elseif ($text[2] == 'hardcore') $playlist['mode'] = 'hardcore';
            else $playlist['mode'] = 'normal';
        }
        else
        {
            $playlist['mode'] = 'normal';
        }

        // Reset timers
        $this->timers2[$id] = array('time' => 0, 'called' => TRUE);

        // Set next playlist
        $this->setNextPlaylist($playlist);
    }

    /**
     * !skip
     *
     * @param Player $player
     * @param string $text
     */
    public function commandSkip(Player $player, $text)
    {
        // Server ID
        $id = Server::get('id');

        // Nothing to skip?
        if (!isset($this->skipEnabled[$id]) OR !$this->skipEnabled[$id])
        {
            Server::get()->message(__('You cannot skip next map right now'), $player, 'error');
            return;
        }

        // Config
        if (!isset($this->config[$id])) $this->loadConfig();

        // Enabled
        if (!$this->config[$id]['skip'])
        {
            Server::get()->message(__('Map skipping has been disabled by an administrator'), $player, 'error');
            return;
        }

        // Exists?
        if (empty($this->skips[$id]))
        {
            $this->skips[$id] = array('players' => array(), 'max' => count(PlayerManager::getPlayers()));
        }

        // Voted?
        if (isset($this->skips[$id]['players'][$player->guid]))
        {
            return;
        }

        // Vote
        $this->skips[$id]['players'][$player->guid] = TRUE;

        // Count
        $count = count($this->skips[$id]['players']);

        // Skip?
        if (($count * 2) > $this->skips[$id]['max'])
        {
            // Load current map
            if (!isset($this->cacheMap[$id]))
            {
                $this->cacheMap[$id] = Server::get()->getCvar('mapname');
            }

            // Maps
            $maps = $this->mapList[$id];

            // Remove current map
            if ($this->cacheMap[$id] AND isset($maps[$this->cacheMap[$id]]) AND count($maps) > 1) unset($maps[$this->cacheMap[$id]]);

            if (count($maps) == 1)
            {
                $map = reset($maps);
            }
            else
            {
                shuffle($maps);

                $map = reset($maps);
            }

            $this->setNextMap($map);

            $this->skips[$id] = array();

            Server::get()->message(__('Majority of players have decided to skip next map. Next map has been set to: ').$map);
        }
        else
        {
            Server::get()->message(__('Player :name has voted to skip next map (:skips/:max)', array(
                ':skips' => $count,
                ':max' => $this->skips[$id]['max'],
                ':name' => $player->name.'^7'
            )));
            return;
        }
    }

    /**
     * Find correct map name
     *
     * @param  string $map
     * @return string
     */
    private function findMap($map)
    {
        // Human names
        switch ($map)
        {
            case 'wmd':
                $map = 'russianbase';
                break;

            case 'launch':
                $map = 'cosmodrome';
                break;

            case 'jungle':
                $map = 'havoc';
                break;

            case 'grid':
                $map = 'duga';
                break;

            case 'nuketown':
                $map = 'nuked';
                break;

            case 'cairo':
                $map = 'havana';
                break;
        }
        // Iterate
        foreach ($this->mapList[Server::get('id')] as $m)
        {
            // Match
            if (stripos($m, $map) !== FALSE)
            {
                return $m;
            }
        }

        // Not found
        return NULL;
    }

    /**
     * Load plugin
     *
     * @return bool
     */
    public function load()
    {
        // Commands
        Commands::add('nextmap'    , array($this, 'commandNextMap'));
        Commands::add('nominate'   , array($this, 'commandNominate'));
        Commands::add('skip'       , array($this, 'commandSkip'));
        Commands::add('setmap'     , array($this, 'commandSetNextMap'), 'setnextmap');
        Commands::add('setplaylist', array($this, 'commandSetPlaylist'), 'setplaylist');
        Commands::add('map'        , array($this, 'commandMap'));

        // Events
        Event::add('onExitLevel'      , array($this, 'onExitLevel'));
        Event::add('onInitGame'       , array($this, 'onInitGame'));
        Event::add('onServerLogFinish', array($this, 'onServerLogFinish'));
        Event::add('onConfigReload'   , array($this, 'loadConfig'));

        // Votes
        VoteManager::add('map'     , array($this, 'voteStartMap')     , array($this, 'voteEndMap'));
        VoteManager::add('playlist', array($this, 'voteStartPlaylist'), array($this, 'voteEndPlaylist'));

        // Vote
        VoteManager::addCallback('servermap', array($this, 'onEndVote'));

        // Done
        return TRUE;
    }

    /**
     * Load config
     */
    public function loadConfig()
    {
        // Server ID
        $id = Server::get('id');

        // Map list
        $this->mapList[$id] = array();

        foreach (Config::get('maps.list', array()) as $m)
        {
            $this->mapList[$id][$m] = $m;
        }

        // Cannot be empty...
        if (empty($this->mapList[$id]))
        {
            $this->mapList[$id][] = 'mp_array';
        }

        // Playlists
        $this->playlists[$id] = array();

        foreach (Config::get('maps.playlists', array()) as $pl)
        {
            // Split
            $pl = explode(' ', $pl, 2);

            // Valid mode
            if (isset(RconConstants::$gametypes[$pl[0]]))
            {
                // Type
                if (isset($pl[1]) AND $pl[1] == 'barebones')
                {
                    $this->playlists[$id][] = array('type' => $pl[0], 'mode' => 'barebones');
                }
                elseif (isset($pl[1]) AND $pl[1] == 'hardcore')
                {
                    $this->playlists[$id][] = array('type' => $pl[0], 'mode' => 'hardcore');
                }
                else
                {
                    $this->playlists[$id][] = array('type' => $pl[0], 'mode' => 'normal');
                }
            }
        }

        // Set current playlist
        $this->playCurrent[$id] = RconConstants::getInfo(Server::get()->getCvar('playlist', TRUE));

        // Config
        $this->config[$id] = array(
            'type' => Config::get('maps.rotation_type', 'vote'),
            'skip' => Config::get('maps.enable_skip', TRUE),
            'nominations' => Config::get('maps.nominations', TRUE),
            'selection_after' => Config::get('maps.selection_after', 30)
        );
    }

    /**
     * Vote end
     *
     * @param string       $result
     * @param array|string $options
     * @param mixed        $params
     */
    public function onEndVote($result, $options, $params)
    {
        // Server ID
        $id = Server::get('id');

        // Enable skip
        $this->skipEnabled[$id] = TRUE;

        // Config
        if (!isset($this->config[$id])) $this->loadConfig();

        // Random map
        if ($result == 'fail')
        {
            // Load current map
            if (!isset($this->cacheMap[$id]))
            {
                $this->cacheMap[$id] = Server::get()->getCvar('mapname');
            }

            // Maps
            $maps = $this->mapList[$id];

            // Remove current map
            if ($this->cacheMap[$id] AND isset($maps[$this->cacheMap[$id]]) AND count($maps) > 1) unset($maps[$this->cacheMap[$id]]);

            // Random
            shuffle($maps);

            $this->setNextMap(reset($maps));
            Server::get()->message(__('Next map has been set to: ').reset($maps));
            return;
        }

        // Draw
        if ($result == 'draw')
        {
            shuffle($options);

            $this->setNextMap(reset($options));
            Server::get()->message(__('Next map has been set to: ').reset($options));
            return;
        }

        // Win
        $this->setNextMap($options);

        // Set
        Server::get()->message(__('Next map has been set to: ').$options);
    }

    /**
     * On exit level
     */
    public function onExitLevel()
    {
        // ID
        $id = Server::get('id');

        // Cleanup stuff
        $this->nominations[$id] = array('players' => array(), 'maps' => array());
        $this->nextMap[$id] = NULL;
        $this->nextType[$id] = NULL;
        $this->skips[$id] = array();
        $this->timers[$id] = array('time' => 0, 'called' => FALSE);
        $this->timers2[$id] = array('time' => 0, 'called' => FALSE);
        if (isset($this->cacheMap[$id])) unset($this->cacheMap[$id]);

        // Disable skip
        $this->skipEnabled[$id] = FALSE;
    }

    /**
     * On map init
     */
    public function onInitGame()
    {
        // Get playlist
        $this->playCurrent[Server::get('id')] = RconConstants::getInfo(Server::get()->getCvar('playlist', TRUE));
    }

    /**
     * After log parsing
     *
     * @param float $deltaTime
     */
    public function onServerLogFinish($deltaTime)
    {
        // Cancel tick
        if (!count(PlayerManager::getPlayers()))
        {
            return;
        }

        // Server ID
        $id = Server::get('id');

        // Config
        if (!isset($this->config[$id])) $this->loadConfig();

        // Timer
        if (!isset($this->timers[$id]))
        {
            $this->timers[$id] = array('time' => 0, 'called' => FALSE);
            $this->timers2[$id] = array('time' => 0, 'called' => FALSE);
        }

        // Playlist
        if ($this->config[$id]['selection_after'] AND !$this->timers2[$id]['called']) $this->onServerLogFinishPlaylist($deltaTime);

        // Disabled
        if (!$this->config[$id]['selection_after'] OR $this->timers[$id]['called'])
        {
            return;
        }

        // Add time
        $this->timers[$id]['time'] += $deltaTime;

        // Time to execute
        if ($this->timers[$id]['time'] >= $this->config[$id]['selection_after'])
        {
            // Don't call it again
            $this->timers[$id] = array('time' => 0, 'called' => TRUE);

            // Load current map
            if (!isset($this->cacheMap[$id]))
            {
                $this->cacheMap[$id] = Server::get()->getCvar('mapname');
            }

            // Type
            if ($this->config[$id]['type'] == 'random')
            {
                // Enable skip
                $this->skipEnabled[$id] = TRUE;

                // Remove current
                $maps = $this->mapList[$id];

                if ($this->cacheMap[$id] AND isset($maps[$this->cacheMap[$id]]) AND count($maps) > 1)
                {
                    unset($maps[$this->cacheMap[$id]]);
                }

                // Find next map
                if (count($maps) == 1)
                {
                    $map = reset($maps);
                }
                else
                {
                    shuffle($maps);

                    $map = reset($maps);
                }

                $this->setNextMap($map);
                Server::get()->message(__('Next map has been set to: ').$map);
            }
            elseif ($this->config[$id]['type'] == 'rotation')
            {
                // Enable skip
                $this->skipEnabled[$id] = TRUE;

                // Find next
                $map = $this->cacheMap[$id];
                $nextMap = NULL;

                if ($map)
                {
                    $select = FALSE;

                    foreach ($this->mapList[$id] as $k)
                    {
                        if ($select)
                        {
                            $nextMap = $k;
                            break;
                        }

                        if ($map == $k)
                        {
                            $select = TRUE;
                        }
                    }
                }

                // First map in rotation
                if (!$nextMap) $nextMap = reset($this->mapList[$id]);

                // Set
                $this->setNextMap($nextMap);
                Server::get()->message(__('Next map has been set to: ').$nextMap);
            }
            else
            {
                // Maps
                $maps = array();
                $count = 0;

                // Nominations
                if (isset($this->nominations[$id]))
                {
                    foreach ($this->nominations[$id]['maps'] as $map)
                    {
                        if ($count >= 5) break;

                        $maps[$map] = 0;
                        $count++;
                    }
                }

                // Random
                if ($count < 5)
                {
                    $random = $this->mapList[$id];
                    shuffle($random);

                    foreach ($random as $map)
                    {
                        if ($map == $this->cacheMap[$id]) continue;

                        if ($count >= 5) break;

                        $maps[$map] = 0;
                        $count++;
                    }
                }

                // Send vote
                if (count($maps))
                {
                    VoteManager::callVote('servermap', $maps, NULL, __('Select next map.'));
                }
            }
        }
    }

    /**
     * Playlist rotation
     *
     * @param float $deltaTime
     */
    private function onServerLogFinishPlaylist($deltaTime)
    {
        // ID
        $id = Server::get('id');

        // Ignore
        if (empty($this->playlists[$id])) return;

        // Add time
        $this->timers2[$id]['time'] += $deltaTime;

        // Time to execute
        if ($this->timers2[$id]['time'] >= ($this->config[$id]['selection_after'] + 10))
        {
            // Don't call it again
            $this->timers2[$id] = array('time' => 0, 'called' => TRUE);

            // Invalid
            if (!empty($this->playCurrent[$id]) AND $this->playCurrent[$id]['type'] == 'wager' AND $this->playCurrent[$id]['mode'] == 'wager')
            {
                $this->playCurrent[$id] = RconConstants::getInfo(Server::get()->getCvar('playlist', TRUE));
            }

            // From the beggining?
            if (empty($this->playCurrent[$id]))
            {
                // First item
                $this->setNextPlaylist(reset($this->playlists[$id]));
            }
            else
            {
                // Current?
                $getNext = FALSE;
                $playlist = NULL;

                foreach ($this->playlists[$id] as $v)
                {
                    if ($getNext)
                    {
                        $playlist = $v;
                        break;
                    }

                    if ($v == $this->playCurrent[$id])
                    {
                        $getNext = TRUE;
                    }
                }

                // First item
                if (!$playlist) $playlist = reset($this->playlists[$id]);

                // Set
                $this->setNextPlaylist($playlist);
            }
        }
    }

    /**
     * Reload plugin
     */
    public function reload()
    {
        // Commands
        Commands::add('nextmap'    , array($this, 'commandNextMap'));
        Commands::add('nominate'   , array($this, 'commandNominate'));
        Commands::add('skip'       , array($this, 'commandSkip'));
        Commands::add('setmap'     , array($this, 'commandSetNextMap'), 'setnextmap');
        Commands::add('setplaylist', array($this, 'commandSetPlaylist'), 'setplaylist');
        Commands::add('map'        , array($this, 'commandMap'));

        // Events
        Event::add('onExitLevel'      , array($this, 'onExitLevel'));
        Event::add('onServerLogFinish', array($this, 'onServerLogFinish'));
        Event::add('onConfigReload'   , array($this, 'loadConfig'));
        Event::add('onInitGame'       , array($this, 'onInitGame'));

        // Votes
        VoteManager::add('map'     , array($this, 'voteStartMap')     , array($this, 'voteEndMap'));
        VoteManager::add('playlist', array($this, 'voteStartPlaylist'), array($this, 'voteEndPlaylist'));

        // Vote
        VoteManager::addCallback('servermap', array($this, 'onEndVote'));

        // Clear config
        $this->config = array();
        $this->mapList = array();
        $this->cacheMap = array();
    }

    /**
     * Set next map
     *
     * @param string $map
     */
    private function setNextMap($map)
    {
        // Server
        $server = Server::get();

        // Full map list
        $maps = RconConstants::$maps;

        // Enabled maps list
        $enabledMaps = $this->mapList[$server->id];

        // -----------------
        // Set rotation
        // -----------------
        $cvar = '';

        foreach ($enabledMaps as $m)
        {
            $cvar .= ' map '.$m;
        }

        $cvar = trim($cvar);

        if (empty($this->nextType[$server->id]))
        {
            $current = explode(' ', $server->getCvar('sv_mapRotation'), 3);

            if ($current[0] == 'gametype')
            {
                $cvar = 'gametype '.$current[1].' '.$cvar;
            }
        }
        else
        {
            $cvar = 'gametype '.$this->nextType[$server->id].' '.$cvar;
        }

        $server->setCvar('sv_mapRotation', $cvar);

        // -----------------
        // Set excludes
        // -----------------
        if (isset($maps[$map])) unset($maps[$map]);

        $server->setCvar('playlist_excludeMap', implode(' ', array_keys($maps)));

        // Next map
        $this->nextMap[$server->id] = $map;
    }

    /**
     * Set next playlist
     *
     * @param array $playlist
     */
    private function setNextPlaylist(array $playlist)
    {
        // ID
        $id = Server::get('id');

        // Calculate ID
        $type = RconConstants::getPlaylistsByType($playlist['type']);
        $mode = RconConstants::getPlaylistsByMode($playlist['mode']);

        foreach ($type as $k => $v)
        {
            if (isset($mode[$k]))
            {
                $playlistID = $k;
                break;
            }
        }

        // Set playlist
        Server::get()->setCvar('playlist', $playlistID);

        // Map rotation
        $cvar = 'gametype '.$playlist['type'];

        foreach ($this->mapList[$id] as $m)
        {
            $cvar .= ' map '.$m;
        }

        $cvar = trim($cvar);

        Server::get()->setCvar('sv_mapRotation', $cvar);

        // Next
        $this->nextType[$id] = $playlist['type'];

        // Message
        Server::get()->message(__('Next playlist has been set to: :type :mode', array(':type' => $playlist['type'], ':mode' => $playlist['mode'])));
    }

    /**
     * Unload plugin
     */
    public function unload()
    {

    }

    /**
     * End vote map
     *
     * @param string       $result
     * @param array|string $option
     * @param mixed        $params
     */
    public function voteEndMap($result, $option, $params)
    {
        // Only win
        if ($result != 'win' OR $option != __('yes'))
        {
            return;
        }

        // Set next map
        $this->setNextMap($params);

        // Set
        Server::get()->message(__('Next map has been set to: ').$params);
    }

    /**
     * End vote playlist
     *
     * @param string       $result
     * @param array|string $option
     * @param mixed        $params
     */
    public function voteEndPlaylist($result, $option, $params)
    {
        // Only win
        if ($result != 'win' OR $option != __('yes'))
        {
            return;
        }

        // Set playlist
        $this->setNextPlaylist($params);
    }

    /**
     * Start map vote
     *
     * @param Player $player
     * @param array $text
     */
    public function voteStartMap(Player $player, $text)
    {
        // Argument
        if (!isset($text[2]) OR !is_string($text[2]))
        {
            Server::get()->message(__('Usage: !callvote map mapname'), $player, 'error');
            return;
        }

        // ID
        $id = Server::get('id');

        // Can't change map?
        if (!isset($this->skipEnabled[$id]) OR !$this->skipEnabled[$id])
        {
            Server::get()->message(__('Cannot change map right now'), $player, 'error');
            return;
        }

        // Valid map?
        if (!isset($this->mapList[$id][$text[2]]))
        {
            if (($text[2] = $this->findMap($text[2])) === NULL)
            {
                Server::get()->message(__('Invalid map'), $player, 'error');
                return;
            }
        }

        // Success
        return array(
            'message' => __('Change map to :name?', array(':name' => $text[2])),
            'options' => array(__('yes') => 0, __('no') => 0),
            'params'  => $text[2]
        );
    }

    /**
     * Start playlist vote
     *
     * @param Player $player
     * @param array $text
     */
    public function voteStartPlaylist(Player $player, $text)
    {
        // Argument
        if (!isset($text[2]) OR !is_string($text[2]))
        {
            Server::get()->message(__('Usage: !callvote playlist gtype normal|hardcore|barebones'), $player, 'error');
            return;
        }

        // ID
        $id = Server::get('id');

        // Can't change playlist?
        if (!isset($this->nextType[$id]) OR empty($this->nextType[$id]))
        {
            Server::get()->message(__('Cannot change playlist right now'), $player, 'error');
            return;
        }

        // Playlist
        $playlist = array('type' => $text[2]);

        // Valid playlist
        if (!isset(RconConstants::$gametypes[$text[2]]))
        {
            Server::get()->message(__('Invalid game type'), $player, 'error');
            return;
        }

        // Mode
        if (isset($text[3]) AND is_string($text[3]))
        {
            if ($text[3] == 'barebones') $playlist['mode'] = 'barebones';
            elseif ($text[3] == 'hardcore') $playlist['mode'] = 'hardcore';
            else $playlist['mode'] = 'normal';
        }
        else
        {
            $playlist['mode'] = 'normal';
        }

        // Success
        return array(
            'message' => __('Change playlist to :name?', array(':name' => $playlist['type'].' '.$playlist['mode'])),
            'options' => array(__('yes') => 0, __('no') => 0),
            'params'  => $playlist
        );
    }
}