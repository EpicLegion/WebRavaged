<?php defined('ROOT_PATH') or die('No direct script access.');

 __autoload('Player');

/**
 * Player manager
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

final class PlayerManager
{
    // Deny / allow
    const DENY = 1;
    const ALLOW = 2;

    /**
     * @var array
     */
    static protected $acl = array();

    /**
     * @var array
     */
    static protected $aclDefault = array();

    /**
     * @var array
     */
    static protected $groups = array();

    /**
     * @var array
     */
    static protected $players = array();

    /**
     * @var array
     */
    static protected $users = array();

    /**
     * Do ACL check
     *
     * @return bool
     */
    public static function aclCheck($guid, $id)
    {
        // Server ID
        $server = Server::get('id');

        // Check expiration
        if (isset(self::$acl[$server][$guid]) AND self::$acl[$server][$guid]['expires'] > 0 AND self::$acl[$server][$guid]['expires'] <= time())
        {
            unset(self::$acl[$server][$guid]);
        }

        // ACL
        if ((self::$aclDefault[$server] == self::DENY AND !isset(self::$acl[$server][$guid]))
           OR (isset(self::$acl[$server][$guid]) AND self::$acl[$server][$guid]['action'] == self::DENY))
        {
            // Fake player object
            $player = new Player($guid, $id, 'none', 'none');

            // Reason
            if (isset(self::$acl[$server][$guid]))
            {
                $reason = self::$acl[$server][$guid]['reason'];
            }
            else
            {
                $reason = __('Private server');
            }

            // Kick
            Server::get()->kick($player, $reason);

            // Done
            return FALSE;
        }

        // Valid player
        return TRUE;
    }

    /**
     * Cleanup player list
     */
    public static function clear()
    {
        if (isset(self::$players[Server::get('id')]))
        {
            self::$players[Server::get('id')] = array();
        }
    }

    /**
     * Find matching user(s) by name
     *
     * @param  string $rule
     * @param  bool   $multiple
     * @return Player
     */
    public static function find($rule, $multiple = FALSE)
    {
        // No server?
        if (!isset(self::$players[Server::get('id')]))
        {
            return $multiple ? array() : NULL;
        }

        // Result
        $result = array();

        // Iterate
        foreach (self::$players[Server::get('id')] as $p)
        {
            // Exact match
            if ($p->name == $rule)
            {
                $result[] = $p;
            }
            elseif (stripos($p->name, $rule) !== FALSE)
            {
                $result[] = $p;
            }

            // Break
            if (!$multiple AND !empty($result)) break;
        }

        // Single player
        if (!$multiple)
        {
            // Found?
            if (!isset($result[0]))
            {
                // Get status
                $status = Server::get()->teamStatus();

                // Iterate
                foreach ($status['players'] as $p)
                {
                    // Match
                    if ($p['name'] == $rule OR stripos($p['name'], $rule) !== FALSE)
                    {
                        // Exists?
                        if (!isset(self::$players[Server::get('id')][$p['guid']]))
                        {
                            self::readPlayer($p['guid'], $p['id'], $p['name']);
                        }

                        return self::$players[Server::get('id')][$p['guid']];
                    }
                }

                return NULL;
            }

            return $result[0];
        }

        // Multiple players
        return $result;
    }

    /**
     * Find player by GUID
     *
     * @param  int    $guid
     * @return Player
     */
    public static function findGUID($guid)
    {
        // No server?
        if (!isset(self::$players[Server::get('id')]))
        {
            return NULL;
        }

        // Iterate
        foreach (self::$players[Server::get('id')] as $p)
        {
            // Exact match
            if ($p->guid == $guid)
            {
                return $p;
            }
        }

        // Not found
        return NULL;
    }

    /**
     * Get flags for player
     *
     * @param  int   $guid
     * @return array
     */
    public static function getFlags($guid)
    {
        // Nope?
        if (!isset(self::$users[Server::get('id')][$guid]))
        {
            return isset(self::$users[Server::get('id')]['none']) ? self::$users[Server::get('id')]['none']['flags'] : array();
        }

        // Yeah they do exist
        return self::$users[Server::get('id')][$guid]['flags'];
    }

    /**
     * Get group name
     *
     * @param  int    $guid
     * @return string
     */
    public static function getGroup($guid)
    {
        // None?
        if (!isset(self::$users[Server::get('id')][$guid]))
        {
            return NULL;
        }

        // Return
        return self::$users[Server::get('id')][$guid]['group'];
    }

    /**
     * Get players list
     *
     * @param  bool  $all
     * @return array
     */
    public static function getPlayers($all = FALSE)
    {
        // All servers
        if ($all) return self::$players;

        // Single server
        if (isset(self::$players[Server::get('id')]))
        {
            return self::$players[Server::get('id')];
        }

        // None
        return array();
    }

    /**
     * Initialize player manager
     */
    public static function init()
    {
        // Clear
        self::$groups = array();
        self::$users = array();
        self::$acl = array();
        self::$aclDefault = array();

        // Events
        Event::add('onExitLevel'   , array('PlayerManager', 'clear'));
        Event::add('onQuit'        , array('PlayerManager', 'removePlayer'));
        Event::add('onConfigReload', array('PlayerManager', 'reloadConfig'));

        // Set group command
        Commands::add('setgroup', array('PlayerManager', 'setGroup'), 'setgroup');

        // Config for every server
        foreach (ServerManager::get() as $server)
        {
            // Server ID
            $id = $server->id;

            // Arrays
            self::$groups[$id] = array();
            self::$users[$id] = array();
            self::$acl[$id] = array();

            // Default ACL action
            self::$aclDefault[$id] = Config::get('users.private_server', FALSE, 'server-'.$id) ? self::DENY : self::ALLOW;

            // Load config
            $config = Config::get('users', array(), 'server-'.$id);

            // ACL
            if (isset($config['acl']) AND is_array($config['acl']))
            {
                // Iterate
                foreach ($config['acl'] as $guid => $info)
                {
                    // Invalid
                    if (!ctype_digit($guid) OR !is_array($info) OR !isset($info['deny']) OR !isset($info['expires']))
                    {
                        continue;
                    }

                    // Reason?
                    if (!isset($info['reason']))
                    {
                        $info['reason'] = __('Access denied');
                    }

                    // Set
                    self::$acl[$id][(int) $guid] = array('action' => $info['deny'] ? self::DENY : self::ALLOW, 'expires' => (int) $info['expires'], 'reason' => $info['reason']);
                }
            }

            // Groups
            if (isset($config['groups']) AND is_array($config['groups']))
            {
                // Iterate
                foreach ($config['groups'] as $g)
                {
                    // Validate
                    if (!is_array($g) OR count($g) != 2 OR !isset($g[0]) OR !isset($g[1]))
                    {
                        continue;
                    }

                    // Flags
                    $flags = array();

                    if ($g[1] AND is_string($g[1]))
                    {
                        // Single flag
                        if (!strstr($g[1], ';'))
                        {
                            $flags[$g[1]] = $g[1];
                        }
                        else
                        {
                            $g[1] = explode(';', $g[1]);

                            foreach ($g[1] as $v)
                            {
                                if (empty($v))
                                {
                                    continue;
                                }

                                $flags[$v] = $v;
                            }
                        }
                    }

                    // Add
                    self::$groups[$id][(string) $g[0]] = $flags;
                }
            }

            // Users
            if (isset($config['users']) AND is_array($config['users']))
            {
                // Iterate
                foreach ($config['users'] as $u)
                {
                    // Validate
                    if (!is_array($u) OR count($u) != 2 OR !isset($u[0]) OR !isset($u[1]))
                    {
                        continue;
                    }

                    // Type
                    if ($u[0] == 'none')
                    {
                        $type = 'guest';
                    }
                    elseif (is_int($u[0]))
                    {
                        $type = 'guid';
                    }
                    else
                    {
                        continue;
                    }

                    // Group?
                    if (isset(self::$groups[$id][$u[1]]))
                    {
                        $group = (string) $u[1];
                        $flags = self::$groups[$id][$u[1]];
                    }
                    else
                    {
                        $group = NULL;

                        $flags = array();

                        if ($u[1] AND is_string($u[1]))
                        {
                            // Single flag
                            if (!strstr($u[1], ';'))
                            {
                                $flags[$u[1]] = $u[1];
                            }
                            else
                            {
                                $u[1] = explode(';', $u[1]);

                                foreach ($u[1] as $v)
                                {
                                    if (empty($v))
                                    {
                                        continue;
                                    }

                                    $flags[$v] = $v;
                                }
                            }
                        }
                    }

                    // Add user
                    self::$users[$id][$u[0]] = array('group' => $group, 'flags' => $flags, 'type' => $type, 'rule' => $u[0]);
                }
            }
        }
    }

    /**
     * Read player
     *
     * @param  int    $guid
     * @param  int    $id
     * @param  string $name
     * @param  string $team
     * @return Player
     */
    public static function readPlayer($guid, $id, $name, $team = NULL)
    {
        // Bad clients
        if (empty($guid) OR empty($id) OR $id <= 0 OR $guid <= 0)
        {
            // World
            if ($name == '[3arc]democlient')
            {
                return new DemoclientPlayer;
            }
            else
            {
                return new WorldPlayer;
            }
        }

        // Clean name
        $name = utf8_clean($name);

        // Server ID
        $serverId = Server::get('id');

        // Server exists?
        if (!isset(self::$players[$serverId]))
        {
            self::$players[$serverId] = array();
        }

        // Player exists?
        if (!isset(self::$players[$serverId][$guid]))
        {
            self::$players[$serverId][$guid] = new Player($guid, $id, $name, $team);
        }
        else
        {
            // Update
            self::$players[$serverId][$guid]->update($id, $name, $team);
        }

        // Return
        return self::$players[$serverId][$guid];
    }

    /**
     * Reload configuration
     */
    public static function reloadConfig()
    {
        // Server ID
        $id = Server::get('id');

        // Arrays
        self::$groups[$id] = array();
        self::$users[$id] = array();
        self::$acl[$id] = array();

        // Default ACL action
        self::$aclDefault[$id] = Config::get('users.private_server', FALSE, 'server-'.$id) ? self::DENY : self::ALLOW;

        // Load config
        $config = Config::get('users', array());

        // ACL
        if (isset($config['acl']) AND is_array($config['acl']))
        {
            // Iterate
            foreach ($config['acl'] as $guid => $info)
            {
                // Invalid
                if (!ctype_digit($guid) OR !is_array($info) OR !isset($info['deny']) OR !isset($info['expires']))
                {
                    continue;
                }

                // Reason?
                if (!isset($info['reason']))
                {
                    $info['reason'] = 'Access denied';
                }

                // Set
                self::$acl[$id][(int) $guid] = array('action' => $info['deny'] ? self::DENY : self::ALLOW, 'expires' => (int) $info['expires'], 'reason' => $info['reason']);
            }
        }

        // Groups
        if (isset($config['groups']) AND is_array($config['groups']))
        {
            // Iterate
            foreach ($config['groups'] as $g)
            {
                // Validate
                if (!is_array($g) OR count($g) != 2 OR !isset($g[0]) OR !isset($g[1]))
                {
                    continue;
                }

                // Flags
                $flags = array();

                if ($g[1] AND is_string($g[1]))
                {
                    // Single flag
                    if (!strstr($g[1], ';'))
                    {
                        $flags[$g[1]] = $g[1];
                    }
                    else
                    {
                        $g[1] = explode(';', $g[1]);

                        foreach ($g[1] as $v)
                        {
                            if (empty($v))
                            {
                                continue;
                            }

                            $flags[$v] = $v;
                        }
                    }
                }

                // Add
                self::$groups[$id][(string) $g[0]] = $flags;
            }
        }

        // Users
        if (isset($config['users']) AND is_array($config['users']))
        {
            // Iterate
            foreach ($config['users'] as $u)
            {
                // Validate
                if (!is_array($u) OR count($u) != 2 OR !isset($u[0]) OR !isset($u[1]))
                {
                    continue;
                }

                // Type
                if ($u[0] == 'none')
                {
                    $type = 'guest';
                }
                elseif (is_int($u[0]))
                {
                    $type = 'guid';
                }
                else
                {
                    continue;
                }

                // Group?
                if (isset(self::$groups[$id][$u[1]]))
                {
                    $group = (string) $u[1];
                    $flags = self::$groups[$id][$u[1]];
                }
                else
                {
                    $group = NULL;

                    $flags = array();

                    if ($u[1] AND is_string($u[1]))
                    {
                        // Single flag
                        if (!strstr($u[1], ';'))
                        {
                            $flags[$u[1]] = $u[1];
                        }
                        else
                        {
                            $u[1] = explode(';', $u[1]);

                            foreach ($u[1] as $v)
                            {
                                if (empty($v))
                                {
                                    continue;
                                }

                                $flags[$v] = $v;
                            }
                        }
                    }
                }

                // Add user
                self::$users[$id][$u[0]] = array('group' => $group, 'flags' => $flags, 'type' => $type, 'rule' => $u[0]);
            }
        }
    }

    /**
     * Remove player (when he has left)
     *
     * @param Player $guid
     */
    public static function removePlayer(Player $player)
    {
        // Server ID
        $id = Server::get('id');

        // Remove
        if (isset(self::$players[$id]))
        {
            if (isset(self::$players[$id][$player->guid])) unset(self::$players[$id][$player->guid]);
        }
    }

    /**
     * Restore session
     *
     * @param array $players
     */
    public static function resume(array $players = array())
    {
        // Init
        self::init();

        // Restore players
        self::$players = $players;
    }

    /**
     * Write new config
     */
    private static function saveConfig()
    {
        // Server ID
        $id = Server::get('id');

        // Generate new config
        $config = array('groups' => array(), 'users' => array(), 'acl' => array(), 'private_server' => (self::$aclDefault[$id] == self::DENY));

        foreach (self::$acl[$id] as $guid => $info)
        {
            // Invalid
            if ($info['expires'] AND $info['expires'] <= time())
            {
                continue;
            }

            // Add
            $config['acl'][$guid] = array('deny' => ($info['action'] == self::DENY), 'expires' => $info['expires'], 'reason' => $info['reason']);
        }

        foreach (self::$groups[$id] as $k => $v)
        {
            $config['groups'][] = array($k, implode(';', $v));
        }

        foreach (self::$users[$id] as $u)
        {
            $config['users'][] = array($u['rule'], ($u['group'] ? $u['group'] : implode(';', $u['flags'])));
        }

        // Set and save
        Config::set('users', $config);
        Config::save();
    }

    /**
     * Set player access
     *
     * @param Player $player
     * @param int    $action
     * @param string $reason
     * @param int    $expires
     */
    public static function setACL(Player $player, $action, $reason = NULL, $expires = 0)
    {
        // Set
        self::$acl[Server::get('id')][$player->guid] = array(
            'action' => ($action == self::DENY ? self::DENY : self::ALLOW),
            'expires' => $expires,
            'reason' => ($reason ? $reason : __('Access denied'))
        );

        // Save config
        self::saveConfig();
    }

    /**
     * Set player group
     *
     * @param Player $player
     * @param string $text
     */
    public static function setGroup(Player $player, $text)
    {
        // Valid syntax
        if (!isset($text[1]) OR !isset($text[2]))
        {
            return;
        }

        // Permissions
        if (!$player->hasFlag('setgroup'))
        {
            Server::get()->message(__('Insufficient permissions'), $player);
            return;
        }

        // Find player
        $target = self::find($text[1]);

        if ($target === NULL OR (!$player->hasFlag('immunity') AND $target->hasFlag('immunity')))
        {
            Server::get()->message(__('Invalid target (player not found or has immunity)'), $player);
            return;
        }

        // Server ID
        $id = Server::get('id');

        // Find group
        if (!isset(self::$groups[$id][$text[2]]))
        {
            Server::get()->message(__('Invalid group'), $player);
            return;
        }

        // Set permissions
        self::$users[$id][$target->guid] = array('group' => $text[2], 'flags' => self::$groups[$id][$text[2]], 'type' => 'guid', 'rule' => $target->guid);

        // Set player
        self::$players[$id][$target->guid]->flags = self::$groups[$id][$text[2]];

        // Save config
        self::saveConfig();

        // Message
        Server::get()->message(__('Group successfully set'), $player);
    }
}